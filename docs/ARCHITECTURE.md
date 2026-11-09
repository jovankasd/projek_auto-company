# Arsitektur Sistem XYZ Motors

Dokumen ini menjelaskan arsitektur perangkat lunak, pola desain, alur data, mesin status (state machine), dan kebijakan otorisasi yang diimplementasikan pada platform portal otomotif **XYZ Motors**.

---

## 1. Ikhtisar Arsitektur

XYZ Motors dibangun menggunakan framework **Laravel 11**, **Blade**, **Tailwind CSS**, dan **Alpine.js** (dengan bundling **Vite** serta **Swiper**/**GSAP** untuk showroom). Catatan keputusan arsitektur mendetail tersedia di [Architecture Decision Records (ADRs)](decisions/README.md).

```
┌─────────────────────────────────────────────────────────────┐
│                       Client Layer                          │
│   Alpine.js (Real-time Polling) • Blade • Tailwind CSS      │
└──────────────────────────────┬──────────────────────────────┘
                               │ HTTP / JSON
┌──────────────────────────────▼──────────────────────────────┐
│                    Routing & Middleware                     │
│    Web / Auth / RoleMiddleware (RBAC) / SecurityHeaders     │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                     Controller Layer                        │
│   ShowroomController • BookingController • ServiceController│
│   TestDriveController • DashboardController • Notification  │
└──────┬───────────────────────┬───────────────────────┬──────┘
       │                       │                       │
┌──────▼─────────────┐ ┌───────▼──────────────┐ ┌──────▼──────┐
│   Service Layer    │ │   Eloquent Models    │ │   Policies  │
│ • TicketGenerator  │ │ • User     • Car     │ │ • Service   │
│ • SlotValidator    │ │ • Service  • Item    │ │ • TestDrive │
│ • CostCalculator   │ │ • StatusLog• Promo   │ │ • Car       │
│ • SlugGenerator    │ │ • AuditLog • Setting │ └─────────────┘
└──────┬─────────────┘ └───────┬──────────────┘
       │                       │
┌──────▼───────────────────────▼──────────────────────────────┐
│                 Event & Observer Layer                      │
│ • ServiceObserver    ──► ServiceStatusChanged               │
│ • TestDriveObserver  ──► TestDriveStatusChanged             │
│ • AuditObserver      ──► AuditLog                           │
└──────────────────────────────┬──────────────────────────────┘
                               │ Dispatch Job
┌──────────────────────────────▼──────────────────────────────┐
│                 Async Queued Notification                   │
│ • SendServiceStatusNotification (Tries: 3, Timeout: 30s)    │
│ • SendTestDriveStatusNotification (Tries: 3, Timeout: 30s)  │
└──────────────────────────────┬──────────────────────────────┘
                               │
            ┌──────────────────┼──────────────────┐
            ▼                  ▼                  ▼
     ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
     │  Database   │    │  Mail/SMTP  │    │  WhatsApp   │
     │  (In-App)   │    │ (Templates) │    │  (Gateway)  │
     └─────────────┘    └─────────────┘    └─────────────┘
```

---

## 2. Matriks Otorisasi Berbasis Peran (RBAC)

Platform menerapkan 5 peran pengguna berbasis enum `UserRole`:

| Fitur / Modul | Customer | Sales | Mekanik | Super Admin | Owner |
|---|:---:|:---:|:---:|:---:|:---:|
| **Katalog Showroom & Filter** | Baca | Baca & Kelola Mobil | Baca | Akses Penuh | Baca (Ringkasan) |
| **Pengajuan Test Drive** | Buat & Lihat Milik Sendiri | Review, Setujui, Tolak | - | Akses Penuh | Metrik Ringkasan |
| **Booking Servis Kendaraan** | Buat & Pantau Milik Sendiri | - | Lihat Antrean, Update WO | Akses Penuh & Assign Mekanik | Metrik Ringkasan |
| **Work Order & Suku Cadang** | - | Kelola Master Sparepart | Kelola Master & Input Item WO | Akses Penuh | Nilai Inventaris |
| **Manajemen Promo** | Baca (Publik) | Baca & Kelola | - | Akses Penuh | - |
| **Pusat Notifikasi** | Milik Sendiri | Milik Sendiri | Milik Sendiri | Milik Sendiri | Milik Sendiri |
| **Laporan Eksekutif** | - | - | - | Lihat & Kirim ke Owner | Lihat & Terima Laporan |
| **Manajemen Pengguna, Audit & Pengaturan** | - | - | - | Akses Penuh | - |

---

## 3. Mesin Status (State Machine)

### A. Alur Status Servis Kendaraan (`ServiceStatus`)

```
      [ pending ] 
           │
           │ (Admin menugaskan mekanik)
           ▼
     [ confirmed ]
           │
           │ (Mekanik menekan "Mulai Pengerjaan")
           ▼
    [ in_progress ]  ◄── (Kelola Service Items: Sparepart & Jasa)
           │
           │ (Pengerjaan tuntas & minimal memiliki 1 item servis)
           ▼
     [ completed ]
```
- **Pembatalan (`cancelled`)**: Dapat dilakukan dari status `pending` atau `confirmed`. Jika servis telah mencapai `in_progress`, sistem mengunci pembatalan untuk mencegah inkonsistensi suku cadang dan jam kerja mekanik.
- **Validasi Kuota**: Maksimal 3 kendaraan per slot jam (`slot_hour`) pada tanggal yang sama.
- **Anti Double-Booking**: Plat nomor yang sama tidak dapat dibooking pada tanggal dan slot jam yang bersamaan.

### B. Alur Status Pengajuan Test Drive (`TestDriveStatus`)

```
      [ requested ]
           │
     ┌─────┴───────────────┐
     │                     │
     ▼ (Disetujui Sales)   ▼ (Ditolak Sales)
[ approved ]          [ rejected ]
     │
     ▼ (Pelaksanaan Selesai)
[ completed ]
```

---

## 4. Pipeline Notifikasi & WhatsApp Gateway

Sistem mengadopsi pola asinkronus (`ShouldQueue`) untuk mencegah latensi jaringan pada transaksi pengguna:

1. **Observers**:
   - `ServiceObserver`: Mendeteksi pembuatan dan transisi status servis, mencatat `ServiceStatusLog`, dan memicu event `ServiceStatusChanged`.
   - `TestDriveObserver`: Memantau status pengajuan test drive dan memicu event `TestDriveStatusChanged`.
2. **Queued Listeners**:
   - `SendServiceStatusNotification`: Menangani notifikasi servis (`tries = 3`, `timeout = 30`).
   - `SendTestDriveStatusNotification`: Menangani notifikasi test drive.
3. **Multi-Channel Delivery**:
   - `database`: Menghasilkan rekam in-app notification untuk Notification Center dan topbar badge.
   - `mail`: Mengirim email berbasis template resmi Laravel MailMessage.
   - `WhatsAppChannel`: Mengabstraksikan gateway pengiriman pesan instan WhatsApp melalui interface `WhatsAppGateway`.
4. **Driver WhatsApp**:
   - `log` (Default / Development): Menulis log payload pesan ke file storage.
   - `fonnte`: Integrasi API Fonnte Indonesia.
   - `twilio`: Integrasi API Twilio WhatsApp Messaging.

---

## 5. Pelacakan Real-time & Upgrade Path

1. **Client Polling (Default)**:
   - Pelacakan servis `/dashboard/my-services/{id}` menggunakan **Alpine.js** polling ke endpoint JSON `/dashboard/services/{id}/status-stream` setiap 30 detik.
   - Topbar Notification Bell melakukan polling hitung pesan belum dibaca setiap 60 detik.
2. **Upgrade Path Laravel Echo / Reverb**:
   - Event `ServiceStatusChanged` dan `TestDriveStatusChanged` telah disiapkan untuk mengimplementasikan `ShouldBroadcast` dengan channel privat `private-service.{id}` dan `private-user.{id}` tanpa perlu mengubah struktur controller atau database.
