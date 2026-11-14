# Architecture Decision Records (ADRs)

Direktori ini menyimpan catatan keputusan arsitektur (*Architecture Decision Records*) untuk proyek **XYZ Motors**. Setiap ADR mendokumentasikan konteks, keputusan yang diambil, alternatif yang dipertimbangkan, serta konsekuensinya bagi pengembang dan agen AI di masa mendatang.

## Daftar Keputusan

| ID | Judul | Status | Tanggal |
|---|---|---|---|
| [ADR-001](0001-laravel-monolith-with-blade-and-alpine.md) | Laravel 11 Monolith dengan Blade, Tailwind CSS, dan Alpine.js | Accepted | 2026-10-01 |
| [ADR-002](0002-enum-rbac-with-policies-and-middleware.md) | Kontrol Akses Berbasis Peran (RBAC) dengan Enum, Middleware, dan Policies | Accepted | 2026-10-01 |
| [ADR-003](0003-service-state-machine-and-booking-guards.md) | Mesin Status Servis, Validasi Kuota Slot, dan Generator Tiket Atomik | Accepted | 2026-10-01 |
| [ADR-004](0004-observer-and-queued-multi-channel-notifications.md) | Observer & Event-Driven Queued Multi-Channel Notifications | Accepted | 2026-10-01 |
| [ADR-005](0005-polling-live-tracking-over-websockets.md) | Live Tracking Berbasis HTTP Polling dengan Jalur Upgrade WebSocket | Accepted | 2026-10-01 |
| [ADR-006](0006-seo-trust-pages-and-honest-documentation.md) | Optimasi SEO, Peta Situs XML, Isolasi Perayapan Area Privat, dan Halaman Kepercayaan | Accepted | 2026-11-14 |

## Aturan Pemeliharaan ADR

- Jangan menghapus ADR lama; dokumen lama menyimpan konteks historis proyek.
- Jika suatu keputusan arsitektur diganti, buat ADR baru dengan status `Accepted` dan ubah status ADR lama menjadi `Superseded by ADR-XXX`.
- Gunakan penomoran berurutan (`0006-...md`) untuk setiap keputusan baru.
