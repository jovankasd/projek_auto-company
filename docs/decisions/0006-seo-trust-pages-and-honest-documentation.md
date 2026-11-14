# ADR-006: Optimasi SEO, Peta Situs XML, Isolasi Perayapan Area Privat, dan Halaman Kepercayaan

## Status
Accepted

## Date
2026-11-14

## Context
Berdasarkan PRD §7.4 dan temuan audit #18, portal otomotif XYZ Motors memerlukan tata kelola mesin perayap (crawling) dan keterbukaan informasi legal yang jujur:
1. Mesin pencari (Google, Bing) membutuhkan XML Sitemap (`/sitemap.xml`) yang hanya mencantumkan halaman publik yang valid dan aktif, tanpa mengekspos area autentikasi staf, panel kontrol internal, atau berkas privat (seperti foto kartu SIM).
2. Area dasbor staf, alur autentikasi (login, register, forgot-password), dan direktori penyimpanan privat wajib dilindungi dari pengindeksan publik melalui kombinasi berkas `robots.txt` dan instruksi `<meta name="robots" content="noindex, nofollow">` pada layout Blade terotentikasi.
3. Formulir publik mengumpulkan informasi sensitif (nomor telepon, nomor polisi kendaraan, nomor SIM A, dan unggahan foto kartu SIM). Pengguna berhak atas dokumen Kebijakan Privasi dan Syarat & Ketentuan resmi yang menjelaskan tata kelola, isolasi penyimpanan privat, serta pembatasan akses secara transparan tanpa klaim sertifikasi palsu.
4. Klaim otomatisasi pada `README.md` dan pesan status kosong showroom (*empty state*) harus mencerminkan kapabilitas kode nyata: `DatabaseBootstrapper` berfungsi sebagai utilitas seeding terkendali pada lingkungan lokal/testing dan tidak memicu auto-healing data otomatis di lingkungan produksi.

## Decision
1. **Sitemap XML Dinamis (`/sitemap.xml`)**:
   - Diimplementasikan melalui `SitemapController@index` yang menghasilkan respons XML standar sitemaps.org dengan header `Content-Type: text/xml; charset=UTF-8` dan `X-Robots-Tag: noindex`.
   - Mengindeks Beranda (`/`), Kebijakan Privasi (`/privacy-policy`), Syarat & Ketentuan (`/terms-of-service`), Kontak (`/contact`), serta seluruh unit mobil yang berstatus tersedia (`Car::available()`).
   - Seluruh rute `/dashboard/*`, `/login`, `/register`, `/password/*`, `/profile`, `/storage/licenses/*`, dan kendaraan non-aktif dikecualikan secara mutlak dari sitemap.

2. **Isolasi Perayapan (`public/robots.txt` & Layout Meta Robots)**:
   - Berkas `public/robots.txt` mengizinkan perayapan halaman publik dan membatasi akses direktori sensitif (`/dashboard/`, `/login`, `/register`, `/password/`, `/profile`, `/storage/licenses/`, `/storage/private/`).
   - Menambahkan tag `<meta name="robots" content="noindex, nofollow">` pada layout `resources/views/layouts/guest.blade.php`, `resources/views/layouts/dashboard.blade.php`, dan `resources/views/layouts/app.blade.php`.
   - Menambahkan tag `<link rel="canonical" href="...">` pada seluruh halaman publik (`showroom/index.blade.php`, `cars/show.blade.php`, `privacy-policy.blade.php`, `terms-of-service.blade.php`, `contact/index.blade.php`).

3. **Halaman Kepercayaan & Transparansi Legal**:
   - Menyediakan halaman Kebijakan Privasi (`resources/views/pages/privacy-policy.blade.php`) dan Syarat & Ketentuan (`resources/views/pages/terms-of-service.blade.php`) dengan rute dwibahasa (`/privacy-policy` dan `/kebijakan-privasi`, `/terms-of-service` dan `/syarat-dan-ketentuan`).
   - Menyajikan informasi transparan mengenai penyimpanan privat foto SIM, hak akses terbatas hanya bagi staf yang ditugaskan, dan tidak adanya penjualan data ke pihak ketiga.
   - Menandai secara eksplisit klausul kebijakan yang membutuhkan persetujuan final pemilik bisnis menggunakan format penanda `[PERLU PENGESAHAN PEMILIK: ...]`.

4. **Koreksi Empty State & Dokumentasi Seeder**:
   - Memperbaiki copy pada empty state showroom agar secara jujur menyatakan bahwa data mobil belum tersedia di database, menghilangkan klaim seolah sistem telah menginisialisasi ulang data secara otomatis.
   - Mendokumentasikan fungsi `DatabaseBootstrapper` di `README.md` sebagai pendukung data demo lokal/testing.

## Alternatives Considered

### Sitemap Statis Dibuat Saat Build (Static XML File)
- **Pros:** Tidak membebani runtime PHP.
- **Cons:** URL mobil baru yang ditambah atau dinonaktifkan oleh tim Sales melalui panel dashboard tidak otomatis terbarui tanpa menjalankan command generate build ulang.
- **Rejected:** Query `Car::available()` ringan dan menghasilkan daftar terkini secara instan.

### Menyerahkan Teks Legal pada Template Standar AI Tanpa Penanda Pemilik
- **Pros:** Teks terlihat selesai secara sepintas.
- **Cons:** Menyesatkan pengguna dan manajemen bisnis mengenai batas kewajiban hukum yang sebenarnya (misalnya batas waktu retensi SIM, deductible asuransi, dan yurisdiksi pengadilan).
- **Rejected:** Standar `antislop` (R-36) mewajibkan transparansi penuh dan penandaan parameter hukum yang membutuhkan pengesahan pemilik bisnis.

## Consequences
- Seluruh halaman publik terindeks dengan rapi, memiliki canonical URL yang valid, dan metadata OpenGraph yang konsisten.
- Direktori sensitif dan berkas privasi terlindung dari pengindeksan mesin pencari publik.
- Pengguna dan pemangku kepentingan memiliki dokumen legal yang transparan mengenai tata kelola data pribadi dan aturan operasional showroom/bengkel.
