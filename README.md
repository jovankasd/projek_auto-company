# Dealer Pro — XYZ Motors Portal Otomotif & Manajemen Bengkel

[![Laravel 11](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Pest Tests](https://img.shields.io/badge/Pest-131%20passed-brightgreen.svg)](https://pestphp.com)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%206-2B5B84.svg)](https://phpstan.org)

**Dealer Pro (XYZ Motors)** adalah platform portal otomotif monolith berbasis **Laravel 11**, **Blade**, **Tailwind CSS**, dan **Alpine.js** yang menyatukan showroom kendaraan interaktif 2.5D (**Swiper** + **GSAP**), katalog suku cadang OEM, pengajuan **Test Drive** dan **Booking Servis** tanpa akun/login dengan pelacakan tiket publik (`TD-` dan `SRV-`), manajemen *work order* bengkel dengan penguncian stok atomik, dasbor operasional khusus **4 peran staf internal**, serta notifikasi asinkron multi-kanal (Database, Email, dan WhatsApp).

Untuk panduan menjalankan proyek di Windows lokal (SQLite maupun MySQL XAMPP/Laragon), lihat [CARA_MENJALANKAN.md](CARA_MENJALANKAN.md).

---

## Prasyarat

- **PHP 8.2+** dengan ekstensi standar Laravel (`bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_sqlite` / `pdo_mysql`, `tokenizer`, `xml`, `zip`)
- **Composer 2.x**
- **Node.js 20+** dan **npm**
- **SQLite** (bawaan `.env.example`, langsung siap pakai) atau **MySQL 8.0+ / MariaDB**

---

## Quick Start (Instalasi Lokal)

```bash
# 1. Kloning repositori
git clone https://github.com/jovankasd/projek_auto-company.git
cd projek_auto-company

# 2. Pasang dependensi backend & frontend
composer install
npm install

# 3. Salin konfigurasi lingkungan & hasilkan app key
cp .env.example .env
php artisan key:generate

# 4. Jalankan migrasi & seeder pengaturan bawaan (SettingSeeder)
php artisan migrate --seed

# Opsional (khusus APP_ENV=local/testing): isi data demo showroom, unit mobil, suku cadang, promo, & akun staf
php artisan db:seed --class=DemoSeeder

# 5. Buat symlink penyimpanan publik & kompilasi aset frontend
php artisan storage:link
npm run build
```

> **Catatan Database & Windows:** Pengguna Windows CMD/PowerShell dapat memakai `copy .env.example .env`. Secara bawaan `.env.example` menggunakan `DB_CONNECTION=sqlite` (`database/database.sqlite`). Jika memakai MySQL/XAMPP/Laragon, sesuaikan `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env` sebelum menjalankan `php artisan migrate --seed`.
>
> **Keamanan Seeder:** `DatabaseSeeder` hanya memanggil `SettingSeeder` sehingga aman di lingkungan produksi. `DemoSeeder` dan `UserSeeder` memiliki penjaga lingkungan (*environment guard*) agar hanya berjalan pada `APP_ENV=local` atau `testing`.

### Menjalankan Server Pengembangan

```bash
# Menjalankan server HTTP Laravel, queue worker, Pail logs, dan Vite dev server bersamaan
composer run dev
```

Atau jalankan server Laravel saja dengan `php artisan serve`, lalu akses:
- **Portal Publik Showroom:** `http://127.0.0.1:8000`
- **Cek Tiket Publik (`SRV-` / `TD-`):** `http://127.0.0.1:8000/#lacak-tiket`
- **Masuk Portal Staf (URL Tersembunyi dari Navbar Publik):** `http://127.0.0.1:8000/logind` (alias `/login`)

---

## Perintah Pengembangan & Pengujian

| Perintah | Deskripsi |
|:---|:---|
| `npm run dev` | Menjalankan server kompilasi Vite (Tailwind CSS + Alpine.js + showroom assets) |
| `npm run build` | Membangun bundle aset produksi di `public/build` |
| `php artisan test --compact` | Menjalankan seluruh test suite Pest (Unit & Feature) |
| `./vendor/bin/pint --test` | Memeriksa kepatuhan gaya kode via Laravel Pint |
| `./vendor/bin/phpstan analyse --memory-limit=1G` | Analisis statis kode `app/` (Larastan Level 6) |
| `php artisan queue:work` | Memproses antrean notifikasi database, email, dan WhatsApp secara asinkron |
| `php artisan media:optimize` | Mengoptimalkan gambar publik di `storage/app/public` ke WebP |

### Tugas Terjadwal (`routes/console.php`)

| Command | Jadwal | Fungsi |
|:---|:---|:---|
| `php artisan services:remind-upcoming` | Harian `08:00` | Mengirim pengingat jadwal servis H-1 kepada kontak pemohon |
| `php artisan test-drives:remind-upcoming` | Harian `08:30` | Mengirim pengingat jadwal test drive H-1 ke kontak pemohon dan sales |
| `php artisan promos:deactivate-expired` | Setiap jam | Menonaktifkan kode promo yang telah melewati `ends_at` |

---

## Fitur Utama & Alur Sistem Terkini

### 1. Showroom Interaktif 2.5D & Halaman Publik SEO
- **Stage 2.5D Showroom (`/`)**: Carousel **Swiper 14** dengan animasi bertahap **GSAP 3.15**, kontrol keyboard (`←`/`→`), tombol jeda/putar otomatis, dukungan penuh `prefers-reduced-motion`, filter kategori (`SPORTS`, `SUV`, `SEDAN`, `MPV`, `EV`), katalog suku cadang, serta program promo aktif.
- **Halaman Detail Unit (`/cars/{slug}`)**: Menampilkan spesifikasi mesin, fitur, stok unit, promo aktif terkait, form pengajuan unit langsung, serta metadata Open Graph, Twitter Card, dan Schema.org JSON-LD (`Product` + `Car`).
- **Halaman Kepercayaan & Sitemap**: Halaman **Hubungi Kami** (`/contact` & `/kontak`, dilindungi *honeypot* `website_url` dan `throttle`), **Kebijakan Privasi** (`/privacy-policy` & `/kebijakan-privasi`), **Syarat & Ketentuan** (`/terms-of-service` & `/syarat-dan-ketentuan`), `GET /sitemap.xml` dinamis, serta `public/robots.txt` yang memblokir rute privat.

### 2. Pengajuan Tamu Tanpa Akun (*Guest-First*) & Cek Tiket Publik
- **Tanpa Login / Tanpa Akun Pelanggan**: Pengunjung showroom publik dapat langsung mengajukan **Test Drive** (`POST /test-drive`) maupun **Booking Servis** (`POST /service-booking`) tanpa membuat akun. Antarmuka publik (`public-navbar` & `public-footer`) tidak memuat tombol registrasi/login pelanggan; navigasi utama diarahkan langsung ke **Cek Tiket**.
- **Test Drive (`TD-YYYYMMDD-XXXX`)**: Penugasan otomatis ke *Sales* aktif via *round-robin* beban terendah, nomor tiket otomatis oleh `TicketNumberGenerator::generateTestDrive()`, penyimpanan foto SIM A terisolasi pada disk privat (`local` -> `storage/app/private/licenses/`), serta mesin status `Requested → Approved / Rejected / Cancelled → Completed`.
- **Booking Servis (`SRV-YYYYMMDD-XXXX`)**: Validasi jam operasional dan kuota slot per jam oleh `BookingSlotValidator` (membaca pengaturan dinamis `booking.open_hour`, `booking.close_hour`, dan `booking.max_per_slot`), pencegahan *double-booking* nomor polisi pada slot jam yang sama, serta generator tiket harian terkunci (`DB::transaction` + `lockForUpdate()`).
- **Lacak Tiket Publik (`/#lacak-tiket` & `GET /track-ticket`)**: Pengunjung dapat memantau progres pengajuan `SRV-...` maupun `TD-...`, jadwal, nama mekanik/sales, rincian biaya/item pengerjaan, dan riwayat log status langsung dari beranda tanpa login (`throttle:30,1`).
- **Resolusi Entitas Kontak Pasif**: `User::resolveGuestCustomer()` menautkan data pemohon ke entitas kontak sementara di tabel `users` dengan status `is_active = false` semata-mata demi integritas relasi *foreign key* (`customer_id`); entitas ini ditolak oleh `LoginRequest` dan `EnsureUserIsActive` agar tidak pernah bisa masuk ke portal staf.

### 3. Work Order Bengkel & Integritas Stok Atomik
- **State Machine `ServiceStatus`**: Transisi tervalidasi (`Pending → Confirmed → InProgress → Completed` serta `Cancelled` hanya sebelum pengerjaan dimulai), lengkap dengan guard penolakan penyelesaian apabila item work order masih kosong dan pencatatan riwayat di `service_status_logs`.
- **Penguncian Stok & Harga Sisi Server**: Penambahan dan penghapusan item suku cadang (`ServiceController::addItem` / `removeItem`) dijalankan dalam transaksi database dengan `lockForUpdate()` pada `SparePart`, mengambil `unit_price` langsung dari database server, memperbarui `stock_quantity` secara atomik, dan menghitung ulang `total_cost` lewat `ServiceCostCalculator`.

### 4. Portal Staf Internal (4 Peran Aktif) & Notifikasi Multi-Kanal
- **Login Tersembunyi Portal Staf (`/logind`)**: Berlabel **"Masuk Portal Staf"** untuk personel internal dealer. Manajemen akun di `/dashboard/users` (`UserController::staffRoles()`) hanya mengelola **4 peran staf aktif**: `Owner`, `SuperAdmin`, `Sales`, dan `Mekanik`.
- **Notifikasi Berbasis Observer**: Perubahan status di `ServiceObserver` dan `TestDriveObserver` memicu event terantre (`ServiceStatusChanged`, `TestDriveStatusChanged`) ke kanal in-app database (topbar badge polling 60 detik), Email, dan `WhatsAppChannel` (`LogWhatsAppGateway` secara default, atau driver `fonnte` / `twilio` via `.env`).
- **Keamanan Lapisan Aplikasi**: Middleware `SecurityHeaders` (CSP, `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, dan HSTS produksi), `trustProxies('*')`, `EnsureUserIsActive` (pemutusan sesi seketika jika akun dinonaktifkan + `User::revokeAccess()`), akses foto SIM terotorisasi (`GET /dashboard/test-drives/{testDrive}/license`), `<meta name="robots" content="noindex, nofollow">` pada layout staf/auth, serta jejak audit di `AuditObserver`.

---

## Arsitektur & Catatan Keputusan (ADR)

```
app/
├── Console/Commands/    # Pengingat jadwal & optimasi media WebP
├── Enums/               # Backed enum PHP 8.2+ (UserRole, ServiceStatus, TestDriveStatus, dll.)
├── Events/              # Domain events (ServiceStatusChanged, TestDriveStatusChanged)
├── Http/
│   ├── Controllers/     # Public showroom, booking, work order, CRUD staf, laporan, sitemap
│   ├── Middleware/      # CheckRole, EnsureUserIsActive, SecurityHeaders
│   └── Requests/        # Validasi form & autentikasi (LoginRequest, StoreServiceBookingRequest, dll.)
├── Listeners/           # Queued notification listeners (tries=3, timeout=30)
├── Models/              # Model Eloquent + relasi & scope domain
├── Notifications/       # Kelas notifikasi database, mail, & WhatsAppChannel
├── Observers/           # CarObserver, ServiceObserver, TestDriveObserver, AuditObserver
├── Policies/            # Otorisasi resource (Car, Promo, Service, SparePart, TestDrive, User)
└── Services/            # TicketNumberGenerator, BookingSlotValidator, ServiceCostCalculator, ImageOptimizer, CarSlugGenerator, DatabaseBootstrapper
```

Dokumentasi arsitektur terperinci tersedia di [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) dan catatan keputusan arsitektur di [docs/decisions/README.md](docs/decisions/README.md):

| ADR | Keputusan Arsitektural | Status |
|:---|:---|:---|
| [ADR-001](docs/decisions/0001-laravel-monolith-with-blade-and-alpine.md) | Monolith Laravel 11 dengan Blade, Tailwind CSS, dan Alpine.js | Accepted *(Diperbarui per [ADR-008](docs/decisions/0008-staff-only-portal-and-guest-ticket-tracking.md))* |
| [ADR-002](docs/decisions/0002-enum-rbac-with-policies-and-middleware.md) | Kontrol Akses Berbasis Peran (RBAC) dengan Enum, Middleware, dan Policies | Superseded in part by [ADR-008](docs/decisions/0008-staff-only-portal-and-guest-ticket-tracking.md) |
| [ADR-003](docs/decisions/0003-service-state-machine-and-booking-guards.md) | Mesin Status Servis, Validasi Kuota Slot, dan Generator Tiket Atomik | Accepted |
| [ADR-004](docs/decisions/0004-observer-and-queued-multi-channel-notifications.md) | Observer + Queued Listeners untuk Notifikasi Multi-Kanal & Audit Trail | Accepted *(Diperbarui per [ADR-008](docs/decisions/0008-staff-only-portal-and-guest-ticket-tracking.md))* |
| [ADR-005](docs/decisions/0005-polling-live-tracking-over-websockets.md) | Polling JSON Ringan untuk Pelacakan & Notifikasi Dibandingkan WebSocket Daemon | Superseded in part by [ADR-008](docs/decisions/0008-staff-only-portal-and-guest-ticket-tracking.md) |
| [ADR-006](docs/decisions/0006-seo-trust-pages-and-honest-documentation.md) | Sitemap Dinamis, Halaman Kepercayaan Publik, Proteksi `noindex`, dan Dokumentasi Jujur | Accepted |
| [ADR-007](docs/decisions/0007-guest-booking-private-license-storage-and-inventory-integrity.md) | Reservasi Guest Tanpa Login, Penyimpanan Privat Foto SIM, dan Integritas Stok Work Order | Accepted *(Diperbarui per [ADR-008](docs/decisions/0008-staff-only-portal-and-guest-ticket-tracking.md))* |
| [ADR-008](docs/decisions/0008-staff-only-portal-and-guest-ticket-tracking.md) | Portal Khusus 4 Peran Staf (`/logind`) & Pelacakan Tiket Publik Tamu (`SRV-` / `TD-`) | Accepted |

---

## Akun Demo Lokal (Portal Staf — `DemoSeeder`)

Kredensial berikut hanya dibuat apabila `php artisan db:seed --class=DemoSeeder` dijalankan di lingkungan `local` atau `testing`, dan hanya dapat masuk melalui halaman **Masuk Portal Staf** (`/logind`):

| Peran Staf | Email | Kata Sandi | Ruang Kerja & Wewenang |
|:---|:---|:---|:---|
| **Owner** | `owner@xyzmotors.id` | `password` | Ringkasan eksekutif, kinerja divisi Sales & Bengkel, serta laporan finansial detail |
| **Super Admin** | `superadmin@xyzmotors.id` | `password` | Manajemen penuh unit mobil, promo, suku cadang, penugasan mekanik, akun staf (`/dashboard/users`), laporan, audit log, dan pengaturan |
| **Sales** | `sales@xyzmotors.id` | `password` | Manajemen lead Test Drive, verifikasi foto SIM terotorisasi, kontak WhatsApp pelanggan, serta katalog mobil, promo, dan suku cadang |
| **Admin Mekanik** | `mekanik1@xyzmotors.id`, `mekanik2@xyzmotors.id`, `mekanik@xyzmotors.id` | `password` | Antrean work order servis, pembaruan status pengerjaan, pengisian item jasa/onderdil, dan manajemen gudang suku cadang |

> Pelanggan akhir tidak memiliki akun portal atau kredensial login. Seluruh reservasi dan pengecekan status pelanggan dilakukan sebagai tamu publik pada beranda (`/`) dan `#lacak-tiket` menggunakan nomor tiket `TD-` atau `SRV-`.

---

## Panduan Lanjutan & Kontribusi

- [Panduan Menjalankan Lokal (Windows / SQLite / XAMPP)](CARA_MENJALANKAN.md)
- [Arsitektur Sistem Terperinci](docs/ARCHITECTURE.md)
- [Panduan Deployment Produksi](docs/DEPLOYMENT.md)
- [Panduan Kontribusi & Standar Kode](docs/CONTRIBUTING.md)
- [Catatan Perubahan Rilis (Changelog)](CHANGELOG.md)

## Lisensi

Perangkat lunak sumber terbuka di bawah lisensi MIT.
