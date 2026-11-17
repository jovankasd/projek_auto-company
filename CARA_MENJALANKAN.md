# Panduan Menjalankan Proyek Dealer Pro (AUTO COMPANY)

Panduan ini menjelaskan langkah menjalankan **Dealer Pro (AUTO COMPANY)** di komputer Windows lokal dengan dua pilihan database: **SQLite** (bawaan `.env.example`, langsung jalan tanpa aplikasi tambahan) atau **MySQL** (menggunakan **XAMPP** / **Laragon**).

---

## 1. Prasyarat Sistem

Pastikan perangkat lunak berikut sudah terpasang di Windows:

- **PHP 8.2+** (termasuk ekstensi `pdo_sqlite`, `pdo_mysql`, `gd`, `fileinfo`, `mbstring`, `openssl`, `xml`)
- **Composer 2.x**
- **Node.js 20+** & **npm**
- **Opsional (jika memakai MySQL):** **XAMPP** atau **Laragon** dengan service **MySQL** berjalan di port `3306`

---

## 2. Pengaturan Pertama Kali (Clone Baru dari GitHub)

Jika Anda baru mengunduh proyek dari GitHub, buka Terminal (**PowerShell** atau **Git Bash**) di dalam folder proyek (`d:\xyz-motorsv2`) lalu jalankan:

```powershell
# 1. Pasang paket PHP & Node.js
composer install
npm install

# 2. Salin konfigurasi contoh dan buat App Key
copy .env.example .env
php artisan key:generate
```

### Pilihan Database A — SQLite Bawaan (Tercepat)

File `.env.example` secara bawaan sudah memakai `DB_CONNECTION=sqlite`. Anda tidak perlu menyalakan XAMPP atau membuat database manual:

```powershell
# Buat file database SQLite (jika belum ada), migrasi tabel, dan isi data demo lokal
if (!(Test-Path database\database.sqlite)) { New-Item database\database.sqlite -ItemType File }
php artisan migrate
php artisan db:seed --class=DemoSeeder
php artisan storage:link
npm run build
```

### Pilihan Database B — MySQL (XAMPP / Laragon)

Jika Anda ingin memakai **MySQL** dengan nama database `xyz_motors`:

1. Buka **XAMPP Control Panel** (atau Laragon) dan klik **Start** pada service **MySQL** (port `3306`).
2. Buat database kosong bernama `xyz_motors` via **phpMyAdmin** (`http://localhost/phpmyadmin`) atau terminal MySQL.
3. Ubah konfigurasi koneksi database di file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xyz_motors
DB_USERNAME=root
DB_PASSWORD=
```

4. Jalankan migrasi, seeder demo, symlink penyimpanan, dan build aset:

```powershell
php artisan migrate
php artisan db:seed --class=DemoSeeder
php artisan storage:link
npm run build
```

> **Catatan:** Data demo (`DemoSeeder`) hanya berjalan jika `APP_ENV=local` atau `testing`. Seeder ini otomatis mengisi pengaturan sistem, 5 unit mobil showroom, 15 suku cadang OEM, 2 promo aktif, contoh work order & test drive, serta akun demo **4 peran staf**.

---

## 3. Menjalankan Website Lokal

Buka Terminal di dalam folder proyek, lalu pilih salah satu mode:

### Opsi A — Mode Lengkap (`composer run dev`)
Menjalankan server HTTP Laravel, queue listener, log Pail, dan Vite dev server dalam satu perintah:

```powershell
composer run dev
```

### Opsi B — Server Laravel Standar (`php artisan serve`)

```powershell
php artisan serve
```

> Jika Anda mengubah tampilan atau skrip frontend (`resources/css`, `resources/js`, atau view Blade), pastikan `npm run dev` berjalan atau jalankan `npm run build`. Jika memakai notifikasi antrean (*queue*), jalankan `php artisan queue:work` di terminal terpisah.

### Daftar URL Akses Aplikasi

- **Halaman Utama Showroom & Pengajuan Tanpa Login:** `http://127.0.0.1:8000`
- **Cek Tiket Publik (`SRV-...` / `TD-...`):** `http://127.0.0.1:8000/#lacak-tiket`
- **Halaman Detail Unit Mobil:** `http://127.0.0.1:8000/cars/{slug}`
- **Halaman Hubungi Kami:** `http://127.0.0.1:8000/contact` (atau `/kontak`)
- **Halaman Legal:** `http://127.0.0.1:8000/privacy-policy` dan `http://127.0.0.1:8000/terms-of-service`
- **Halaman Login Portal Staf (URL Tersembunyi):** `http://127.0.0.1:8000/logind` (atau `/login`)

