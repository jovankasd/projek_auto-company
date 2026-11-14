# ADR-006: Mesin Status Test Drive, Hak Akses Kepemilikan Sales, dan Aksi Siklus Lengkap

## Status
Accepted

## Date
2026-11-14

## Context
Berdasarkan PRD §5.2 dan aturan bisnis BR-07:
1. Lead test drive hanya boleh dilihat dan diproses oleh Sales yang ditugaskan (`sales_id`), dengan pengecualian Super Admin yang dapat mengakses semua lead.
2. Alur status test drive harus mengikuti state machine:
   - `requested` -> `approved` (Aktor: Sales pemilik / Super Admin)
   - `requested` -> `rejected` (Aktor: Sales pemilik / Super Admin, dengan alasan penolakan wajib)
   - `requested` -> `cancelled` (Aktor: Customer pemilik / Sales pemilik / Super Admin)
   - `approved` -> `completed` (Aktor: Sales pemilik / Super Admin, mencatat timestamp `completed_at`)
   - `approved` -> `cancelled` (Aktor: Customer pemilik / Sales pemilik / Super Admin)
   - Status terminal (`completed`, `rejected`, `cancelled`) tidak dapat dimutasi kembali ke status mana pun.
3. Sebelumnya, `TestDrivePolicy` mengizinkan seluruh Sales menyetujui atau menolak lead milik sales lain tanpa validasi status asal, serta tidak menyediakan route atau aksi untuk penyelesaian (`complete`) dan pembatalan (`cancel`).

## Decision
1. **State Machine pada Enum**: Menambahkan method `allowedTransitions()`, `canTransitionTo()`, `isTerminal()`, dan `canBeCancelled()` pada [`App\Enums\TestDriveStatus`](../../app/Enums/TestDriveStatus.php).
2. **Integritas Model Eloquent**: Menerapkan event hook `booted()` pada [`App\Models\TestDrive`](../../app/Models/TestDrive.php) untuk melempar `\DomainException` apabila terjadi perubahan status ilegal atau mutasi pada status terminal.
3. **Penegakan Kepemilikan & Otorisasi**: Memperbarui [`App\Policies\TestDrivePolicy`](../../app/Policies/TestDrivePolicy.php) agar:
   - `view`: Hanya sales pemilik (`sales_id === user->id`), customer pemilik (`customer_id === user->id`), dan Super Admin.
   - `approve` / `reject`: Hanya valid dari status `requested`, diotorisasi untuk sales pemilik dan Super Admin.
   - `complete`: Hanya valid dari status `approved`, diotorisasi untuk sales pemilik dan Super Admin.
   - `cancel`: Hanya valid dari status non-terminal (`requested` dan `approved`), diotorisasi untuk customer pemilik, sales pemilik, dan Super Admin.
4. **Query Scoping**: Menggunakan `forSales($user->id)` pada query dashboard sales untuk memastikan data terisolasi sesuai BR-07.
5. **Rute & UI**:
   - Menambahkan rute `dashboard/test-drives/{testDrive}` (GET, show), `dashboard/test-drives/{testDrive}/complete` (PATCH), dan `dashboard/test-drives/{testDrive}/cancel` (PATCH).
   - Menambahkan view detail [`resources/views/dashboard/test-drives/show.blade.php`](../../resources/views/dashboard/test-drives/show.blade.php).
   - Menyediakan aksi tombol Selesai dan Batal pada panel sales dan riwayat customer.
6. **Notifikasi**: Memperbarui listener [`App\Listeners\SendTestDriveStatusNotification`](../../app/Listeners/SendTestDriveStatusNotification.php) untuk mengirimkan `TestDriveCompleted` kepada customer dan sales, serta notifikasi `TestDriveCancelled` saat pembatalan.

## Consequences
- Keamanan lead terjaga dan tidak terjadi kebocoran antar-sales (BR-07 terpenuhi).
- Siklus hidup lead test drive menjadi lengkap mulai dari pengajuan hingga penyelesaian atau pembatalan.
- Seluruh transisi status dilindungi berlapis pada level Policy, Controller, dan Model Eloquent.
