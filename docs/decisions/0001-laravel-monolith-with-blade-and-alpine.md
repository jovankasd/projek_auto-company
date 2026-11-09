# ADR-001: Laravel 11 Monolith dengan Blade, Tailwind CSS, dan Alpine.js

## Status
Accepted

## Date
2026-10-01

## Context
Platform **XYZ Motors** menggabungkan portal publik (showroom mobil 2.5D, katalog suku cadang, promo, pengajuan test drive & booking servis) dengan dashboard operasional multi-peran (Customer, Sales, Mekanik, Super Admin, Owner). Proyek perlu:
- Berjalan di lingkungan pengembangan lokal umum (Laragon, XAMPP, atau `php artisan serve`) serta server Linux standar (Nginx + PHP-FPM) tanpa orkestrasi banyak service frontend/backend terpisah.
- Mendukung SEO server-rendered untuk halaman showroom publik, autentikasi sesi aman bawaan (Laravel Breeze), serta animasi interaktif showroom (Swiper & GSAP).
- Meminimalkan kompleksitas build dan duplikasi validasi antara klien dan server untuk tim kecil.

## Decision
Menggunakan arsitektur **monolith Laravel 11** dengan rendering server-side **Blade**, styling **Tailwind CSS**, interaktivitas ringan **Alpine.js**, serta bundling aset via **Vite** (`resources/js/app.js` dan `resources/js/showroom.js` untuk Swiper/GSAP).

## Alternatives Considered

### Decoupled SPA (React / Vue) + Laravel REST API
- **Pros:** Pemisahan penuh frontend dan backend, pengalaman transisi halaman layaknya aplikasi native.
- **Cons:** Membutuhkan dua pipeline deploy, manajemen token/CORS (Sanctum), duplikasi routing/validasi, serta upaya ekstra untuk SSR/SEO halaman showroom publik.
- **Rejected:** Menambah beban operasional dan kompleksitas deployment untuk kebutuhan portal otomotif ini.

### Inertia.js (React / Vue)
- **Pros:** Tidak perlu membangun REST API terpisah sambil tetap memakai komponen React/Vue.
- **Cons:** Menambah lapisan abstraksi protokol Inertia dan ketergantungan framework JS penuh padahal sebagian besar dashboard bersifat CRUD tabular dan formulir standar.
- **Rejected:** Blade + Alpine.js sudah cukup ekspresif dan lebih ringan dirawat.

### Livewire
- **Pros:** Komponen reaktif berbasis PHP tanpa menulis banyak JavaScript.
- **Cons:** Tidak diperlukan untuk showroom GSAP/Swiper yang memang berbasis DOM/JS murni, serta menambah payload runtime Livewire di setiap halaman.
- **Rejected:** Kombinasi Blade + Alpine.js + endpoint JSON khusus lebih transparan dan mudah diuji dengan Pest HTTP test.

## Consequences
- Seluruh routing web, otentikasi sesi, CSRF protection, dan otorisasi Blade terpusat dalam satu aplikasi Laravel (`routes/web.php`, `routes/auth.php`).
- Aset showroom (`resources/js/showroom.js`) dan dashboard (`resources/js/app.js`) dikompilasi melalui Vite (`vite.config.js`).
- Halaman yang mengandalkan submit AJAX di showroom harus tetap memperhatikan progressive enhancement jika JavaScript tidak aktif.