---

## 4. Portal Staf & Akun Demo Lokal

Pengunjung showroom dan pelanggan bengkel **tidak memerlukan akun atau login**. Pengunjung langsung mengajukan **Test Drive** dan **Booking Servis** dari beranda (`/`) atau halaman detail mobil (`/cars/{slug}`), lalu memantau progres menggunakan nomor tiket (`TD-...` atau `SRV-...`) di bagian **Cek Tiket (`/#lacak-tiket`)**.

Oleh karena itu, navbar publik sengaja tidak menampilkan tombol *Daftar* atau *Masuk* bagi pelanggan. Untuk masuk ke dasbor operasional internal, buka **`http://127.0.0.1:8000/logind`** dan gunakan salah satu akun demo staf di bawah ini (seluruh kata sandi demo adalah `password`):

| Peran Staf | Email | Kata Sandi | Ruang Kerja & Akses |
|:---|:---|:---|:---|
| **Owner** | `owner@xyzmotors.id` | `password` | Melihat ringkasan eksekutif, kinerja divisi Sales & Bengkel, serta laporan finansial detail. |
| **Super Admin** | `superadmin@xyzmotors.id` | `password` | Mengelola penuh mobil, promo, suku cadang, penugasan mekanik servis, manajemen akun staf (`/dashboard/users`), laporan eksekutif, audit log, dan pengaturan sistem. |
| **Sales** | `sales@xyzmotors.id` | `password` | Memproses lead Test Drive (*approve* / *reject* / *complete* / *cancel*), melihat foto SIM terotorisasi, menghubungi pelanggan via WhatsApp, serta mengelola unit mobil, promo, dan suku cadang. |
| **Admin Mekanik** | `mekanik1@xyzmotors.id` / `mekanik2@xyzmotors.id` / `mekanik@xyzmotors.id` | `password` | Mengelola antrean Work Order bengkel, memperbarui status perbaikan, mencatat item jasa/suku cadang, dan mengelola stok gudang suku cadang. |

---

## 5. Reset Data ke Kondisi Awal

Jika setelah mencoba fitur Anda ingin mereset seluruh tabel (unit, suku cadang, tiket servis, test drive, pengguna) kembali ke data demo bersih:

```powershell
php artisan migrate:fresh --seeder=DemoSeeder
```

---

## 6. Menjalankan Pengujian & Pemeriksaan Kualitas Kode

```powershell
# 1. Seluruh unit & feature test Pest
php artisan test --compact

# 2. Pemeriksaan gaya kode PSR-12 dengan Laravel Pint
.\vendor\bin\pint --test

# 3. Analisis statis PHPStan (Larastan Level 6)
.\vendor\bin\phpstan analyse --memory-limit=1G
```

---

## 7. Solusi Masalah Umum

1. **Error koneksi database MySQL (`SQLSTATE[HY000] [2002]`)**
   - **Penyebab:** `.env` diset ke `DB_CONNECTION=mysql` tetapi service **MySQL** di XAMPP/Laragon belum menyala, atau database `xyz_motors` belum dibuat.
   - **Solusi:** Nyalakan **MySQL** dan buat database `xyz_motors`, atau ubah `DB_CONNECTION=sqlite` di `.env` agar memakai SQLite lokal.

2. **Tampilan berantakan atau animasi showroom tidak muncul**
   - **Penyebab:** Aset Vite belum dikompilasi ke `public/build`.
   - **Solusi:** Jalankan `npm run build` di terminal.

3. **Gambar mobil/suku cadang tidak tampil (`404` pada `/storage/...`)**
   - **Penyebab:** Symbolic link `public/storage` belum dibuat.
   - **Solusi:** Jalankan `php artisan storage:link`.

4. **Tiket Test Drive / Servis baru belum memicu log email**
   - **Penyebab:** `QUEUE_CONNECTION=database` di `.env` memerlukan worker untuk memproses antrean.
   - **Solusi:** Jalankan `php artisan queue:work` (atau gunakan `composer run dev`), lalu cek log email di `storage/logs/laravel.log` ketika `MAIL_MAILER=log`.
