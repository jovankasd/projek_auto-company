# XYZ Motors — Platform Portal Otomotif & Layanan Terintegrasi

[![CI Pipeline](https://github.com/jovankasd/projek_auto-company/actions/workflows/ci.yml/badge.svg)](https://github.com/jovankasd/projek_auto-company/actions/workflows/ci.yml)
[![Pest Tests](https://img.shields.io/badge/Pest-116%20passed-brightgreen.svg)](https://pestphp.com)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%206-blue.svg)](https://phpstan.org)
[![Laravel Pint](https://img.shields.io/badge/Code%20Style-PSR--12%20via%20Pint-orange.svg)](https://laravel.com/docs/pint)
[![License](https://img.shields.io/badge/License-MIT-black.svg)](LICENSE)

**XYZ Motors** adalah platform portal otomotif berbasis Laravel 11 yang menggabungkan showroom mobil interaktif 2.5D, katalog suku cadang (*sparepart*), pengajuan test drive dengan penugasan sales otomatis, booking servis kendaraan dengan validasi kuota per slot jam, manajemen work order bengkel, pelacakan status servis berkala (*live status tracking*), serta sistem notifikasi multi-kanal (Database, Email, WhatsApp).

Aplikasi ini dapat dijalankan melalui **`php artisan serve`**, **Laragon**, maupun **XAMPP** (lihat juga panduan khusus Windows/XAMPP di [CARA_MENJALANKAN.md](CARA_MENJALANKAN.md)).

---

## Quick Start (Dari GitHub)

### Prasyarat
- **PHP 8.2+** dengan ekstensi standar Laravel (`bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_mysql`/`pdo_sqlite`, `tokenizer`, `xml`, `zip`)
- **Composer 2.5+**
- **Node.js 18+** dan **NPM**
- **MySQL 8.0+ / MariaDB** (atau SQLite untuk pengembangan lokal cepat)

### Langkah Instalasi

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
   Sesuaikan konfigurasi database (`DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) pada `.env` jika menggunakan MySQL, atau biarkan `DB_CONNECTION=sqlite` untuk langsung memakai SQLite.
4. **Migrasi & isi data awal (seeder)**
   ```bash
   php artisan migrate:fresh --seed
   # Opsional untuk pengembangan lokal: isi akun & katalog demo melalui DemoSeeder
   php artisan db:seed --class=DemoSeeder
   php artisan storage:link
   ```
5. **Build aset frontend & jalankan server pengembangan**
   ```bash
   npm run build
   composer run dev
   ```
   *(Atau jalankan server tunggal dengan `php artisan serve`, lalu akses `http://127.0.0.1:8000` dan login staf di `http://127.0.0.1:8000/logind`.)*

---

## Perintah Umum (Commands)

| Perintah | Deskripsi |
|---|---|
| `composer run dev` | Menjalankan server Laravel, queue listener, pail log, dan Vite dev server bersamaan |
| `php artisan serve` | Menjalankan web server pengembangan Laravel di `http://127.0.0.1:8000` |
| `php artisan queue:listen --tries=1` | Menjalankan worker antrean untuk notifikasi asinkron (Email & WhatsApp) |
| `npm run dev` | Menjalankan Vite dev server dengan hot reload CSS/JS |
| `npm run build` | Mengompilasi aset Tailwind CSS & JavaScript (Vite) untuk produksi |
| `php artisan test` | Menjalankan seluruh test suite Pest (Unit & Feature) |
| `./vendor/bin/pint --test` | Memeriksa kepatuhan format kode PSR-12 dengan Laravel Pint |
| `./vendor/bin/phpstan analyse --memory-limit=1G` | Menjalankan analisis statis Larastan Level 6 |

---

## Fitur Utama Platform

### 1. Showroom 2.5D & Katalog Mobil Interaktif
- Eksplorasi katalog mobil (`SUV`, `Sedan`, `Sports`, `EV`, `MPV`) dengan animasi slider Swiper + GSAP dan filter kategori.
- Spesifikasi teknis terperinci (daya kuda, torsi, akselerasi, transmisi) serta tampilan kartu promo aktif dengan kode voucher.
- Pengisian data katalog demo lokal dilakukan melalui `DemoSeeder` (`CarSeeder` / `php artisan db:seed --class=DemoSeeder`).

### 2. Katalog & Manajemen Sparepart Mobil
- Klasifikasi 10 kategori suku cadang: **Mesin**, **Rem**, **Suspensi**, **Kelistrikan**, **Bodi**, **Interior**, **Transmisi**, **Knalpot**, **Pendingin**, dan **Aksesoris**.
- Kompatibilitas multi-brand & model mobil, manajemen stok, peringatan stok menipis (*low stock alert*), dan pencarian SKU.

### 3. Pengajuan Test Drive
- Formulir pengajuan dengan input nomor SIM A, unggahan foto SIM, dan lokasi jemput.
- **Round-Robin Assignment**: Penugasan otomatis prospek ke tim Sales aktif dengan beban antrean paling sedikit.
- Persetujuan atau penolakan oleh Sales disertai catatan dan alasan.

### 4. Booking Servis & Manajemen Kuota Bengkel
- Pembatasan kuota bengkel maksimal 3 kendaraan per slot jam (`BookingSlotValidator`).
- Validasi anti *double-booking* berdasarkan nomor polisi per tanggal dan slot jam.
- Generator nomor tiket servis unik harian berurutan berformat `SRV-YYYYMMDD-XXXX` (`TicketNumberGenerator`).

### 5. Work Order & Pelacakan Servis
- Siklus status terstruktur melalui `ServiceStatus`: `Pending` → `Confirmed` → `In Progress` → `Completed` (atau `Cancelled` sebelum pengerjaan dimulai).
- Penambahan dinamis item suku cadang, *consumable*, dan jasa (*labor*) dengan kalkulasi ulang total biaya (`ServiceCostCalculator`).
- Halaman pelacakan pelanggan `/dashboard/my-services/{id}` dengan polling JSON setiap 30 detik ke `/dashboard/services/{id}/status-stream`.

### 6. Notifikasi Multi-Kanal & Keamanan RBAC
- 9+ kelas notifikasi antrean (`ShouldQueue`) ke kanal **Database**, **Email**, dan **WhatsApp Gateway** (`log`, `fonnte`, `twilio`).
- 5 peran pengguna berbasis enum `UserRole`: **Customer**, **Sales**, **Mekanik**, **Super Admin**, dan **Owner**.
- Middleware `SecurityHeaders` dan `AuditObserver` untuk pencatatan aktivitas perubahan model kritis.

### 7. Optimasi SEO, Keamanan Perayapan & Halaman Kepercayaan (Trust Pages)
- **Sitemap Dinamis (`/sitemap.xml`)**: Generator peta situs XML yang hanya mengindeks URL publik aktif (Beranda, Detail Mobil, Kontak, Kebijakan Privasi, Syarat & Ketentuan) tanpa menyertakan rute internal atau data privat.
- **Perlindungan Direktori Privat (`robots.txt` & Meta Noindex)**: Perlindungan penuh area autentikasi dan dasbor staf via `robots.txt` dan tag `<meta name="robots" content="noindex, nofollow">` pada seluruh layout terotentikasi.
- **Halaman Legal & Transparansi Data**: Kebijakan Privasi (`/privacy-policy`) dan Syarat & Ketentuan (`/terms-of-service`) resmi dengan penjelasan jujur mengenai tata kelola berkas foto SIM, hak privasi, dan batasan operasional bengkel.
- **Integritas Seeding & Bootstrapping**: Pengisian data akun demo lokal dikelola melalui `DemoSeeder` dan `DatabaseBootstrapper` secara terkendali pada environment lokal/testing, tanpa manipulasi otomatis yang mengubah state pada environment produksi.

---

## Arsitektur & Dokumentasi

Ringkasan desain sistem dan catatan keputusan teknis tersedia di direktori `docs/`:

- **Ikhtisar Arsitektur**: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) — Diagram lapisan aplikasi, matriks RBAC, state machine servis/test drive, dan alur notifikasi.
- **Architecture Decision Records (ADRs)**: [docs/decisions/README.md](docs/decisions/README.md)
  - [ADR-001](docs/decisions/0001-laravel-monolith-with-blade-and-alpine.md): Laravel 11 Monolith dengan Blade, Tailwind CSS, dan Alpine.js
  - [ADR-002](docs/decisions/0002-enum-rbac-with-policies-and-middleware.md): Kontrol Akses Berbasis Peran (RBAC) dengan Enum, Middleware, dan Policies
  - [ADR-003](docs/decisions/0003-service-state-machine-and-booking-guards.md): Mesin Status Servis, Validasi Kuota Slot, dan Generator Tiket Atomik
  - [ADR-004](docs/decisions/0004-observer-and-queued-multi-channel-notifications.md): Observer & Event-Driven Queued Multi-Channel Notifications
  - [ADR-005](docs/decisions/0005-polling-live-tracking-over-websockets.md): Live Tracking Berbasis HTTP Polling dengan Jalur Upgrade WebSocket
  - [ADR-006](docs/decisions/0006-seo-trust-pages-and-honest-documentation.md): Optimasi SEO, Peta Situs XML, Isolasi Perayapan Area Privat, dan Halaman Kepercayaan
- **Panduan Deployment Produksi**: [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) — Konfigurasi server Ubuntu, Nginx, Supervisor, cron scheduler, dan `deploy.sh`.
- **Panduan Kontribusi**: [docs/CONTRIBUTING.md](docs/CONTRIBUTING.md) — Standar kode, branching workflow, konvensi commit, dan pengujian Pest.
- **Panduan Lokal Windows/XAMPP**: [CARA_MENJALANKAN.md](CARA_MENJALANKAN.md)
- **Catatan Perubahan**: [CHANGELOG.md](CHANGELOG.md)

---

## Akun Demo Lokal (Seeded Users)

> **Catatan Keamanan:** Akun bawaan di bawah ini disediakan oleh `UserSeeder` (melalui `DemoSeeder`) **hanya untuk lingkungan pengembangan/demo lokal**. Jangan gunakan kata sandi bawaan ini pada server produksi publik.

Semua akun demo menggunakan kata sandi: **`password`**

| Peran (Role) | Alamat Email | Hak Akses Utama |
|---|---|---|
| **Owner** | `owner@xyzmotors.id` | Dashboard eksekutif finansial, total pemasukan, nilai aset mobil & sparepart, grafik omzet |
| **Super Admin** | `superadmin@xyzmotors.id` | Akses operasional penuh, penugasan mekanik, manajemen user/promo/pengaturan, kirim laporan ke Owner |
| **Sales** | `sales@xyzmotors.id` | Manajemen katalog mobil, sparepart, promo, serta review & persetujuan test drive |
| **Mekanik** | `mekanik1@xyzmotors.id`, `mekanik2@xyzmotors.id`, `mekanik@xyzmotors.id` | Antrean work order, update status pengerjaan, input item suku cadang & jasa |
| **Customer** | `customer@xyzmotors.id` | Pengajuan test drive & booking servis, pelacakan status servis |

---

## Pengujian Otomatis & Analisis Statis

```bash
# 1. Menjalankan seluruh test suite Pest
php artisan test --compact

# 2. Memeriksa format kode PSR-12 (Laravel Pint)
./vendor/bin/pint --test

# 3. Menjalankan analisis tipe statis (PHPStan / Larastan Level 6)
./vendor/bin/phpstan analyse --memory-limit=1G

# 4. Build aset frontend
npm run build
```

---

## Lisensi

Platform XYZ Motors dilisensikan di bawah [MIT License](LICENSE).
