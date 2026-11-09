# ADR-004: Observer & Event-Driven Queued Multi-Channel Notifications

## Status
Accepted

## Date
2026-10-01

## Context
Setiap perubahan status servis dan pengajuan test drive perlu:
1. Mencatat riwayat kronologis (`ServiceStatusLog`) dan jejak audit sistem (`AuditLog`).
2. Mengirimkan pemberitahuan kepada pihak terkait (Customer, Mekanik, Sales, Super Admin) melalui beberapa kanal sekaligus: in-app database notification, Email, dan WhatsApp Gateway.
3. Tidak memperlambat waktu respons HTTP ketika driver email/SMTP atau API WhatsApp eksternal mengalami latensi atau gangguan sementara.

## Decision
Menggunakan arsitektur berbasis **Eloquent Observer → Domain Event → Queued Listener → Driver Contract**:
1. **Observers**: `ServiceObserver`, `TestDriveObserver`, dan `AuditObserver` (didaftarkan pada `AppServiceProvider::boot()`) otomatis mencatat `ServiceStatusLog`/`AuditLog` serta memicu event `ServiceStatusChanged` dan `TestDriveStatusChanged`.
2. **Queued Listeners**: `SendServiceStatusNotification` dan `SendTestDriveStatusNotification` mengimplementasikan `ShouldQueue` (`$tries = 3`, `$timeout = 30`) dengan pencatatan error terstruktur via `Log::error` agar kegagalan notifikasi tidak menggagalkan transaksi utama.
3. **Abstraksi WhatsApp Gateway**: Interface [`App\Services\Contracts\WhatsAppGateway`](../../app/Services/Contracts/WhatsAppGateway.php) di-bind sebagai singleton di `AppServiceProvider` dengan tiga implementasi driver yang dipilih lewat `WHATSAPP_DRIVER`: `log` (`LogWhatsAppGateway`, default lokal), `fonnte` (`FonnteGateway`), dan `twilio` (`TwilioGateway`), disalurkan melalui custom channel `WhatsAppChannel`.

## Alternatives Considered

### Pemanggilan Pengiriman Email & WhatsApp Sinkron di Controller
- **Pros:** Tidak memerlukan queue worker di background.
- **Cons:** Setiap klik perubahan status harus menunggu round-trip API WhatsApp/SMTP, menggandakan kode notifikasi di berbagai controller, dan gagal jika layanan eksternal timeout.
- **Rejected:** Mengganggu UX dan melanggar pemisahan tanggung jawab.

### Hardcode Satu Vendor WhatsApp (mis. hanya Fonnte)
- **Pros:** Implementasi awal lebih cepat.
- **Cons:** Mengikat aplikasi ke satu provider dan menyulitkan pengembangan lokal tanpa kuota/token API.
- **Rejected:** Kontrak `WhatsAppGateway` dengan driver `log` sebagai default membuat pengujian lokal dan CI berjalan tanpa kredensial eksternal.

## Consequences
- Perubahan status model melalui Eloquent secara otomatis memicu log status, audit, dan antrean notifikasi tanpa kode berulang di controller (catatan: update massal via `Query\Builder` seperti `Model::where(...)->update(...)` tidak memicu Eloquent observer).
- Lingkungan lokal & produksi harus menjalankan queue worker (`php artisan queue:listen` atau `Supervisor` dengan `docs/supervisor/supervisord.conf`) agar notifikasi antrean diproses; dalam pengujian Pest (`phpunit.xml`), `QUEUE_CONNECTION=sync` mengeksekusi listener secara sinkron.
- `CarObserver` ikut memelihara konsistensi cache showroom (`Cache::forget('showroom_cars')`) saat data mobil disimpan, dihapus, atau dipulihkan.
