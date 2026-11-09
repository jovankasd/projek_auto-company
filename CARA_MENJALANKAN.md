# Panduan Menjalankan Proyek — Dealer Pro (XYZ Motors)

Panduan ini berisi langkah-langkah lengkap dan mudah diikuti untuk menjalankan website **Dealer Pro (XYZ Motors)** di komputer lokal Anda menggunakan **XAMPP**.

> **Catatan Kondisi Proyek Saat Ini:**
> Proyek ini sudah dikonfigurasi untuk langsung terhubung ke **MySQL XAMPP** dengan nama database `xyz_motors`. Tabel database beserta seluruh data awal (akun demo, katalog 5 mobil showroom, 15 suku cadang lengkap dengan foto, promo, dan contoh servis) sudah disiapkan.

---

## Daftar Isi
1. [Persiapan Awal (Prasyarat)](#1-persiapan-awal-prasyarat)
2. [Cara Cepat Menjalankan Website (Direkomendasikan)](#2-cara-cepat-menjalankan-website-direkomendasikan)
3. [Menjalankan Sekaligus Queue Notifikasi & Vite Dev Server](#3-menjalankan-sekaligus-queue-notifikasi--vite-dev-server)
4. [Cara Alternatif: Mengakses via Apache XAMPP (`localhost`)](#4-cara-alternatif-mengakses-via-apache-xampp-localhost)
5. [Daftar Akun Login Demo (Semua Divisi)](#5-daftar-akun-login-demo-semua-divisi)
6. [Cara Mengakses Database di phpMyAdmin](#6-cara-mengakses-database-di-phpmyadmin)
7. [Cara Reset Data ke Kondisi Awal](#7-cara-reset-data-ke-kondisi-awal)
8. [Perintah Pengujian & Build (Untuk Developer)](#8-perintah-pengujian--build-untuk-developer)
9. [Mengatasi Masalah Umum (Troubleshooting)](#9-mengatasi-masalah-umum-troubleshooting)

---

## 1. Persiapan Awal (Prasyarat)

Pastikan di komputer Windows Anda sudah terpasang dan aktif:
- **XAMPP** (dengan service **MySQL** menyala)
- **PHP 8.2+** *(terdeteksi PHP 8.4 pada environment Anda)*
- **Node.js & NPM** *(opsional, hanya dibutuhkan jika Anda mengubah file CSS/JS atau menjalankan multi-proses dengan `npx`)*

Folder `vendor/` (library PHP), `node_modules/` (library JS), dan `public/build/` **tidak disimpan di repositori Git**. Jika Anda melakukan `git clone` dari GitHub, jalankan terlebih dahulu di terminal proyek:

```powershell
composer install
npm install
npm run build
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
```

*(Jika Anda memakai paket folder lokal yang sudah terpasang lengkap di `d:\xyz-motorsv2`, langkah di atas dapat dilewati).*

---

## 2. Cara Cepat Menjalankan Website (Direkomendasikan)

Metode paling bersih dan bebas error URL adalah menjalankan server bawaan Laravel sambil menyalakan MySQL di XAMPP.

### Langkah 1: Nyalakan MySQL di XAMPP
1. Buka aplikasi **XAMPP Control Panel**.
2. Pada baris **MySQL**, klik tombol **Start** hingga statusnya berubah menjadi hijau (port `3306`).

### Langkah 2: Buka Terminal di Folder Proyek
1. Buka **PowerShell**, **Command Prompt (CMD)**, atau Terminal di VS Code / editor Anda.
2. Arahkan ke folder proyek ini:
   ```powershell
   cd d:\xyz-motorsv2
   ```

### Langkah 3: Jalankan Server Website
Ketik perintah berikut lalu tekan **Enter**:
```powershell
php artisan serve
```

Jika berhasil, terminal akan menampilkan pesan seperti:
```text
INFO  Server running on [http://127.0.0.1:8000].
Press Ctrl+C to stop the server
```

### Langkah 4: Buka di Browser
Buka Google Chrome / Edge / Firefox Anda, lalu kunjungi alamat:
- **Halaman Utama (Landing Page & Katalog Publik):** `http://127.0.0.1:8000` *(bersih untuk pengunjung tanpa tombol Masuk/Daftar)*
- **Halaman Login Tersembunyi (Staf / Admin / Akun):** `http://127.0.0.1:8000/logind`

> **Penting:** Jangan menutup jendela terminal selama Anda sedang membuka website. Untuk mematikan server, tekan **`Ctrl + C`** di terminal.

---

## 3. Menjalankan Sekaligus Queue Notifikasi & Vite Dev Server

Aplikasi ini menggunakan sistem antrean (`QUEUE_CONNECTION=database`) untuk mengirim notifikasi saat ada *Booking Servis*, *Test Drive*, atau pengiriman laporan ke *Owner*. Jika Anda ingin menguji fitur notifikasi lonceng secara langsung, nyalakan juga *queue worker*.

Anda cukup menjalankan **satu perintah** ini di terminal (`d:\xyz-motorsv2`) untuk menyalakan Server + Queue Worker sekaligus:

```powershell
npx concurrently "php artisan serve" "php artisan queue:listen --tries=1" --names=server,queue
```

Atau jika Anda sedang mengedit tampilan (Blade/CSS/JS) dan ingin browser otomatis memperbarui CSS/JS tanpa build manual:

```powershell
npx concurrently "php artisan serve" "php artisan queue:listen --tries=1" "npm run dev" --names=server,queue,vite
```

---

## 4. Cara Alternatif: Mengakses via Apache XAMPP (`localhost`)

Jika Anda lebih suka mengakses website lewat Apache XAMPP (`http://localhost/xyz-motorsv2`) tanpa mengetik `php artisan serve`:

1. Pastikan folder proyek berada di dalam folder `htdocs` XAMPP (misalnya `C:\xampp\htdocs\xyz-motorsv2`), **atau** buat *Directory Junction* dari `htdocs` ke folder proyek ini dengan membuka CMD sebagai Administrator:
   ```cmd
   mklink /J C:\xampp\htdocs\xyz-motorsv2 D:\xyz-motorsv2
   ```
2. Di **XAMPP Control Panel**, klik **Start** pada **Apache** dan **MySQL**.
3. Pastikan `mod_rewrite` di Apache aktif (klik **Config** di baris Apache -> `httpd.conf`, pastikan baris `LoadModule rewrite_module modules/mod_rewrite.so` tidak diawali tanda `#`).
4. Buka browser ke alamat:
   `http://localhost/xyz-motorsv2`

*(Catatan: Untuk pengalaman rute `/login`, `/dashboard`, dan API pelacakan tanpa kendala subfolder, menggunakan **`php artisan serve`** di Langkah 2 tetap sangat disarankan).*

---

## 5. Daftar Akun Login Demo (Semua Divisi)

Demi menjaga tampilan halaman publik tetap bersih bagi pengunjung showroom, tombol *Masuk* dan *Daftar Akun* disembunyikan dari halaman utama. Untuk masuk ke dashboard, ketik langsung URL **`http://127.0.0.1:8000/logind`** di browser Anda dan gunakan salah satu akun di bawah ini:

> **Password untuk semua akun demo:** `password`

| Divisi / Peran (Role) | Email | Password | Hal yang Bisa Diuji |
|---|---|---|---|
| **Super Admin** | `superadmin@xyzmotors.id` | `password` | Mengelola 3 divisi (Sales, Admin Mekanik, Customer), menugaskan mekanik ke servis, menyetujui Test Drive, mengelola user/promo/pengaturan, mengirim laporan resmi ke Owner. |
| **Admin Mekanik 1** | `mekanik1@xyzmotors.id` *(atau `mekanik@xyzmotors.id`)* | `password` | Melihat antrean *Work Order* bengkel, melihat katalog 15 suku cadang dengan foto studio, memasang suku cadang ke mobil servis, mengubah status pengerjaan. |
| **Admin Mekanik 2** | `mekanik2@xyzmotors.id` | `password` | Akun mekanik kedua untuk menguji penugasan antar-mekanik oleh Super Admin. |
| **Owner** | `owner@xyzmotors.id` | `password` | Memantau laporan eksekutif yang dikirim Super Admin, melihat nilai aset mobil & gudang suku cadang, serta grafik pendapatan. |
| **Sales** | `sales@xyzmotors.id` | `password` | Menyetujui/menolak pengajuan Test Drive pelanggan, mengelola unit mobil showroom dan kode promo. |
| **Customer** | `customer@xyzmotors.id` | `password` | Mengajukan Test Drive, melakukan Booking Servis, dan memantau progres perbaikan mobil secara *real-time* (*Live Tracking*). |

---

## 6. Cara Mengakses Database di phpMyAdmin

Jika Anda ingin melihat isi tabel (users, cars, spare_parts, services, test_drives, dll.):
1. Di **XAMPP Control Panel**, pastikan **Apache** dan **MySQL** berstatus hijau (**Running**).
2. Klik tombol **Admin** di baris **MySQL**, atau buka **`http://localhost/phpmyadmin`** di browser.
3. Pada panel sebelah kiri, klik database bernama **`xyz_motors`**.

Konfigurasi koneksi saat ini tersimpan di file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xyz_motors
DB_USERNAME=root
DB_PASSWORD=
```

---

## 7. Cara Reset Data ke Kondisi Awal

Jika setelah mencoba berbagai fitur Anda ingin mengembalikan seluruh data (stok suku cadang, status servis, jadwal test drive, akun) kembali bersih seperti semula:

```powershell
# Menghapus semua tabel, membuat ulang tabel, dan mengisi data seeder awal
php artisan migrate:fresh --seed

# Membersihkan cache aplikasi agar data terbaru langsung tampil
php artisan cache:clear
```

---

## 8. Perintah Pengujian & Build (Untuk Developer)

Jika Anda memodifikasi kode atau ingin memastikan keseluruhan sistem berjalan tanpa error:

```powershell
# 1. Menjalankan seluruh unit & feature test (Pest/PHPUnit)
php artisan test

# 2. Mengompile aset CSS & JavaScript untuk produksi (Vite)
npm run build

# 3. Mengecek tipe data & bug statis (PHPStan Level 6)
.\vendor\bin\phpstan analyse --memory-limit=1G

# 4. Mengecek & merapikan format kode sesuai standar PSR-12 (Laravel Pint)
.\vendor\bin\pint --test
```

---

## 9. Mengatasi Masalah Umum (Troubleshooting)

### 1. Error: `SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it`
- **Penyebab:** Service **MySQL** di XAMPP belum dinyalakan.
- **Solusi:** Buka **XAMPP Control Panel** lalu klik **Start** pada **MySQL**.

### 2. Error: `Vite manifest not found at: public\build\manifest.json`
- **Penyebab:** Aset frontend belum di-build atau server `npm run dev` dimatikan saat build belum ada.
- **Solusi:** Jalankan perintah berikut satu kali di terminal:
  ```powershell
  npm run build
  ```

### 3. Port `8000` Sudah Digunakan Aplikasi Lain
- **Solusi:** Jalankan server Laravel pada port lain, misalnya port `8080`:
  ```powershell
  php artisan serve --port=8080
  ```
  Lalu buka `http://127.0.0.1:8080` di browser.

### 4. Gambar Baru yang Diunggah Tidak Muncul (404)
- **Penyebab:** Shortcut `public/storage` terputus atau belum ada.
- **Solusi:** Jalankan:
  ```powershell
  php artisan storage:link
  ```

### 5. Notifikasi Lonceng di Dashboard Belum Masuk Setelah Submit Booking / Laporan
- **Penyebab:** *Queue worker* tidak sedang berjalan saat pengiriman data.
- **Solusi:** Jalankan worker di terminal terpisah (atau gunakan perintah `npx concurrently` pada Bagian 3):
  ```powershell
  php artisan queue:listen --tries=1
  ```
