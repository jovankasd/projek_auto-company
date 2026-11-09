# ADR-002: Kontrol Akses Berbasis Peran (RBAC) dengan Enum, Middleware, dan Policies

## Status
Accepted

## Date
2026-10-01

## Context
Sistem **XYZ Motors** melayani lima aktor dengan tanggung jawab yang berbeda:
- **Customer**: Mengajukan test drive, booking servis, dan melacak progres kendaraan miliknya.
- **Sales**: Mengelola katalog mobil, promo, spare part, dan memproses persetujuan/penolakan test drive.
- **Mekanik**: Mengelola antrean work order, status pengerjaan servis, dan rincian item pekerjaan/suku cadang.
- **Super Admin**: Mengakses seluruh modul operasional, menugaskan mekanik, mengelola pengguna/pengaturan, melihat audit log, dan mengirim laporan ke Owner.
- **Owner**: Memantau ringkasan eksekutif finansial dan laporan tanpa mutasi operasional harian.

Aplikasi membutuhkan mekanisme otorisasi yang type-safe, mudah diaudit, dan tidak mudah menyimpang akibat string role yang salah ketik.

## Decision
Menggunakan PHP Backed Enum [`App\Enums\UserRole`](../../app/Enums/UserRole.php) (`customer`, `sales`, `mekanik`, `super_admin`, `owner`) yang dipetakan melalui Eloquent cast pada model `User`, dikombinasikan dengan:
1. Route middleware `role` ([`App\Http\Middleware\CheckRole`](../../app/Http/Middleware/CheckRole.php)) pada pengelompokan route di `routes/web.php`.
2. Eloquent **Policies** (`CarPolicy`, `PromoPolicy`, `ServicePolicy`, `SparePartPolicy`, `TestDrivePolicy`, `UserPolicy`) untuk pemeriksaan otorisasi tingkat resource di controller.

## Alternatives Considered

### Paket Pihak Ketiga (Spatie Laravel Permission)
- **Pros:** Dukungan role & permission dinamis berbasis tabel database (`roles`, `permissions`, `model_has_roles`).
- **Cons:** Menambah tabel, migrasi, cache permission, dan kompleksitas manajemen UI padahal set peran dealer bersifat tetap secara bisnis.
- **Rejected:** Lima peran statis yang terdefinisi jelas lebih aman dan sederhana dimodelkan dengan PHP 8.2+ backed enum + Laravel Policies bawaan.

### Pengecekan Role Manual di Controller
- **Pros:** Cepat ditulis pada tahap prototipe.
- **Cons:** Rawan duplikasi logika `if ($user->role === ...)` yang tersebar dan mudah terlupa di endpoint baru.
- **Rejected:** Sulit diaudit; Laravel Policies + middleware `CheckRole` menjaga pemisahan tanggung jawab lebih rapi.

## Consequences
- Perubahan atau penambahan peran baru memerlukan perubahan terpusat pada `UserRole` enum, middleware route, dan policy terkait, serta diverifikasi oleh `tests/Unit/EnumsTest.php` dan `tests/Feature/RbacTest.php`.
- Setiap endpoint mutasi harus tetap memanggil `$this->authorize(...)` atau memeriksa kepemilikan resource secara eksplisit agar pembatasan tidak hanya bergantung pada tampilan UI atau middleware grup route.
- Penonaktifan user (`is_active = false`) harus dipastikan dicek baik saat login maupun pada request sesi aktif agar pencabutan akses berlaku segera.
