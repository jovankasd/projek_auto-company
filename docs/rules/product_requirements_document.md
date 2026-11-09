# Product Requirements Document (PRD)

**Project Title:** XYZ Motors & Service Management System
**Version:** 3.0.0 (Production Ready)
**Target Platform:** Web Responsive (1920×1080 / 1024×768 / 375px+)
**Timezone:** Asia/Jakarta (WIB, UTC+7) — simpan UTC, render WIB
**Status:** Approved for Implementation
```

---

## 1. Executive Summary & Objective

### 1.1. Latar Belakang
Dealer otomotif XYZ memiliki dua masalah: (a) konversi prospek rendah karena katalog statis, dan (b) operasional bengkel manual sehingga status pengerjaan tidak transparan.

### 1.2. Tujuan
Sistem terpadu dengan dua pilar:
1. **Interactive Digital Showroom (B2C)** — Katalog 2.5D slider + booking test drive & servis.
2. **Automotive Aftersales Management (B2B)** — Work Order, antrean mekanik, inspeksi, suku cadang, status real-time.

### 1.3. Success Metrics (KPI)

| Metrik | Baseline | Target 6 Bulan | Cara Ukur |
|:---|:---:|:---:|:---|
| Landing → Test Drive Request | 0.8% | ≥ 4.0% | Event `test_drive_submitted` |
| Bounce rate Showroom | 68% | ≤ 45% | GA4 |
| Avg. Time on Showroom | 22s | ≥ 75s | Analytics |
| Servis booking / bulan | 40 | ≥ 150 | Count `services` |
| SLA approval test drive | 2 hari | ≤ 6 jam | Median `approved_at - created_at` |
| Work order completion | — | ≥ 92% | `completed` / total |
| CLS Showroom | — | < 0.1 | Lighthouse CI |
| LCP Mobile | — | < 2.5s | Lighthouse CI |

### 1.4. Personas

| Persona | Kebutuhan | Pain Point |
|:---|:---|:---|
| **Prospek (Guest)** | Spek cepat, visual menarik, booking mudah | Katalog statis |
| **Customer** | Cek status servis, riwayat | Harus telepon untuk progres |
| **Sales** | Approval cepat, kontak WA 1 klik | Data prospek tersebar |
| **Mekanik** | Antrean jelas, update cepat dari HP | Work order kertas hilang |
| **Super Admin** | Laporan omzet, audit, kontrol user | Tidak ada dashboard terpadu |

### 1.5. Scope

**IN SCOPE (v1.0):** Showroom 2.5D, Auth multi-role RBAC, Booking Test Drive, Booking Servis, Dashboard 4 role, Work Order + catatan + suku cadang, Notifikasi in-app + email, Master data mobil & promo, Laporan dasar.

**OUT OF SCOPE (v1.0):** Payment gateway, WebSocket realtime (pakai polling), Mobile native, Multi-cabang, Integrasi DMS pihak ketiga, AI recommendation.

---

## 2. Technology Stack

| Layer | Teknologi | Versi |
|:---|:---|:---|
| Framework | Laravel | 11.x (PHP 8.2+) |
| Database | MySQL | 8.0+ (fallback PostgreSQL 15+) |
| Cache/Queue | Redis | 7.x |
| Auth | Laravel Breeze (Blade) | — |
| Authorization | Policies + Gates | — |
| Frontend | Blade + Tailwind CSS | 3.4+ |
| Interactivity | Alpine.js | 3.x |
| Motion | GSAP | 3.12+ |
| Slider | Swiper.js | 11+ |
| Build | Vite | 5.x |
| Image | Intervention Image | 3.x |
| Testing | Pest PHP | 2.x |
| Static Analysis | Larastan | Level 6 |

**Constraint Non-Negotiable:**
- PSR-12, tanpa TODO di kode produksi.
- Semua query list wajib eager-load.
- Semua form wajib FormRequest terpisah.
- Mutasi data wajib via Service/Action Class.
- Zero inline style.
- Gambar mobil PNG/WebP transparan, max 800KB.

---

## 3. RBAC

### 3.1. Role Matrix

| Role | Auth | Capabilities |
|:---|:---:|:---|
| Guest | No | Landing, katalog, form kontak, inisiasi auth |
| Customer | Yes | Booking TD & servis, lihat status, riwayat pribadi |
| Sales | Yes | CRUD mobil, CRUD promo, approve test drive, WA lead |
| Mekanik | Yes | Work Order, update status, input item & catatan |
| Super Admin | Yes | Full access, user, audit, laporan |

### 3.2. Permission Code

```
car.view / create / update / delete
promo.view / create / update / delete
testdrive.create / view.own / view.all / approve / reject / complete
service.create / view.own / view.all / assign / update.status / add.item / cancel
user.view / create / update / delete
report.view / audit.view / settings.manage
```

### 3.3. Role → Permission

| Permission | Customer | Sales | Mekanik | Super Admin |
|:---|:---:|:---:|:---:|:---:|
| `car.view` | ✅ | ✅ | ✅ | ✅ |
| `car.create/update/delete` | ❌ | ✅ | ❌ | ✅ |
| `promo.*` | ❌ | ✅ | ❌ | ✅ |
| `testdrive.create` | ✅ | ❌ | ❌ | ✅ |
| `testdrive.view.own` | ✅ | ❌ | ❌ | ✅ |
| `testdrive.view.all` | ❌ | ✅ | ❌ | ✅ |
| `testdrive.approve/reject` | ❌ | ✅ | ❌ | ✅ |
| `service.create` | ✅ | ❌ | ❌ | ✅ |
| `service.view.own` | ✅ | ❌ | ❌ | ✅ |
| `service.view.all` | ❌ | ❌ | ✅ | ✅ |
| `service.update.status` | ❌ | ❌ | ✅ | ✅ |
| `service.add.item` | ❌ | ❌ | ✅ | ✅ |
| `user.*` | ❌ | ❌ | ❌ | ✅ |
| `report.view` | ❌ | ✅ | ❌ | ✅ |

---

## 4. Database Schema

### 4.1. Konvensi
PK `BIGINT UNSIGNED AI` · FK `{singular}_id` dengan `onDelete` eksplisit · Uang `DECIMAL(15,2)` · JSON cast ke `array` · SoftDeletes pada master & transaksional · Charset `utf8mb4_unicode_ci`.

### 4.2. `users`
`id, name, email(unique), phone_number, email_verified_at, password, role ENUM(customer/sales/mekanik/super_admin), avatar_path, is_active, last_login_at, remember_token, timestamps, deleted_at`
**Index:** `idx_users_role_active(role, is_active)`

### 4.3. `cars`
`id, slug(unique), brand, model_name, year, category ENUM(SUV/Sedan/Sports/EV/MPV), color_name, color_hex, price DECIMAL(15,2), engine_specs JSON, dimensions JSON, features JSON, description TEXT, hero_image_transparent, hero_image_alt, gallery_images JSON, stock_quantity, is_available, is_featured, sort_order, view_count, meta_title, meta_description, timestamps, deleted_at`
**Index:** `idx_cars_available_sort(is_available, sort_order)`, `idx_cars_brand_category(brand, category)`

### 4.4. `promos`
`id, code(unique), title, description, discount_type ENUM(percentage/fixed/bonus), discount_value, car_id FK→cars NULLABLE, banner_image, starts_at, ends_at, max_usage, used_count, is_active, timestamps, deleted_at`
**Index:** `idx_promos_active_period(is_active, starts_at, ends_at)`

### 4.5. `services` (Work Order)
`id, ticket_number(unique) SRV-YYYYMMDD-XXXX, customer_id FK→users CASCADE, vehicle_plate_number, vehicle_model, vehicle_year, odometer_km, service_type ENUM(regular/major/inspection/warranty), service_date DATETIME, slot_hour TINYINT, complaint_notes TEXT, assigned_mechanic_id FK→users NULLABLE, mechanic_notes TEXT, recommendation TEXT, estimated_cost, total_cost, status ENUM(pending/confirmed/in_progress/completed/cancelled), confirmed_at, started_at, completed_at, cancelled_at, cancellation_reason, timestamps, deleted_at`
**Index:**
- `idx_services_status_date(status, service_date)`
- `idx_services_mechanic_status(assigned_mechanic_id, status)`
- `uq_services_slot(service_date, slot_hour, vehicle_plate_number)` — anti double booking

### 4.6. `service_items`
`id, service_id FK→services CASCADE, item_type ENUM(part/labor/consumable), name, part_number, quantity DECIMAL(8,2), unit_price, subtotal, added_by FK→users, timestamps`

### 4.7. `service_status_logs`
`id, service_id FK→services CASCADE, from_status, to_status, changed_by FK→users NULLABLE, notes, created_at`
**Index:** `idx_ssl_service_created(service_id, created_at DESC)`

### 4.8. `test_drives`
`id, customer_id FK→users CASCADE, car_id FK→cars RESTRICT, sales_id FK→users NULLABLE, preferred_date DATETIME, driver_license_id, driver_license_image, pickup_location, status ENUM(requested/approved/completed/rejected/cancelled), sales_notes, rejection_reason, approved_at, completed_at, timestamps, deleted_at`
**Index:** `idx_td_status_date(status, preferred_date)`, `idx_td_sales_status(sales_id, status)`

### 4.9. `notifications` (Laravel default)
`id CHAR(36) PK, type, notifiable_type, notifiable_id, data JSON, read_at, timestamps`

### 4.10. `audit_logs`
`id, user_id FK→users NULLABLE, event, auditable_type, auditable_id, old_values JSON, new_values JSON, ip_address, user_agent, created_at`
**Index:** `idx_audit_auditable(auditable_type, auditable_id)`

### 4.11. `settings`
`id, group, key(unique), value TEXT, type, timestamps`

### 4.12. Relasi
```
User      hasMany Service (customer_id, assigned_mechanic_id)
          hasMany TestDrive (customer_id, sales_id)
