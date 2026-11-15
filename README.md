# Dealer Pro (XYZ Motors) — Platform Portal Otomotif & Layanan Bengkel

[![CI Pipeline](https://github.com/jovankasd/projek_auto-company/actions/workflows/ci.yml/badge.svg)](https://github.com/jovankasd/projek_auto-company/actions/workflows/ci.yml)
[![Pest Tests](https://img.shields.io/badge/Pest-131%20passed-brightgreen.svg)](https://pestphp.com)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%206-blue.svg)](https://phpstan.org)
[![Laravel Pint](https://img.shields.io/badge/Code%20Style-PSR--12%20via%20Pint-orange.svg)](https://laravel.com/docs/pint)
[![License](https://img.shields.io/badge/License-MIT-black.svg)](LICENSE)

**Dealer Pro (XYZ Motors)** adalah platform portal otomotif monolith berbasis **Laravel 11**, **Blade**, **Tailwind CSS**, dan **Alpine.js** yang menyatukan showroom mobil interaktif 2.5D (Swiper + GSAP), katalog suku cadang OEM, pengajuan test drive dan booking servis tanpa wajib login dengan pelacakan tiket publik (`SRV-` / `TD-`), manajemen *work order* bengkel dengan penguncian stok atomik, dasbor operasional untuk 5 peran pengguna, serta notifikasi asinkron multi-kanal (Database, Email, WhatsApp).

Untuk panduan khusus pengguna Windows/XAMPP, lihat [CARA_MENJALANKAN.md](CARA_MENJALANKAN.md).

---

## Quick Start

### Prasyarat
- **PHP 8.2+** dengan ekstensi Laravel (`bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_mysql`/`pdo_sqlite`, `tokenizer`, `xml`, `zip`)
- **Composer 2.5+**
- **Node.js 18+** (direkomendasikan 20 LTS) dan **NPM**
- **SQLite** (bawaan `.env.example`) atau **MySQL 8.0+ / MariaDB**

### Langkah Instalasi Lokal

1. **Clone repositori**
   ```bash
   git clone https://github.com/jovankasd/projek_auto-company.git
   cd projek_auto-company
   ```

2. **Install dependensi PHP & JavaScript**
   ```bash
   composer install
   npm install
   ```

3. **Siapkan environment & application key**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   > Pengguna Windows CMD/PowerShell dapat memakai `copy .env.example .env`. Secara default `.env.example` memakai `DB_CONNECTION=sqlite`; sesuaikan `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` bila menggunakan MySQL/XAMPP.

4. **Jalankan migrasi & pengisian data**
   ```bash
   php artisan migrate --seed
   # Opsional (khusus APP_ENV=local/testing): isi akun & katalog demo
   php artisan db:seed --class=DemoSeeder
   php artisan storage:link
   ```
   `DatabaseSeeder` hanya menjalankan `SettingSeeder` sehingga aman dijalankan di produksi. `DemoSeeder` dan `UserSeeder` memiliki penjaga lingkungan (*environment guard*) agar hanya berjalan di `local` atau `testing`.

5. **Kompilasi aset & jalankan server pengembangan**
   ```bash
   npm run build
   composer run dev
   ```
   Atau jalankan server Laravel saja dengan `php artisan serve`, lalu akses `http://127.0.0.1:8000` (halaman publik) dan `http://127.0.0.1:8000/logind` (atau `/login`) untuk masuk ke dasbor.

---

## Commands

### Pengembangan, Build & Pengujian

| Perintah | Deskripsi |
|---|---|
| `composer run dev` | Menjalankan `php artisan serve`, `queue:listen`, `pail`, dan `npm run dev` secara paralel |
| `php artisan serve` | Menjalankan server pengembangan Laravel di `http://127.0.0.1:8000` |
| `php artisan queue:listen --tries=1` | Memproses antrean notifikasi asinkron (Database, Email, WhatsApp) |
| `npm run dev` | Menjalankan Vite dev server dengan hot reload CSS/JS |
| `npm run build` | Mengompilasi bundle produksi CSS/JS (`public/build/`) |
| `php artisan test --compact` | Menjalankan seluruh test suite Pest (Unit & Feature) |
| `./vendor/bin/pint --test` | Memeriksa kepatuhan format kode PSR-12 via Laravel Pint |
| `./vendor/bin/phpstan analyse --memory-limit=1G` | Menjalankan analisis statis Larastan Level 6 |

### Perintah Console & Penjadwalan (`routes/console.php`)

| Perintah | Jadwal / Eksekusi | Fungsi |
|---|---|---|
| `php artisan services:remind-upcoming` | Harian `08:00` | Mengirim pengingat jadwal servis H-1 kepada pelanggan |
| `php artisan test-drives:remind-upcoming` | Harian `08:30` | Mengirim pengingat jadwal test drive H-1 ke pelanggan dan sales |
| `php artisan promos:deactivate-expired` | Setiap jam | Menonaktifkan promo yang melewati batas tanggal `ends_at` |
| `php artisan media:optimize {directory=public/storage}` | Manual | Mengonversi dan mengoptimasi gambar media ke format WebP |

---

## Fitur Website

### 1. Showroom 2.5D & Katalog Kendaraan Publik
- Eksplorasi showroom interaktif berbasis Swiper & GSAP pada `/` dengan filter kategori (`SUV`, `Sedan`, `Sports`, `EV`, `MPV`) dan dukungan `prefers-reduced-motion` serta tombol jeda/putar carousel.
- Halaman detail kendaraan publik (`/cars/{car:slug}`) menampilkan spesifikasi teknis, stok, promo aktif terkait, serta modal reservasi.
- Katalog suku cadang OEM (10 kategori `SparePartCategory`) dengan pencarian nama/SKU dan indikator stok menipis.

### 2. Reservasi Tanpa Wajib Login & Pelacakan Tiket Publik
- Pengunjung dapat langsung mengajukan **Test Drive** (`POST /test-drive`) dan **Booking Servis** (`POST /service-booking`) tanpa wajib mendaftar lebih dulu; `User::resolveGuestCustomer()` menautkan data ke entitas pelanggan sementara (`is_active = false`) agar tidak membuka celah login.
- **Test Drive**: Penugasan otomatis ke *Sales* aktif via *round-robin* beban terendah, unggahan foto SIM A ke disk `local` terisolasi, nomor tiket harian `TD-YYYYMMDD-XXXX`, serta alur status `Requested → Approved / Rejected / Cancelled → Completed`.
- **Booking Servis**: Validasi jam operasional dan kuota maksimal per slot jam (`BookingSlotValidator`, membaca pengaturan dinamis `booking.open_hour`, `booking.close_hour`, dan `booking.max_per_slot`), pencegahan *double-booking* nomor polisi pada slot yang sama, serta generator tiket harian terkunci `SRV-YYYYMMDD-XXXX` (`TicketNumberGenerator`).
- **Pelacakan Tiket Publik (`#lacak-tiket` / `GET /track-ticket`)**: Pengunjung dapat memeriksa status tiket `SRV-...` maupun `TD-...` langsung dari showroom tanpa login (dilindungi `throttle:30,1`).

### 3. Work Order Bengkel, Integritas Stok & Live Tracking
- Siklus status servis mengikuti *state machine* pada `ServiceStatus`: `Pending → Confirmed → InProgress → Completed`, atau `Cancelled` sebelum pengerjaan dimulai. Status terminal (`Completed`, `Cancelled`) mengunci seluruh mutasi item dan penugasan ulang mekanik.
- Penambahan item suku cadang (`ServiceController::addItem`) mengambil harga satuan resmi dari tabel `SparePart` di server, memotong stok secara atomik dengan `lockForUpdate()`, dan mengembalikan stok saat item dihapus (`removeItem`), lalu menghitung ulang `total_cost` lewat `ServiceCostCalculator`.
- Halaman pelacakan pelanggan (`/dashboard/my-services/{service}`) melakukan polling JSON setiap 30 detik ke `/dashboard/services/{service}/status-stream`, sedangkan lonceng notifikasi melakukan polling setiap 60 detik.

### 4. Dasbor Multi-Peran & Notifikasi Multi-Kanal
- **5 Peran Berbasis Enum `UserRole`**: `Customer`, `Sales`, `Mekanik`, `SuperAdmin`, dan `Owner`, dengan ruang kerja, metrik KPI, dan otorisasi terpisah.
- **Laporan Eksekutif**: Rekapitulasi layanan, kinerja mekanik, unit populer, dan pengiriman laporan resmi dari `SuperAdmin` ke `Owner` (`/dashboard/reports`).
- **Notifikasi Antrean (`ShouldQueue`)**: Pengiriman paralel ke kanal **Database** (Notification Center `/dashboard/notifications`), **Email**, dan **WhatsApp** melalui kontrak `WhatsAppGateway` (driver `log`, `fonnte`, `twilio`).

### 5. SEO, Halaman Kepercayaan & Keamanan
- **SEO & Isolasi Perayapan**: Sitemap XML dinamis (`/sitemap.xml` via `SitemapController`) hanya mencantumkan halaman publik aktif; `/robots.txt` dan `<meta name="robots" content="noindex, nofollow">` melindungi area autentikasi, dasbor, dan berkas privat.
- **Halaman Legal & Kontak**: Halaman Kebijakan Privasi (`/privacy-policy`, `/kebijakan-privasi`), Syarat & Ketentuan (`/terms-of-service`, `/syarat-dan-ketentuan`), serta halaman Kontak (`/contact`, `/kontak`, `POST /contact` dengan *honeypot* `website_url` dan `throttle:5,1`).
- **Perlindungan Akses & Header**: Middleware `SecurityHeaders` (termasuk CSP dan HSTS produksi), `trustProxies('*')`, `EnsureUserIsActive` (pemutusan sesi *real-time* saat akun dinonaktifkan + `User::revokeAccess()`), akses foto SIM terotorisasi (`GET /dashboard/test-drives/{testDrive}/license`), rute login staf `/logind`, dan pencatatan jejak perubahan model kritis melalui `AuditObserver`.

---

## Architecture

Aplikasi ini dibangun sebagai monolith Laravel 11 terstruktur tanpa dependensi SPA terpisah.

### Struktur Direktori Inti

```text
app/
├── Console/Commands/    # Perintah terjadwal (pengingat H-1, promo kedaluwarsa, optimasi media)
├── Enums/               # Enum backed PHP 8.2+ (UserRole, ServiceStatus, TestDriveStatus, dll.)
├── Events/              # Domain events (ServiceStatusChanged, TestDriveStatusChanged)
├── Http/
│   ├── Controllers/     # Controller publik, auth, booking, work order, dashboard, laporan, sitemap
│   ├── Middleware/      # CheckRole, EnsureUserIsActive, SecurityHeaders
│   └── Requests/        # Validasi terpusat (LoginRequest, StoreServiceBookingRequest, dll.)
├── Listeners/           # Queued listeners pengirim notifikasi multi-kanal
├── Models/              # Model Eloquent (Car, SparePart, Promo, Service, TestDrive, Setting, AuditLog)
├── Notifications/       # Kelas notifikasi Database/Mail/WhatsApp & WhatsAppChannel
├── Observers/           # ServiceObserver, TestDriveObserver, CarObserver, AuditObserver
├── Policies/            # Otorisasi granular per resource & kepemilikan data
└── Services/            # TicketNumberGenerator, BookingSlotValidator, ServiceCostCalculator, WhatsAppGateway
docs/
├── decisions/           # Architecture Decision Records (ADR-001 s.d. ADR-007)
├── nginx/               # Contoh konfigurasi Nginx produksi
└── supervisor/          # Konfigurasi worker Supervisor untuk queue
```

### Keputusan Arsitektur (ADRs)

Detail latar belakang, alternatif yang dipertimbangkan, dan konsekuensi setiap keputusan teknis terdokumentasi di [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) dan [`docs/decisions/README.md`](docs/decisions/README.md):

| Dokumen | Topik Keputusan |
|---|---|
| [ARCHITECTURE.md](docs/ARCHITECTURE.md) | Ikhtisar lapisan aplikasi, matriks RBAC, state machine, dan pipeline notifikasi |
| [ADR-001](docs/decisions/0001-laravel-monolith-with-blade-and-alpine.md) | Pemilihan Laravel 11 Monolith dengan Blade, Tailwind CSS, dan Alpine.js |
| [ADR-002](docs/decisions/0002-enum-rbac-with-policies-and-middleware.md) | Kontrol akses berbasis peran (RBAC) menggunakan PHP Enum, Middleware, dan Policies |
| [ADR-003](docs/decisions/0003-service-state-machine-and-booking-guards.md) | Mesin status servis, validasi kuota slot, dan generator tiket atomik |
| [ADR-004](docs/decisions/0004-observer-and-queued-multi-channel-notifications.md) | Pipeline Observer → Event → Queued Listener & kontrak WhatsApp Gateway |
| [ADR-005](docs/decisions/0005-polling-live-tracking-over-websockets.md) | Live tracking berbasis HTTP Polling dengan jalur upgrade WebSocket |
| [ADR-006](docs/decisions/0006-seo-trust-pages-and-honest-documentation.md) | Sitemap XML dinamis, isolasi perayapan area privat, dan halaman kepercayaan |
| [ADR-007](docs/decisions/0007-guest-booking-private-license-storage-and-inventory-integrity.md) | Reservasi guest tanpa wajib login, penyimpanan privat foto SIM, dan integritas stok Work Order |

---

## Konfigurasi Lingkungan & Pengaturan Sistem

- **Queue & Session**: `.env.example` mengonfigurasi `QUEUE_CONNECTION=database` dan `SESSION_DRIVER=database`. Pastikan worker antrean berjalan saat memproses notifikasi Email/WhatsApp secara asinkron.
- **WhatsApp Gateway** (`config/services.php`): Atur `WHATSAPP_ENABLED=true` dan `WHATSAPP_DRIVER=log|fonnte|twilio` beserta `FONNTE_TOKEN` atau kredensial `TWILIO_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_WHATSAPP_FROM`.
- **Pengaturan Operasional Dinamis (`/dashboard/settings`)**: Super Admin dapat mengatur jam operasional (`booking.open_hour`, `booking.close_hour`), kuota slot (`booking.max_per_slot`), kontak (`contact.whatsapp`, `contact.email`, `contact.address`), serta metadata SEO (`seo.site_name`, `seo.tagline`).

---

## Akun Demo Lokal

> **Catatan Keamanan:** Akun berikut hanya dibuat oleh `UserSeeder` / `DemoSeeder` pada lingkungan `local` dan `testing`, dan tidak dibuat otomatis di lingkungan `production` atau `staging`.

Kata sandi seluruh akun demo: **`password`**

| Peran | Email | Fokus Akses |
|---|---|---|
| **Owner** | `owner@xyzmotors.id` | Ringkasan finansial eksekutif, nilai aset unit & suku cadang, laporan resmi |
| **Super Admin** | `superadmin@xyzmotors.id` | Kendali operasional penuh, penugasan mekanik, manajemen user/promo/pengaturan, audit log |
| **Sales** | `sales@xyzmotors.id` | Katalog mobil, suku cadang, promo, serta persetujuan/penyelesaian test drive |
| **Mekanik** | `mekanik@xyzmotors.id`, `mekanik1@xyzmotors.id`, `mekanik2@xyzmotors.id` | Antrean work order, pembaruan status servis, input item suku cadang & jasa |
| **Customer** | `customer@xyzmotors.id` | Pengajuan test drive, booking servis, pembatalan sebelum diproses, pelacakan status |

---

## Pengujian & Deployment

Rangkaian validasi yang dijalankan oleh [CI Pipeline](.github/workflows/ci.yml) sebelum rilis:
- **Pest Test Suite**: `php artisan test --compact` (**131 passed, 1097 assertions**)
- **Code Style**: `./vendor/bin/pint --test` (PSR-12 compliant)
- **Static Analysis**: `./vendor/bin/phpstan analyse --memory-limit=1G` (Larastan Level 6)
- **Frontend Bundle**: `npm run build` (Vite + Tailwind CSS)

Untuk prosedur setup server Ubuntu, Nginx, Supervisor, cron scheduler, dan skrip *zero-downtime deployment* (`deploy.sh`), lihat [Panduan Deployment Produksi](docs/DEPLOYMENT.md).

---

## Contributing

Standar format kode, konvensi pesan commit, alur Pull Request, dan kewajiban pengujian otomatis dijelaskan di [Panduan Kontribusi](docs/CONTRIBUTING.md). Riwayat perubahan lintas rilis tercatat di [CHANGELOG.md](CHANGELOG.md).

---

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).
