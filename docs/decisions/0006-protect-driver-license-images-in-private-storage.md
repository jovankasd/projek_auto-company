# ADR-006: Penyimpanan Privat dan Otorisasi Akses Foto SIM Test Drive

- **Status:** Accepted
- **Tanggal:** 2026-11-09
- **Pemilik:** Antigravity Team
- **Rujukan:** Audit 2026-11-09 (#6), PRD §3.1-3.3, §7.4, §8

---

## Konteks

Sebelumnya, berkas foto Surat Izin Mengemudi (SIM) yang diunggah saat pengajuan test drive disimpan pada disk publik Laravel (`Storage::disk('public')->store('licenses')`). Tautan berkas disajikan langsung menggunakan URL aset statis (`asset('storage/' . $path)`) pada antarmuka Sales.

Kondisi tersebut menimbulkan risiko privasi dan keamanan data identitas pelanggan:
1. Setiap pihak yang mengetahui atau menebak URL berkas dapat mengunduh foto SIM tanpa otorisasi.
2. Tidak ada penegakan kebijakan kontrol akses (IDOR/RBAC) pada berkas sensitif tersebut.
3. Berkas di-cache secara publik oleh browser atau proxy perantara.

## Keputusan

1. **Penyimpanan Privat Bawaan (`local`):**
   - Seluruh unggahan foto SIM baru dialihkan dan disimpan pada disk privat bawaan Laravel (`local` dengan root path `storage/app/private/licenses`).
   - Berkas tidak dapat diakses secara langsung melalui web server statis.

2. **Endpoint Terotorisasi (`test-drives.license-image`):**
   - Akses foto SIM dialihkan melalui endpoint terautentikasi dan terotorisasi di `TestDriveController@showLicenseImage`.
   - Menggunakan policy `TestDrivePolicy@viewDriverLicense` yang secara ketat hanya mengizinkan:
     - Super Admin
     - Sales yang ditugaskan pada sesi test drive terkait (`sales_id === user->id`)
     - Customer pemilik pengajuan test drive (`customer_id === user->id`)
   - Permintaan dari pengguna lain (mekanik, owner, customer lain, atau sales lain) ditolak dengan HTTP 403 Forbidden.
   - Permintaan tanpa login dialihkan ke halaman login (atau HTTP 401 unauthenticated).

3. **Header Keamanan Respons:**
   - Menyertakan header `X-Content-Type-Options: nosniff`.
   - Menyertakan header `Cache-Control: private, no-cache, no-store, must-revalidate` dan `Pragma: no-cache` untuk mencegah penyimpanan cache di shared proxy/browser publik.
   - Menolak percobaan path traversal (`..`) dengan HTTP 404.

4. **Prosedur Migrasi Berkas Lama (`test-drives:protect-license-photos`):**
   - Disediakan command Artisan untuk menginventarisasi berkas di `public/licenses`, mencocokkan referensi database, menyalin ke disk privat, dan memverifikasi integritasnya menggunakan checksum SHA-256.
   - Penghapusan salinan publik dilindungi opsi eksplisit `--delete-public` dan konfirmasi interaktif (atau `--force` untuk skrip otomasi).

5. **Defense in Depth pada Nginx:**
   - Ditambahkan aturan pemblokiran langsung lokasi `/storage/licenses/` pada konfigurasi Nginx produksi untuk mencegah kebocoran sisa berkas publik.

## Konsekuensi

- **Positif:** Perlindungan privasi data identitas (SIM) sesuai standar keamanan informasi dan PRD; bebas celah IDOR; aman dari cache publik.
- **Dampak Operasional:** Pada lingkungan produksi eksisting, jalankan `php artisan test-drives:protect-license-photos --delete-public --force` saat deployment untuk memindahkan berkas lama.
