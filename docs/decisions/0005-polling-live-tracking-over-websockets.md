# ADR-005: Live Tracking Berbasis HTTP Polling dengan Jalur Upgrade WebSocket

## Status
Accepted

## Date
2026-10-01

## Context
Customer perlu memantau progres pengerjaan servis kendaraannya secara berkala di halaman `/dashboard/my-services/{service}`, dan seluruh pengguna terautentikasi perlu melihat pembaruan badge notifikasi belum dibaca di topbar dashboard. Di sisi lain, aplikasi harus mudah dijalankan oleh pengguna di lingkungan Windows lokal (**XAMPP**, **Laragon**, `php artisan serve`) maupun VPS standar tanpa memaksa konfigurasi daemon WebSocket tambahan (Redis pub/sub + Laravel Reverb / Soketi / Pusher).

## Decision
Menggunakan **HTTP JSON Polling berkala dengan Alpine.js**:
1. Halaman pelacakan servis (`/dashboard/my-services/{service}`) melakukan polling setiap **30 detik** ke endpoint `GET /dashboard/services/{service}/status-stream` (dibatasi otorisasi `ServicePolicy::view`).
2. Topbar Notification Bell melakukan polling setiap **60 detik** ke endpoint `GET /dashboard/notifications/unread-count`.
3. Event domain `ServiceStatusChanged` dan `TestDriveStatusChanged` tetap dipisahkan sehingga dapat mengimplementasikan `ShouldBroadcast` di masa depan tanpa mengubah skema database atau struktur controller utama.

## Alternatives Considered

### Laravel Reverb / Pusher WebSockets Sejak Awal
- **Pros:** Pembaruan status instan (< 1 detik) tanpa request polling periodik.
- **Cons:** Memerlukan proses daemon WebSocket tambahan, konfigurasi proxy Nginx khusus `/app`, sertifikat TLS WSS, atau layanan berbayar pihak ketiga, yang menyulitkan setup Laragon/XAMPP lokal.
- **Rejected:** Kebutuhan bisnis bengkel otomotif (pembaruan tiap beberapa menit/tahap pengerjaan) cukup terlayani dengan interval polling 30 detik.

### Server-Sent Events (SSE)
- **Pros:** Push satu arah berbasis HTTP standar.
- **Cons:** Menahan worker PHP-FPM/built-in PHP server tetap terbuka per koneksi klien, berisiko menghabiskan pool worker pada server PHP standar atau XAMPP.
- **Rejected:** Polling JSON pendek lebih aman terhadap batasan konkurensi PHP-FPM/XAMPP.

## Consequences
- Aplikasi berjalan langsung di XAMPP, Laragon, `php artisan serve`, maupun Nginx+PHP-FPM tanpa daemon WebSocket terpisah.
- Pembaruan status pada layar customer memiliki jeda maksimal ~30 detik (atau langsung saat halaman di-refresh).
- Jika volume pengguna atau kebutuhan latensi sub-detik meningkat di masa depan, upgrade ke Laravel Reverb/Echo hanya memerlukan implementasi `ShouldBroadcast` pada event yang sudah ada serta substitusi listener Echo di sisi frontend.
