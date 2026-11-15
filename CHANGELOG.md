# Catatan Perubahan (Changelog) - XYZ Motors

Semua perubahan besar, fitur baru, perbaikan, dan pembaruan arsitektural pada platform otomotif **XYZ Motors** didokumentasikan di sini berdasarkan format [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

### Pemeriksaan Akhir Lintas Alur (Final Release Gate - 14 November 2026)
- **Remediasi Keamanan P0 & Proteksi Data Pribadi (UU PDP)**:
  - Mengalihkan penyimpanan foto SIM pengemudi dari disk `public` ke disk `local` privat terisolasi di `TestDriveController`.
  - Membuat endpoint unduh/streaming aman `GET /dashboard/test-drives/{testDrive}/license` (`test-drives.license`) yang dilindungi otorisasi ketat `viewLicense` (hanya Super Admin, Customer pemilik pengajuan, dan Sales yang ditugaskan).
  - Memperbarui antarmuka dashboard Sales (`sales.blade.php`) agar menggunakan tautan terproteksi baru.
- **Pengetatan Otorisasi (BOLA/IDOR) & Proteksi Rute**:
  - Memperketat `TestDrivePolicy` sehingga Sales hanya dapat melihat, menyetujui, menolak, atau menyelesaikan test drive yang ditugaskan ke dirinya sendiri (`sales_id === $user->id`), mencegah manipulasi lead antar-sales.
  - Memperbaiki deklarasi resource route `promos` dan `users` di `routes/web.php` dengan `->except(['show'])` guna mencegah runtime 500 error akibat metode `show()` yang tidak terdefinisi.
  - Memperbaiki signature type mismatch pada otorisasi AJAX reorder urutan mobil di `CarController::reorder` dan `CarPolicy::reorder`.
  - Menerapkan middleware rate limiting `throttle:6,1` pada `POST /register`, `throttle:5,1` pada `POST /forgot-password`, dan `throttle:10,1` pada `POST /reset-password` di `routes/auth.php`.
  - Mendaftarkan endpoint publik `POST /contact` (`contact.store`) berfitur anti-bot honeypot (`website_url`) dan rate limiting `throttle:5,1` sehingga formulir kontak publik tidak lagi memicu `RouteNotFoundException`.
- **Integritas Work Order, Stok Spare Part & Mesin Status**:
  - Menerapkan *state machine transitions* formal pada enum `TestDriveStatus` (`allowedTransitions()`, `canTransitionTo()`) untuk mencegah perubahan status ilegal dari status terminal (`Rejected`, `Completed`, `Cancelled`).
  - Mengunci work order servis berstatus terminal (`Completed`, `Cancelled`) pada `ServicePolicy` dan `ServiceController` dari operasi penambahan/penghapusan item serta penugasan ulang mekanik.
  - Menjamin integritas harga onderdil di server-side: harga suku cadang diambil langsung dari database `SparePart` (mengabaikan payload harga client) dan stok onderdil otomatis dipotong secara atomik saat ditambahkan ke work order serta dikembalikan saat dihapus.
- **Keamanan Header, Proxy & Antislop**:
  - Menambahkan header `Content-Security-Policy` (CSP) pada `SecurityHeaders.php` serta mengonfigurasi `trustProxies('*')` pada `bootstrap/app.php`.
  - Mengamankan interpolasi variabel string di script pelacakan servis (`track.blade.php`) menggunakan direktif `@js(...)`.
  - Menghapus kode mati `User::getRedirectRoute()` dan membersihkan macro/stub bawaan awal di `tests/Pest.php`.
  - Menyederhanakan generator slug pada `CarSlugGenerator`.
- **Hasil Verifikasi Rilis Akhir**:
  - Test Suite: **125 passed (1004 assertions)** — seluruh unit & feature test 100% hijau.
  - Analisis Statis: PHPStan Level 6 **[OK] No errors**.
  - Gaya Kode: Laravel Pint **passed** (100% compliant PSR-12).
  - Kompilasi Aset: Vite build berhasil tanpa error (CSS 87.6 kB, JS app 107.4 kB, Showroom 161.2 kB).

### Keamanan (Security Hardening - Audit Perbaikan 2026-11-09)
- **01. Hilangkan Kredensial Demo dari UI dan Seeder Produksi** ([PR #1](https://github.com/jovankasd/projek_auto-company/pull/1)):
  - Menghapus kartu kredensial demo, teks kata sandi default `password`, dan autofill JavaScript dari halaman login publik (`resources/views/auth/login.blade.php`).
  - Memisahkan data demo ke seeder terpisah `DemoSeeder` (`php artisan db:seed --class=DemoSeeder`).
  - Menjadikan `UserSeeder` dan seluruh seeder katalog/layanan idempoten (`firstOrCreate` / `updateOrCreate`) tanpa mereset kata sandi atau mengaktifkan kembali akun nonaktif di lingkungan produksi.
  - Penambahan 16 feature test di `DemoCredentialsSecurityTest.php`.
- **02. Tegakkan Status Akun Aktif di Login dan Sesi** ([PR #2](https://github.com/jovankasd/projek_auto-company/pull/2)):
  - Autentikasi `LoginRequest` memverifikasi status aktif via `Auth::attemptWhen()` konstan-waktu dengan pesan generik `auth.failed` untuk mencegah kebocoran informasi.
  - Penambahan global middleware `EnsureUserIsActive` pada grup `web` untuk mendeteksi penonaktifan akun secara *real-time*, memutus sesi aktif, meregenerasi token CSRF, serta mengembalikan 403 Forbidden untuk permintaan JSON.
  - Penambahan method `User::revokeAccess()` untuk mengosongkan `remember_token` dan membersihkan baris sesi aktif di database saat akun dinonaktifkan, dihapus, atau kata sandi diubah.
  - Proteksi mandiri Super Admin dari deaktivasi akun diri sendiri atau demosi peran di `UserController`.
  - Penambahan 18 feature test komprehensif di `ActiveAccountEnforcementTest.php` (278 assertions).
- **17. Lengkapi SEO, Halaman Kepercayaan, dan Dokumentasi** ([PR #17](https://github.com/jovankasd/projek_auto-company/pull/17)):
  - Pembuatan sitemap XML dinamis (`/sitemap.xml`) via `SitemapController` yang hanya mengindeks URL publik nyata (Beranda, Detail Mobil, Kontak, Kebijakan Privasi, Syarat & Ketentuan) tanpa menyertakan area privat.
  - Pengamanan berkas `public/robots.txt` dengan pembatasan area privat (`/dashboard/`, `/login`, `/register`, `/password/`, `/profile`, `/storage/licenses/`, `/storage/private/`).
  - Penambahan `<meta name="robots" content="noindex, nofollow">` pada seluruh layout Blade terotentikasi (`guest.blade.php`, `dashboard.blade.php`, `app.blade.php`).
  - Pembuatan halaman Kebijakan Privasi (`/privacy-policy`) dan Syarat & Ketentuan (`/terms-of-service`) resmi dengan transparansi data booking servis, verifikasi foto SIM, dan penandaan parameter legal `[PERLU PENGESAHAN PEMILIK: ...]`.
  - Penambahan tag `<link rel="canonical" href="...">` konsisten pada seluruh halaman publik showroom, detail mobil, kontak, dan halaman legal.
  - Pembaruan copy empty state showroom agar jujur dan menghilangkan klaim seolah sistem melakukan auto-healing data secara otomatis.
  - Penambahan ADR-006 (`docs/decisions/0006-seo-trust-pages-and-honest-documentation.md`) dan pembaruan `README.md`.
  - Penambahan 6 skenario feature test di `tests/Feature/SeoAndTrustPagesTest.php` (94 assertions).

---

## [1.0.0] - 2026-11-09

### Ditambahkan (Dokumentasi, ADR & Rilis Repositori)
- **Architecture Decision Records (`docs/decisions/`)**:
  - `ADR-001` hingga `ADR-005` mendokumentasikan pilihan arsitektur monolith Laravel 11 + Blade/Alpine, RBAC berbasis enum & policy, mesin status servis & booking guards, observer/queued notifications, serta strategi polling live tracking.
- **Kebersihan Repositori & Lisensi**:
  - Penambahan file `LICENSE` (MIT License) agar selaras dengan `composer.json` dan badge dokumentasi.
  - Penyesuaian `.gitignore` untuk mengecualikan artefak analisis lokal (`graphify-out/`, `anti-slop/`, laporan audit internal).
  - Penyelarasan `README.md`, `docs/ARCHITECTURE.md`, `docs/CONTRIBUTING.md`, dan `docs/DEPLOYMENT.md` dengan kondisi aktual kode serta URL repositori `jovankasd/projek_auto-company`.

### Ditambahkan (Notifications, Testing & Deployment)
- **Multi-Channel Notification System (`ShouldQueue`)**:
  - 9 kelas notifikasi: `ServiceCreated`, `ServiceConfirmed`, `ServiceStarted`, `ServiceCompleted`, `ServiceCancelled`, `TestDriveRequested`, `TestDriveApproved`, `TestDriveRejected`, `TestDriveCompleted`.
  - Integrasi 3 kanal paralel: In-app database notifications, MailMessage email resmi, dan WhatsApp Messaging.
- **WhatsApp Gateway Architecture**:
  - Kontrak interface `WhatsAppGateway`.
  - 3 implementasi driver: `LogWhatsAppGateway` (pengembangan/log), `FonnteGateway` (Fonnte API), dan `TwilioGateway` (Twilio API).
  - Kustom channel notification `WhatsAppChannel`.
- **Event-Driven Lifecycle Listeners**:
  - `ServiceStatusChanged` & `TestDriveStatusChanged` events.
  - Queued listeners `SendServiceStatusNotification` dan `SendTestDriveStatusNotification` dengan fail-safe error handling (`tries=3`, `timeout=30s`).
  - Model observers: `ServiceObserver` dan `TestDriveObserver` untuk memicu event transisi secara otomatis.
- **Real-Time Customer Status Tracking**:
  - Halaman pelacakan visual interaktif `/dashboard/my-services/{id}` dengan progress bar 4 tahap dan visual timeline kronologis.
  - Endpoint polling JSON `/dashboard/services/{id}/status-stream` dengan polling Alpine.js setiap 30 detik.
  - Arsitektur siap upgrade ke WebSocket (Laravel Echo / Reverb).
- **Pusat Notifikasi (Notification Center)**:
  - Halaman terpadu `/dashboard/notifications` dengan tab filter (Semua / Belum Dibaca).
  - Fitur tandai satu atau semua notifikasi telah dibaca secara instan.
  - Topbar Notification Bell dengan polling otomatis 60 detik dan badge counter interaktif.
- **Rangkaian Pengujian Otomatis (Pest Test Suite)**:
  - 76 pengujian komprehensif (Unit & Feature, 355 assertions) dengan tingkat kelulusan 100%.
  - Pengujian generator kode tiket servis (`TicketNumberGenerator`).
  - Pengujian kalkulator subtotal dan total biaya work order (`ServiceCostCalculator`).
  - Pengujian validasi kuota slot jam dan anti double-booking plat kendaraan (`BookingSlotValidator`).
  - Pengujian generator slug unik kendaraan (`CarSlugGenerator`).
  - Pengujian integritas enum (`CarCategory`, `ServiceStatus`, `TestDriveStatus`, `UserRole`, `ServiceItemType`).
  - Pengujian RBAC (Customer, Sales, Mekanik, Super Admin) dengan guard 403 Forbidden.
  - Pengujian Work Order lifecycle, penambahan item, dan guard pembatalan servis.
  - Pengujian notifikasi dan live tracking.
- **Security Hardening**:
  - Middleware `SecurityHeaders` (HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy).
- **Console Scheduler & Automation**:
  - Perintah `services:remind-upcoming` (Pengingat servis H-1).
  - Perintah `test-drives:remind-upcoming` (Pengingat test drive H-1).
  - Perintah `promos:deactivate-expired` (Penonaktifan promo kedaluwarsa otomatis).
- **CI/CD & Deployment Artifacts**:
  - Workflow GitHub Actions CI (`.github/workflows/ci.yml`) mencakup Pint, PHPStan, Pest, dan NPM build.
  - Skrip rilis zero-downtime otomatis `deploy.sh`.
  - Konfigurasi Supervisor worker `docs/supervisor/supervisord.conf`.
  - Konfigurasi produksi Nginx dengan SSL dan caching `docs/nginx/xyz-motors.conf`.
  - Dokumentasi teknis: `docs/ARCHITECTURE.md`, `docs/DEPLOYMENT.md`, `docs/CONTRIBUTING.md`, dan `README.md`.

---

### Ditambahkan (Admin Dashboards & Work Orders)
- **Role-Based Dedicated Dashboards**:
  - Dashboard Customer (Ringkasan servis aktif, riwayat test drive, shortcut booking).
  - Dashboard Sales (Statistik pengajuan test drive, pipeline konversi pelanggan, penugasan otomatis).
  - Dashboard Mekanik (Antrean servis hari ini, update progres pengerjaan, formulir work order).
  - Dashboard Super Admin (KPI eksekutif, pendapatan servis, penugasan mekanik, manajemen user & promo).
  - Dashboard Owner (Ringkasan eksekutif finansial, nilai inventaris, dan penerimaan laporan resmi).
- **Work Order & Sparepart Management**:
  - Dynamic Service Items (Pilihan suku cadang vs jasa perbaikan, kuantitas, harga satuan, subtotal otomatis).
  - Recalculation engine untuk `total_cost` tiket servis saat item ditambah atau dihapus.
  - Audit logging transisi status servis dengan pencatatan `ServiceStatusLog` dan `AuditLog`.

---

### Ditambahkan (Public Portal & Booking Systems)
- **Showroom Mobil Interaktif**:
  - Filter real-time multi-dimensi (Kategori: SUV/Sedan/Sports/EV/MPV, rentang harga, tahun rilis, brand).
  - Tampilan visual kendaraan dengan preview spesifikasi teknis dan warna kendaraan.
- **Wizard Pengajuan Test Drive**:
  - Pilihan unit mobil, pemilihan tanggal & jam, input nomor SIM A.
  - Algoritma round-robin otomatis untuk penugasan tim Sales.
- **Sistem Booking Servis Kendaraan**:
  - Pemilihan tanggal servis, pemilihan slot jam (08:00 - 16:00 WIB).
  - Validasi kuota kapasitas bengkel (maksimal 3 kendaraan per slot jam).
  - Validasi anti double-booking plat kendaraan pada jam yang sama.
  - Generator tiket servis unik berbasis tanggal (`SRV-YYYYMMDD-XXXX`).
- **Katalog Promo & Diskon**:
  - Tampilan kartu promo interaktif dengan kode voucher dan masa berlaku.

---

### Ditambahkan (Foundation & Database Setup)
- Inisialisasi arsitektur proyek Laravel 11 dengan Vite dan Tailwind CSS.
- Migrasi database 12 tabel: `users`, `cars`, `promos`, `services`, `service_items`, `service_status_logs`, `test_drives`, `audit_logs`, `settings`, `notifications`, `cache`, serta `spare_parts`.
- Database Seeder komprehensif (`DatabaseSeeder`) dengan akun default untuk 5 peran dan katalog mobil/sparepart.
- Otentikasi lengkap (Login, Registrasi, Lupa Password, Reset Password, Verifikasi Email, Profil Pengguna).
