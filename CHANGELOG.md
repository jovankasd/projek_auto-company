# Changelog

Seluruh perubahan penting pada proyek **Dealer Pro (XYZ Motors)** didokumentasikan pada berkas ini. Format mengikuti standar [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) dan [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.1.0] - 2026-11-15 — Rilis Final: Alur Tamu Tanpa Login, Portal 4 Peran Staf, SEO & Hardening Keamanan

### Diubah (Model Akses & Antarmuka Publik)
- **Penghapusan Login/Registrasi Pelanggan di Antarmuka Publik**:
  - Mengubah pengalaman showroom menjadi *guest-first*: tombol *Login/Register* pelanggan dihilangkan dari `public-navbar` & `public-footer`, digantikan navigasi langsung ke **Cek Tiket** (`/#lacak-tiket`).
  - Memfokuskan halaman masuk (`/logind` & alias `/login`) sebagai **"Masuk Portal Staf"** khusus personel internal (`Owner`, `Super Admin`, `Sales`, `Admin Mekanik`).
  - Menyaring `UserRole::Customer` dari manajemen akun staf di `UserController::staffRoles()` dan `/dashboard/users` sehingga antarmuka dasbor hanya mengelola 4 peran staf aktif.
  - Mempertahankan `User::resolveGuestCustomer()` (`is_active = false`) di lapisan database demi integritas relasi *foreign key* tanpa membuka akses login bagi tamu.
- **Pelacakan Tiket Publik Ganda (`SRV-` & `TD-`)**:
  - Menambahkan kolom `ticket_number` pada tabel `test_drives` (`2026_11_15_000001_add_ticket_number_to_test_drives_table`) serta generator otomatis `TicketNumberGenerator::generateTestDrive()` dengan format `TD-YYYYMMDD-XXXX`.
  - Menambahkan widget **Lacak Tiket Publik (`#lacak-tiket`)** dan endpoint `GET /track-ticket` (`HomeController::trackTicket`, `throttle:30,1`) agar pengunjung dapat memantau progres pengajuan `SRV-` maupun `TD-` beserta histori log status tanpa login.

### Ditambahkan (Halaman Publik, SEO & Dokumentasi Arsitektural)
- **Halaman Detail Unit (`/cars/{slug}`) & Hubungi Kami (`/contact`, `/kontak`)**: Halaman detail mobil dilengkapi Schema.org JSON-LD (`Product` + `Car`), Open Graph, promo aktif, dan formulir pemesanan langsung; halaman kontak dilengkapi *honeypot* `website_url` dan *rate limiting*.
- **Halaman Kepercayaan Legal**: Menambahkan halaman **Kebijakan Privasi** (`/privacy-policy`, `/kebijakan-privasi`) dan **Syarat & Ketentuan** (`/terms-of-service`, `/syarat-dan-ketentuan`).
- **SEO & Pengindeksan**: Menambahkan `SitemapController` (`GET /sitemap.xml`), memperketat `public/robots.txt`, serta menyematkan `<meta name="robots" content="noindex, nofollow">` di seluruh layout autentikasi dan dasbor staf.
- **Architecture Decision Records (ADRs)**: Menambahkan `ADR-006`, `ADR-007`, dan `ADR-008` pada `docs/decisions/` serta menyelaraskan seluruh dokumen Markdown proyek dengan perilaku website final.

### Keamanan & Integritas Data (Security Hardening)
- **Isolasi Foto SIM Privat & Otorisasi Lead**:
  - Mengalihkan penyimpanan unggahan foto SIM pengemudi dari disk `public` ke disk privat `local` (`licenses/`) di `TestDriveController`.
  - Menyediakan endpoint terotorisasi `GET /dashboard/test-drives/{testDrive}/license` (`TestDriveController::viewLicense`) dengan pemeriksaan `TestDrivePolicy::viewLicense`.
  - Memperketat `TestDrivePolicy` agar `Sales` hanya dapat memproses lead miliknya sendiri atau lead yang belum memiliki PIC (`sales_id`), serta menerapkan transisi state machine formal di enum `TestDriveStatus`.