Car       hasMany TestDrive, hasMany Promo
Service   belongsTo User, hasMany ServiceItem, hasMany ServiceStatusLog
TestDrive belongsTo User (customer, sales), belongsTo Car
```

---

## 5. Business Logic & State Machines

### 5.1. Servis

| From | To | Trigger | Actor | Side Effect |
|:---|:---|:---|:---|:---|
| — | `pending` | Create booking | Customer | Generate ticket, notif, log |
| `pending` | `confirmed` | Confirm + assign mekanik | Super Admin | Set `confirmed_at`, notif |
| `pending`/`confirmed` | `cancelled` | Cancel | Customer/Admin | Set `cancelled_at` + reason |
| `confirmed` | `in_progress` | Cek in fisik | Mekanik | Set `started_at`, notif |
| `in_progress` | `completed` | Selesai + item | Mekanik | Set `completed_at`, hitung cost |
| `completed` / `cancelled` | — | Terminal | — | — |

**Guard:** Tidak boleh `completed` jika `service_items` kosong. Tidak boleh `cancelled` setelah `in_progress`. Setiap transisi wajib tulis `service_status_logs`.

### 5.2. Test Drive

| From | To | Trigger | Actor |
|:---|:---|:---|:---|
| — | `requested` | Submit | Customer |
| `requested` | `approved` | Approve | Sales |
| `requested` | `rejected` | Reject + reason | Sales |
| `approved` | `completed` | Selesai | Sales |
| `approved` | `cancelled` | Batal | Customer/Sales |

### 5.3. Business Rules

| Kode | Aturan |
|:---|:---|
| BR-01 | `service_date` ≥ `now()` |
| BR-02 | Maks. **3 kendaraan per slot jam** (transaction lock) |
| BR-03 | Ticket `SRV-YYYYMMDD-XXXX` (sequence harian, retry) |
| BR-04 | Anti double booking plat sama di jam sama |
| BR-05 | Test drive hanya `cars.is_available = true` |
| BR-06 | Guest submit → session → redirect login → restore |
| BR-07 | Sales hanya lihat lead miliknya (kecuali Super Admin) |
| BR-08 | Customer hanya lihat data miliknya (Policy) |
| BR-09 | `total_cost` = SUM(`service_items.subtotal`) |
| BR-10 | Promo valid jika aktif + dalam rentang + kuota tersedia |

---

## 6. Functional Requirements

### Modul 1 — Showroom

| ID | Requirement | AC | P |
|:---|:---|:---|:---:|
| FR-01.1 | Tampilkan `cars.is_available` urut `sort_order` | Eager load, render < 300ms | P0 |
| FR-01.2 | Slider responsif swipe/drag & tombol | CLS < 0.1 | P0 |
| FR-01.3 | Transisi tipografi + spek + mobil | GSAP timeline, no flicker | P0 |
| FR-01.4 | Indikator `01 / 05` | Update sinkron | P1 |
| FR-01.5 | CTA Test Drive & Servis | Modal prefill `car_id` | P0 |
| FR-01.6 | Lazy load kecuali slide 1 | LCP < 2.5s | P1 |
| FR-01.7 | Progressive enhancement | Slide 1 render tanpa JS | P2 |

### Modul 2 — Booking

| ID | Requirement | AC | P |
|:---|:---|:---|:---:|
| FR-02.1 | Validasi tanggal ≥ now | FormRequest `after:now` | P0 |
| FR-02.2 | Guest retensi state | Session → restore setelah login | P0 |
| FR-02.3 | Kuota maks 3/jam | Request ke-4 ditolak | P0 |
| FR-02.4 | Ticket otomatis | Format benar, unik | P0 |
| FR-02.5 | Toast feedback | Muncul 4s/6s | P0 |
| FR-02.6 | Support AJAX & non-AJAX | JSON / redirect + flash | P1 |
| FR-02.7 | SIM wajib test drive | Min 5 karakter | P0 |

### Modul 3 — Dashboard

| ID | Requirement | AC | P |
|:---|:---|:---|:---:|
| FR-03.1 | Mekanik tanpa menu penjualan | Menu hidden + route block | P0 |
| FR-03.2 | Aksi cepat mekanik | AJAX update, optimistic UI | P0 |
| FR-03.3 | Panel approval + WA | `wa.me/{phone}?text=` | P0 |
| FR-03.4 | Customer timeline dinamis | Dari `service_status_logs` | P0 |
| FR-03.5 | Super Admin KPI | Akurat | P0 |
| FR-03.6 | Semua dashboard responsif | Usable 375px | P0 |

---

## 7. Non-Functional Requirements

### 7.1. Performance Budget
LCP mobile < 2.5s · CLS < 0.1 · INP < 200ms · TTFB < 500ms · JS awal < 180KB gzip · Query/page < 15 · API p95 < 300ms.

### 7.2. Security
Bcrypt cost 12 / Argon2id · Rate limit login 5/min, form 10/min · CSRF wajib · `$fillable` eksplisit · Blade escaping default · Upload validasi MIME + max 5MB · HTTPS + Secure/HttpOnly/SameSite cookie · Policy di setiap controller action · Audit log mutasi sensitif.

### 7.3. Accessibility (WCAG 2.1 AA)
Kontras ≥ 4.5:1 · Keyboard accessible · `alt` deskriptif · Modal focus trap + `Esc` · `<label>` eksplisit · Toast `role="status"` · Hormati `prefers-reduced-motion`.

### 7.4. SEO
`meta_title`, `meta_description`, OG tags per car · Slug unik · Sitemap XML · `robots.txt` + `noindex` dashboard · Schema.org `Product` + `AutoDealer`.

### 7.5. Reliability
Queue untuk email/notif · Log JSON + request ID · Error tracking Sentry/Flare · Backup harian retensi 30 hari · Health check `/up`.

---

## 8. Notification Matrix

| Event | Penerima | In-App | Email | WA |
|:---|:---|:---:|:---:|:---:|
| Servis baru | Super Admin, Sales | ✅ | ✅ | ❌ |
| Servis dikonfirmasi | Customer, Mekanik | ✅ | ✅ | ✅ |
| Servis mulai | Customer | ✅ | ❌ | ✅ |
| Servis selesai | Customer | ✅ | ✅ | ✅ |
| Servis batal | Customer, Admin | ✅ | ✅ | ❌ |
| TD diminta | Sales | ✅ | ✅ | ❌ |
| TD disetujui | Customer | ✅ | ✅ | ✅ |
| TD ditolak | Customer | ✅ | ✅ | ❌ |
| TD selesai | Customer, Sales | ✅ | ❌ | ❌ |

---

## 9. Risk Register

| Risiko | Prob | Dampak | Mitigasi |
|:---|:---:|:---:|:---|
| Race condition slot | M | H | Transaction + unique index + retry |
| PNG terlalu besar | H | M | Pipeline kompresi + WebP + CDN |
| Animasi berat di low-end | M | M | Reduced-motion fallback |
| Email spam | M | M | SPF/DKIM/DMARC + queue |
| Scope creep payment | H | H | Tegaskan out-of-scope |
| Data guest hilang | M | H | Session retention |

---

## 10. Glossary

**Work Order** · **2.5D** · **HUD** · **RBAC** · **Slot Hour** · **Lead** · **CLS/LCP/INP**
