# ADR-008: Portal Khusus 4 Peran Staf (`/logind`) & Pelacakan Tiket Publik Tamu (`SRV-` / `TD-`)

- **Status:** Accepted
- **Tanggal:** 2026-11-15
- **Menggantikan Sebagian:** [ADR-001](0001-laravel-monolith-with-blade-and-alpine.md), [ADR-002](0002-enum-rbac-with-policies-and-middleware.md), [ADR-004](0004-observer-and-queued-multi-channel-notifications.md), [ADR-005](0005-polling-live-tracking-over-websockets.md), [ADR-007](0007-guest-booking-private-license-storage-and-inventory-integrity.md)

## Konteks

Pada rancangan awal (v1.0), platform memodelkan **5 peran pengguna yang login ke dasbor** termasuk `Customer` yang harus masuk ke akun untuk melihat progres di `/dashboard/my-services/{service}` dan `/dashboard/my-test-drives`. Dalam penyelesaian akhir pengalaman showroom:

1. Pelanggan showroom publik tidak memerlukan akun pribadi maupun proses daftar/masuk untuk menjadwalkan test drive, memesan servis bengkel, atau memeriksa status tiket.
2. Antarmuka publik (`public-navbar`, `public-footer`, dan modal konfirmasi showroom) perlu bebas dari ajakan login/registrasi pelanggan dan mengarahkan pengunjung langsung ke fitur **Cek Tiket (`/#lacak-tiket`)**.
3. Portal dasbor (`/dashboard`) dan halaman login (`/logind`) ditujukan khusus sebagai **Portal Staf Internal** dengan 4 peran aktif: `Owner`, `SuperAdmin`, `Sales`, dan `Mekanik`.

## Keputusan

1. **Alur Tamu Tanpa Akun (*Guest-First*) & Pelacakan Tiket Publik**:
   - Pengunjung publik langsung mengajukan Test Drive (`POST /test-drive` -> nomor tiket `TD-YYYYMMDD-XXXX`) dan Booking Servis (`POST /service-booking` -> nomor tiket `SRV-YYYYMMDD-XXXX`) tanpa akun atau sesi autentikasi.
   - Pengunjung memantau progres pengerjaan dan histori status secara mandiri melalui widget `#lacak-tiket` (`GET /track-ticket`, dilindungi `throttle:30,1`) baik untuk tiket servis maupun test drive.
   - Navbar dan footer publik hanya menampilkan navigasi ke **Cek Tiket (`/#lacak-tiket`)** tanpa tautan *Login* atau *Register* pelanggan.

2. **Portal Masuk & Manajemen Pengguna Khusus 4 Peran Staf Internal**:
   - Halaman masuk (`/logind` & alias `/login`) diberi label **"Masuk Portal Staf"** untuk personel internal (`Owner`, `SuperAdmin`, `Sales`, `Mekanik`).
   - `UserController::staffRoles()` dan `UserController::index()` menyaring `UserRole::Customer` sehingga halaman `/dashboard/users` (*Manajemen Pengguna & Staf*) hanya mengelola **4 peran staf aktif**.

3. **Kompatibilitas Skema Internal (`User::resolveGuestCustomer()`)**:
   - Di lapisan skema database dan kode warisan, nilai enum `UserRole::Customer` tetap dipertahankan agar `User::resolveGuestCustomer()` dapat menautkan pengajuan tamu ke entitas kontak pasif (`is_active = false`, fallback domain `@guest.xyzmotors.id`) demi menjaga integritas *foreign key* `customer_id` pada tabel `services` dan `test_drives`.
   - Akun kontak tamu dengan `is_active = false` ditolak secara ketat pada saat autentikasi (`LoginRequest::authenticate()`) dan setiap permintaan HTTP terotentikasi (`EnsureUserIsActive`).
   - Sejumlah rute dan policy kompatibilitas warisan (seperti `my-services`, `my-test-drives`, dan `TestDrivePolicy`/`ServicePolicy` pemilik internal) tetap dipertahankan di kode backend untuk menjaga kompatibilitas suite pengujian regresi yang sudah ada, namun tidak lagi diekspos sebagai peran portal pengguna akhir.

## Konsekuensi

### Positif
- Pengunjung showroom dapat memesan Test Drive, memesan Servis, dan melacak progres (`SRV-`/`TD-`) dalam satu alur tanpa hambatan pembuatan akun.
- Permukaan autentikasi publik berkurang drastis karena akun tamu bersifat pasif (`is_active = false`) dan manajemen staf hanya mencakup 4 peran internal dealer.
- Dokumentasi proyek (`README.md`, `CARA_MENJALANKAN.md`, `ARCHITECTURE.md`, `CHANGELOG.md`) selaras penuh dengan perilaku situs final tanpa mencantumkan `Customer` sebagai peran login aktif.

### Perlu Diingat
- Saat menambahkan migrasi atau membersihkan enum `UserRole::Customer` di masa depan jika skema *foreign key* diubah menjadi nullable/tabel `customers` terpisah, suite pengujian regresi (`RbacTest`, `DashboardTest`, `RoleNavigationAuditTest`, `ServiceTrackingTest`) dan `User::resolveGuestCustomer()` perlu dimigrasikan bersamaan.
