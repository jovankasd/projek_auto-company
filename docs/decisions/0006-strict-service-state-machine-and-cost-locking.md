# ADR-006: Penguncian State Machine Servis, Pertahanan Berlapis Biaya Final, dan Pipeline Pembatalan Pelanggan

## Status
Accepted

## Date
2026-11-14

## Context
Berdasarkan hasil audit sistem 9 November 2026 (Audit #3) dan PRD §5.1:
1. Work order servis sebelumnya dapat dimutasi setelah mencapai status terminal (`completed` atau `cancelled`). Item servis dapat ditambah/dihapus sehingga mengubah `total_cost` yang seharusnya sudah final.
2. Mekanik yang tidak ditugaskan ke sebuah servis dapat melakukan mutasi status dan item pada work order milik mekanik lain karena `ServicePolicy` sebelumnya hanya mengecek `isMekanik()` tanpa memeriksa `assigned_mechanic_id`.
3. Pelanggan belum memiliki alur pembatalan mandiri untuk booking servis berstatus `pending` atau `confirmed`, padahal dijamin dalam PRD §5.1.
4. `ServiceObserver::updated` memicu mutasi sekunder `recalculate()` yang dapat memicu rekursi/konflik dengan guard integritas terminal.
5. Pembuatan booking pada `ServiceBookingController` memicu notifikasi ganda ke Super Admin (manual via controller + otomatis via event observer).

## Decision
Menerapkan arsitektur Tri-Tier State Machine, pertahanan integritas berlapis (*defense-in-depth*), serta dedicated pipeline untuk pembatalan servis:

1. **Tri-Tier State Machine:**
   - **Tier 1 (Pure State Graph):** [`App\Enums\ServiceStatus`](../../app/Enums/ServiceStatus.php) mengelola definisi enum, label, visual color, relasi transisi yang sah (`allowedTransitions`, `canTransitionTo`), serta predikat murni `isTerminal()` dan `canBeCancelled()`.
   - **Tier 2 (Authorization Boundary):** [`App\Policies\ServicePolicy`](../../app/Policies/ServicePolicy.php) menegakkan otorisasi kepemilikan data (`assigned_mechanic_id` untuk mekanik, `customer_id` untuk pelanggan, dan Super Admin) serta langsung menolak aksi mutasi pada tiket berstatus terminal (`403 Forbidden`).
   - **Tier 3 (Domain Mutation Engine):** [`App\Services\ServiceStateMachine`](../../app/Services/ServiceStateMachine.php) memusatkan eksekusi transisi status dalam transaksi database atomik, memvalidasi guard kelengkapan item (`items()->count() > 0` sebelum selesai), mencatat timestamp resmi (`confirmed_at`, `started_at`, `completed_at`, `cancelled_at`), alasan pembatalan, dan mengunci biaya final `total_cost`.

2. **Dedicated Cancellation Pipeline:**
   - Menambahkan route `PATCH /dashboard/services/{service}/cancel` dengan FormRequest terpisah [`App\Http\Requests\CancelServiceRequest`](../../app/Http/Requests/CancelServiceRequest.php) yang memvalidasi `cancellation_reason` (minimal 5 karakter).
   - Menyediakan modal pembatalan interaktif untuk pelanggan pada `dashboard/partials/customer.blade.php` dan `dashboard/services/show.blade.php`.

3. **Defense-in-Depth Cost & Mutation Locking:**
   - **Policy Layer:** Menolak `addItem`, `removeItem`, `updateStatus` pada status terminal (`403`).
   - **Domain Service Layer:** `ServiceStateMachine` menghitung dan menetapkan biaya final saat status `completed`. `ServiceCostCalculator::recalculate` mengabaikan mutasi jika status servis terminal.
   - **Model Integrity Guard:** `ServiceItem::booted()` melarang create/update/delete item pada servis terminal. `Service::booted()` melarang pembaruan `status` atau `total_cost` pada servis yang sudah berstatus terminal.

4. **Eliminasi Mutasi Sekunder & Duplikasi Notifikasi:**
   - Menghapus pemanggilan `recalculate()` di dalam `ServiceObserver::updated` untuk mencegah siklus pembaruan pada status terminal.
   - Menghapus pengiriman notifikasi manual di `ServiceBookingController`, memusatkan seluruh notifikasi lifecycle servis ke event observer `ServiceStatusChanged` dan listener `SendServiceStatusNotification`.

## Consequences
- Work order yang telah selesai (`completed`) atau dibatalkan (`cancelled`) mustahil diubah kembali, baik lewat controller, API JSON, seeder, background job, maupun perintah Tinker.
- Pelanggan dapat membatalkan booking berstatus `pending` atau `confirmed` secara mandiri dari antarmuka web, dan riwayat alasan tercatat rapi pada `service_status_logs`.
- Super Admin menerima tepat satu notifikasi resmi per booking baru.
- Seluruh aturan transisi dan integritas teruji penuh melalui `tests/Feature/ServiceStateMachineTest.php` dan unit test `tests/Unit/EnumsTest.php`.
