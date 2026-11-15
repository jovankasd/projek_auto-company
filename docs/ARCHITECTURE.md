# Arsitektur Sistem — Dealer Pro (XYZ Motors)

Dokumen ini menjelaskan arsitektur perangkat lunak, pola desain, alur data tamu (*guest-first*), mesin status (*state machine*), dan matriks otorisasi staf yang berjalan pada platform portal otomotif **Dealer Pro (XYZ Motors)**.

## 1. Gambaran Umum Monolith Berlapis

Aplikasi dibangun dengan **Laravel 11**, **Blade**, **Tailwind CSS**, dan **Alpine.js** (dibundel via **Vite** bersama **Swiper** dan **GSAP** untuk panggung showroom 2.5D). Catatan alasan tiap keputusan teknis tersedia di [Architecture Decision Records (ADRs)](decisions/README.md).

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       PRESENTATION LAYER (HTTP / UI)                    │
│  Public Showroom (Swiper + GSAP) • /cars/{slug} • /contact • Legal      │
│  Lacak Tiket Publik (#lacak-tiket) • Portal Staf 4 Peran (/logind)      │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │ Web / Auth / RoleMiddleware (RBAC)
                                     │ EnsureUserIsActive / SecurityHeaders
                                     ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                         APPLICATION CONTROLLERS                         │
│   HomeController • CarController • SparePartController • PromoController │
│   ServiceBookingController • ServiceController • TestDriveController     │
│   DashboardController • UserController • ReportController • Sitemap     │
└──────┬─────────────────────────────┬──────────────────────────────┬─────┘
       │                             │                              │
       ▼                             ▼                              ▼
┌───────────────────┐     ┌──────────────────────┐     ┌──────────────────┐
│  DOMAIN SERVICES  │     │    ELOQUENT MODELS   │     │ SECURITY/SUPPORT │
│ • SlotValidator   │     │ • Service • Item     │     │ • User::resolve  │
│ • CostCalculator  │     │ • TestDrive • Car    │     │   GuestCustomer  │
│ • TicketGenerator │     │ • Promo • SparePart  │     │ • Policies       │
│ • ImageOptimizer  │     │ • AuditLog • Setting │     │ • FormRequests   │
└───────────────────┘     └──────────┬───────────┘     └──────────────────┘
                                     │ Eloquent Lifecycle Events
                                     ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                       OBSERVERS & DOMAIN EVENTS                         │
│  • CarObserver (Cache invalidation showroom_cars)                       │
│  • ServiceObserver ──► ServiceStatusLogs + ServiceStatusChanged         │
│  • TestDriveObserver ──► TestDriveStatusChanged                         │
│  • AuditObserver ──► AuditLogs (created, updated, deleted)              │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │ Dispatched to Database Queue
                                     ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                     QUEUED LISTENERS & CHANNELS                         │
│  • SendServiceStatusNotification (Tries: 3, Timeout: 30s)               │
│  • SendTestDriveStatusNotification (Tries: 3, Timeout: 30s)             │
│  ──► Channels: Database (In-App), Mail, WhatsAppChannel (Log/Fonnte/Tw.)│
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Model Akses: Tamu Publik (*Guest-First*) & 4 Peran Staf Internal

Pada versi final platform, pelanggan akhir adalah **pengunjung publik (tamu) tanpa akun login portal**, sedangkan autentikasi `/logind` (*"Masuk Portal Staf"*) dan manajemen akun `/dashboard/users` (`UserController::staffRoles()`) dibatasi secara eksklusif untuk **4 peran staf internal aktif** berbasis enum [`App\Enums\UserRole`](../app/Enums/UserRole.php): `Sales`, `Mekanik`, `SuperAdmin`, dan `Owner`.

### Matriks Hak Akses Publik & Staf

| Fitur / Modul | Tamu Publik (Guest) | Sales | Admin Mekanik | Super Admin | Owner |
|:---|:---:|:---:|:---:|:---:|:---:|
| **Showroom 2.5D, Detail Unit (`/cars/{slug}`), Kontak, Legal** | Lihat & Kirim Form | - | - | - | - |
| **Pengajuan Test Drive (`POST /test-drive`)** | Kirim Tanpa Login (`TD-...`) | Review, Setujui, Tolak, Selesai, Batal | - | Akses Penuh | Metrik Ringkas |
| **Booking Servis (`POST /service-booking`)** | Kirim Tanpa Login (`SRV-...`) | - | Lihat Antrean | Akses Penuh & Assign Mekanik | Metrik Ringkas |
| **Lacak Tiket Publik (`GET /track-ticket`)** | Cek Tiket `SRV-` / `TD-` | - | - | - | - |
| **Work Order & Stok Suku Cadang (`/dashboard/services/{id}`)** | - | - | Update Status & Item (Unit Ditugaskan) | Akses Penuh | Lihat Detail |
| **Katalog Mobil & Promo (`/dashboard/cars`, `/promos`)** | Lihat di Showroom | CRUD Penuh | - *(403 Forbidden)* | CRUD Penuh | Lihat Ringkasan |
| **Gudang Suku Cadang (`/dashboard/spare-parts`)** | Lihat Katalog Publik | CRUD | CRUD & Potong Stok Work Order | CRUD Penuh | Lihat Ringkasan |
| **Foto SIM A Privat (`/dashboard/test-drives/{id}/license`)** | - | Lead Sendiri / Belum Bertuan | - *(403 Forbidden)* | Akses Penuh | - *(403 Forbidden)* |
| **Pengguna Staf, Audit Log, Pengaturan** | - | - *(403)* | - *(403)* | CRUD Staf (`staffRoles`) & Penuh | - *(403)* |
| **Laporan Eksekutif & Keuangan (`/dashboard/reports`)** | - | - *(403)* | - *(403)* | Akses Penuh & Kirim Rekap | Akses Penuh (Read) |

> **Catatan Kejujuran Implementasi (`UserRole::Customer` Internal):** Di dalam skema database, enum `UserRole::Customer` tetap dipertahankan sebagai nilai relasi internal bagi fungsi `User::resolveGuestCustomer()`. Saat tamu mengirim booking tanpa akun, sistem menautkannya ke entitas kontak pasif dengan `is_active = false` agar integritas *foreign key* (`services.customer_id` dan `test_drives.customer_id`) terjaga tanpa membuka akses login pelanggan. `LoginRequest` dan `EnsureUserIsActive` otomatis menolak login apa pun dari akun berstatus `is_active = false`. Beberapa rute/policy warisan Laravel Breeze dan fitur internal tetap dijaga demi kompatibilitas pengujian regresi otomatis.

---

## 3. Mesin Status (*State Machines*)

### A. Siklus Hidup Work Order Servis (`App\Enums\ServiceStatus`)

```
            (Submit Tamu / Booking)
                       │
                       ▼
                 [ pending ]
                 /         \
   (Assign / Confirm)    (Cancel: sebelum in_progress)
               /             \
              ▼               ▼
       [ confirmed ] ──► [ cancelled ]
             │
       (Mulai Kerjakan Mekanik)
             ▼
      [ in_progress ]  <── Tambah / Hapus Suku Cadang & Jasa (Stok Atomik DB)
             │
     (Selesai: wajib >= 1 service_item)
             ▼
       [ completed ]
```

**Penjaga Bisnis (*Business Rules*) Servis:**
1. Pengisian `ServiceItem` tipe `Part` dikunci di dalam `DB::transaction` menggunakan `SparePart::lockForUpdate()`, memvalidasi ketersediaan `stock_quantity`, memaksa `unit_price` dari server, dan mengurangi stok secara atomik (dan mengembalikan stok saat item dihapus sebelum selesai).
2. Status `completed` **ditolak** oleh `ServiceController::updateStatus()` jika `items()->count() === 0`.
3. `in_progress`, `completed`, dan `cancelled` mengunci pembatalan serta perubahan item lebih lanjut (`ServiceStatus::canTransitionTo()`).
4. Validasi slot membaca setting dinamis `booking.open_hour`, `booking.close_hour`, dan `booking.max_per_slot` lewat `BookingSlotValidator` dan `StoreServiceBookingRequest`.

### B. Siklus Hidup Test Drive (`App\Enums\TestDriveStatus`)

```
    (Submit Tamu -> auto-assign Sales + generate tiket TD-YYYYMMDD-XXXX)
                       │
                       ▼
                [ requested ] ──► [ cancelled ]
                 /         \
       (Approve Sales)   (Reject Sales + alasan)
               /             \
              ▼               ▼
        [ approved ]      [ rejected ]
         /         \
(Test Drive Selesai) (Dibatalkan Sales / SuperAdmin)
       /             \
      ▼               ▼
[ completed ]    [ cancelled ]
```

---

## 4. Event-Driven Notifications & Jejak Audit

- **`ServiceObserver`** dan **`TestDriveObserver`** menangkap perubahan status (`wasChanged('status')`), mencatat log kronologis di `service_status_logs`, dan memancarkan event `ServiceStatusChanged` / `TestDriveStatusChanged`.
- **`SendServiceStatusNotification`** dan **`SendTestDriveStatusNotification`** berjalan di background queue (`ShouldQueue`, `$tries = 3`, `$timeout = 30`) dan mengirim notifikasi ke kanal `database`, `mail`, serta `WhatsAppChannel` (driver `log`, `fonnte`, atau `twilio` sesuai `.env`) tanpa menggagalkan transaksi HTTP utama jika gateway eksternal bermasalah.
- **`AuditObserver`** memantau `Car`, `Service`, dan `TestDrive` (`created`, `updated`, `deleted`) untuk dicatat ke tabel `audit_logs` beserta IP address dan user agent.

---

## 5. Alur Pengajuan Tamu, Lacak Tiket Publik & Polling

1. **Pengajuan Tanpa Login & Nomor Tiket Ganda (`SRV-` / `TD-`)**:
   - `TestDriveController::store()` memakai `TicketNumberGenerator::generateTestDrive()` (via `TestDrive::booted()`) untuk menerbitkan nomor tiket harian `TD-YYYYMMDD-XXXX`.
   - `ServiceBookingController::store()` menerbitkan nomor tiket `SRV-YYYYMMDD-XXXX` via `TicketNumberGenerator::generate()`.
   - Modal konfirmasi showroom segera menampilkan nomor tiket untuk disalin (`Salin Tiket`) atau langsung dilacak.
2. **Lacak Tiket Publik (`GET /track-ticket`, `HomeController::trackTicket`)**:
   - Dilindungi rate limiter `throttle:30,1`.
   - Menerima nomor tiket `SRV-...` atau `TD-...`, lalu mengembalikan JSON status terkini, persentase progres, detail unit/kendaraan, jadwal, mekanik/sales penanggung jawab, rincian item/biaya servis, dan histori transisi `statusLogs`.
3. **Notifikasi Real-Time Dasbor Staf**:
   - Topbar dasbor staf melakukan polling JSON ke `GET /dashboard/notifications/unread-count` setiap **60 detik**.

---

## 6. Keamanan HTTP, Privasi Berkas & SEO

- **`SecurityHeaders`**: Menetapkan header `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `X-XSS-Protection: 1; mode=block`, `Permissions-Policy`, `Content-Security-Policy`, dan HSTS (`Strict-Transport-Security`) pada koneksi aman / produksi.
- **`EnsureUserIsActive`**: Memeriksa status `is_active` akun secara langsung ke database pada setiap request dan memutus sesi seketika saat akun dinonaktifkan (`User::revokeAccess()`).
- **Penyimpanan Privat Foto SIM**: Berkas foto SIM A pendaftar Test Drive disimpan ke disk `local` (`storage/app/private/licenses/`) yang tidak dapat diakses via URL publik langsung, dan hanya dapat dilihat oleh `SuperAdmin` atau `Sales` berwenang lewat endpoint terotorisasi `GET /dashboard/test-drives/{testDrive}/license`.
- **SEO & Privasi Mesin Pencari**: Seluruh halaman staf dan autentikasi (`layouts/dashboard`, `layouts/guest`, `layouts/app`) menyertakan `<meta name="robots" content="noindex, nofollow">`, didukung aturan blokir di `public/robots.txt`, serta hanya URL showroom publik yang dimuat ke `GET /sitemap.xml`.