- **Integritas Stok & Harga Work Order**:
  - Menambahkan relasi inventaris dan penguncian atomik (`DB::transaction` + `lockForUpdate()`) pada `ServiceController::addItem()` dan `removeItem()`, sekaligus mengunci harga satuan suku cadang berdasarkan master database `SparePart`.
- **Akun Demo, Keaktifan Akun & Header HTTP**:
  - Menghapus kartu kredensial demo dan autofill dari halaman login publik, memisahkan data demo ke `DemoSeeder` berpenjaga `local`/`testing`, serta membuat seeder idempoten (`firstOrCreate`). ([PR #1](https://github.com/jovankasd/projek_auto-company/pull/1))
  - Menambahkan `EnsureUserIsActive`, `User::revokeAccess()`, proteksi anti-self-disable/demote Super Admin, dan verifikasi `is_active` pada `LoginRequest::authenticate()`. ([PR #2](https://github.com/jovankasd/projek_auto-company/pull/2))
  - Menambahkan middleware `SecurityHeaders` (CSP, `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, HSTS produksi), `trustProxies('*')`, dan rate limiter rute sensitif.
- **Showroom & Aksesibilitas**: Memperbaiki dukungan `prefers-reduced-motion` dan tombol jeda/putar carousel (`resources/js/showroom.js`), semantik tablist WAI-ARIA, kontras teks WCAG AA, dan dukungan form fallback non-JS.
- **Pengujian Otomatis**: **131 passed (1097 assertions)** pada suite Unit & Feature Pest (termasuk `DemoCredentialsSecurityTest`, `ActiveAccountEnforcementTest`, `SeoAndTrustPagesTest`, `RoleNavigationAuditTest`, dan `WorkOrderTest`), Pint bersih, serta PHPStan Level 6 bersih.

---

## [1.0.0] - 2026-11-08 — Rilis Awal MVP (Arsitektur Historis)

> **Catatan:** Pada rilis v1.0.0 awal, sistem masih menyediakan antarmuka dasbor untuk pelanggan yang login (`/dashboard/my-services`). Pada rilis final **v1.1.0 (2026-11-15)**, alur tersebut digantikan sepenuhnya oleh reservasi tamu tanpa login + pelacakan tiket publik (`#lacak-tiket` / `GET /track-ticket`) sementara portal dasbor dikhususkan bagi 4 peran staf internal.

### Ringkasan Fitur Awal v1.0.0
- **Fondasi Laravel 11 & Skema Database**: 11+ migrasi awal (`users`, `cars`, `promos`, `services`, `service_items`, `service_status_logs`, `test_drives`, `audit_logs`, `settings`, `notifications`, `cache`, serta penambahan `spare_parts`) dan enum backed PHP 8.2+.
- **Showroom 2.5D & Booking Engine**: Hero stage 2.5D Swiper + GSAP, filter kategori, `TicketNumberGenerator` (`SRV-YYYYMMDD-XXXX`), `BookingSlotValidator` (`3` kendaraan/jam), dan mencegah double-booking plat nomor.
- **Dashboard Operasional & Work Order**: Panel terpisah per peran, `ServiceCostCalculator`, CRUD `Car`/`Promo`/`SparePart`/`User` dengan `ImageOptimizer` (GD WebP) & `CarSlugGenerator`, serta `AuditObserver`.
- **Notifikasi Terantre & Pengingat Harian**: `ServiceObserver`, `TestDriveObserver`, `ServiceStatusChanged`/`TestDriveStatusChanged` listeners (`ShouldQueue`), `WhatsAppChannel` (`LogWhatsAppGateway`/`FonnteGateway`/`TwilioGateway`), serta command `services:remind-upcoming`, `test-drives:remind-upcoming`, dan `promos:deactivate-expired`.
- **Pengujian Awal**: 76 pengujian Pest awal (355 assertions) dengan kelulusan 100%.
