# Architecture Decision Records (ADRs)

Direktori ini menyimpan catatan keputusan arsitektural utama yang membentuk sistem **Dealer Pro (XYZ Motors)**. Setiap ADR mengikuti format ringkas: konteks, keputusan, alternatif yang dipertimbangkan, dan konsekuensi.

## Daftar ADR

| ID | Judul | Status | Tanggal |
|:---|:---|:---|:---|
| [ADR-001](0001-laravel-monolith-with-blade-and-alpine.md) | Monolith Laravel 11 dengan Blade, Tailwind CSS, dan Alpine.js | Accepted *(Diperbarui per ADR-008)* | 2026-10-01 |
| [ADR-002](0002-enum-rbac-with-policies-and-middleware.md) | Kontrol Akses Berbasis Peran (RBAC) dengan Enum, Middleware, dan Policies | Superseded in part by [ADR-008](0008-staff-only-portal-and-guest-ticket-tracking.md) | 2026-10-01 |
| [ADR-003](0003-service-state-machine-and-booking-guards.md) | Mesin Status Servis, Validasi Kuota Slot, dan Generator Tiket Atomik | Accepted | 2026-10-01 |
| [ADR-004](0004-observer-and-queued-multi-channel-notifications.md) | Observer + Queued Listeners untuk Notifikasi Multi-Kanal & Audit Trail | Accepted *(Diperbarui per ADR-008)* | 2026-10-01 |
| [ADR-005](0005-polling-live-tracking-over-websockets.md) | Polling JSON Ringan untuk Pelacakan & Notifikasi Dibandingkan WebSocket Daemon | Superseded in part by [ADR-008](0008-staff-only-portal-and-guest-ticket-tracking.md) | 2026-10-01 |
| [ADR-006](0006-seo-trust-pages-and-honest-documentation.md) | Sitemap Dinamis, Halaman Kepercayaan Publik, Proteksi `noindex`, dan Dokumentasi Jujur | Accepted | 2026-11-14 |
| [ADR-007](0007-guest-booking-private-license-storage-and-inventory-integrity.md) | Reservasi Guest Tanpa Login, Penyimpanan Privat Foto SIM, dan Integritas Stok Work Order | Accepted *(Diperbarui per ADR-008)* | 2026-11-15 |
| [ADR-008](0008-staff-only-portal-and-guest-ticket-tracking.md) | Portal Khusus 4 Peran Staf (`/logind`) & Pelacakan Tiket Publik Tamu (`SRV-` / `TD-`) | Accepted | 2026-11-15 |

## Panduan Penambahan ADR Baru

1. Salin pola berkas bernomor berikutnya (`0009-judul-keputusan.md`).
2. Gunakan struktur: **Status**, **Tanggal**, **Konteks**, **Keputusan**, **Alternatif yang Dipertimbangkan**, dan **Konsekuensi**.
3. Jika sebuah keputusan menggantikan ADR lama, ubah status ADR lama menjadi `Superseded by [ADR-XXX](...)` tanpa menghapus riwayat keputusannya.
4. Perbarui tabel daftar di halaman ini.
