# ADR-006: Standarisasi Penyimpanan UTC, Presentasi WIB, dan Keandalan Pelaporan Eksekutif

## Status
Accepted

## Date
2026-11-14

## Context
Berdasarkan PRD v3 (§3.1 & metadata) dan hasil temuan audit #17 serta #19:
1. **Perbedaan Konvensi Waktu (Audit #17):** PRD mensyaratkan basis data menyimpan waktu dalam UTC, sementara tampilan pengguna beroperasi dalam zona waktu `Asia/Jakarta` (WIB, UTC+7). Sebelumnya, output waktu sering kali hanya ditempeli string `WIB` secara manual tanpa konversi zona waktu dari UTC. Hal ini menyebabkan distorsi jam (selisih 7 jam) serta kesalahan filter tanggal lintas hari (misalnya, peristiwa pukul 23:30 UTC yang semestinya masuk tanggal esok harinya pukul 06:30 WIB).
2. **Status Pengiriman Laporan dan Pelaporan Sukses Palsu (Audit #19):** Pengiriman laporan eksekutif dari Super Admin ke Owner (`ReportController@submitToOwner`) menelan exception notifikasi dalam blok `try/catch` kosong dan langsung mencatat status sukses terkirim. Jika server notifikasi/mail gagal, Owner tidak menerima laporan namun Super Admin melihat laporan seolah telah resmi terkirim. Tidak tersedia pelacakan status (`pending`, `sent`, `failed`), pencatatan error di log, maupun mekanisme coba ulang (*retry*) yang idempoten.

## Decision
1. **Konvensi Tunggal Zona Waktu:**
   - Standarisasi konfigurasi `config('app.timezone')` dan `.env.example` ke `UTC`.
   - Menggunakan helper terpusat [`App\Support\Timezone`](../../app/Support/Timezone.php) untuk konversi eksplisit ke `Asia/Jakarta` (`Timezone::toWib()`), pemformatan seragam (`Timezone::format()`), serta penentuan batas rentang filter harian UTC (`Timezone::startOfDayUtc()` & `Timezone::endOfDayUtc()`).
   - Mendaftarkan macro Carbon (`toWib()`, `toWibString()`) dan Blade directive `@wib(...)` pada [`App\Providers\AppServiceProvider`](../../app/Providers/AppServiceProvider.php).
   - Menyediakan command migrasi data lama [`App\Console\Commands\MigrateLegacyTimezoneCommand`](../../app/Console/Commands/MigrateLegacyTimezoneCommand.php) (`php artisan timezone:migrate-legacy [--dry-run] [--force]`) untuk instalasi yang telanjur menyimpan data dengan waktu lokal Jakarta.

2. **Keandalan Pelaporan Eksekutif & Retry Idempoten:**
   - Memisahkan status pengiriman laporan menjadi state eksplisit: `pending`, `sent`, `failed` dengan pencatatan `last_report_error` dan `last_report_failed_at` pada model [`App\Models\Setting`](../../app/Models/Setting.php).
   - Memusatkan logika bisnis ke [`App\Services\ExecutiveReportService`](../../app/Services/ExecutiveReportService.php) dan queued job [`App\Jobs\SendExecutiveReportJob`](../../app/Jobs/SendExecutiveReportJob.php).
   - Menghilangkan *error swallowing*: jika notifikasi gagal, status dicatat sebagai `failed`, pesan kegagalan dicatat via `Log::error()`, dan UI menampilkan flash error yang jujur kepada Super Admin.
   - Menyediakan endpoint retry terdedikasi `POST /dashboard/reports/retry-submission` (`reports.retry-submission`). Mekanisme retry bersifat idempoten: apabila laporan sudah berstatus `sent`, sistem menolak pengiriman ulang agar tidak terjadi duplikasi notifikasi ke Owner.
   - Dashboard Owner hanya menampilkan status "Resmi Terkirim" jika status pengiriman laporan bernilai `sent`.

## Consequences
- Semua tampilan waktu di dashboard, riwayat servis, pelacakan, audit log, dan notifikasi konsisten menampilkan jam aktual WIB (Asia/Jakarta) dengan basis data tetap berstandar UTC.
- Pengujian rentang tanggal laporan pada batas pergantian hari (edge case 23:30 UTC / 06:30 WIB) terbukti akurat dan terisolasi dengan baik.
- Status pengiriman laporan ke Owner transparan dan dapat ditindaklanjuti saat terjadi kegagalan jaringan atau ketiadaan akun Owner aktif.
