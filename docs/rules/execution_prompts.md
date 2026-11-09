# Phased AI Execution Prompts

**Cara Memberikan Prompt ke AI:**
Saat Anda siap untuk mengerjakan sebuah fase, salin dan tempel (atau ketik) pesan berikut ke dalam chat AI:

> "Tolong eksekusi **[NAMA PROMPT]** dari file `rules/execution_prompts.md`.
> Pastikan kamu memuat dan mematuhi instruksi dari skill **@ponytail**, **@ui-ux-pro-max**, dan **@taste-skill**."

**Contoh Eksekusi Prompt 2:**
> "Tolong eksekusi **PROMPT 2 — Showroom 2.5D + Booking Engine** dari file `rules/execution_prompts.md`. Pastikan kamu menggunakan skill @ponytail, @ui-ux-pro-max, dan @taste-skill."

**Aturan Eksekusi:**
- Jalankan **satu prompt per satu**.
- Wajib memanggil skill `@ponytail` (untuk efisiensi kode), `@ui-ux-pro-max` (untuk aksesibilitas, Tailwind, komponen UI), dan `@taste-skill` (untuk animasi GSAP yang elegan & desain non-slop) agar kualitas web maksimal.
- Tunggu AI selesai bekerja dan pastikan semua checklist `Definition of Done` tercentang sebelum pindah ke prompt berikutnya.

**Referensi:** `product_requirements_document.md` · `desain_ui_ux_guidelines.md`

**Konvensi Global:**
- PHP: PSR-12, Pint clean, PHPStan level 6 clean.
- JS: ES2020+, tanpa `var`, tanpa global leak.
- Styling: Tailwind utility, tanpa inline style.
- UI: Bahasa Indonesia.
- Tanpa `TODO`/`FIXME`/placeholder.
```

---

## 🅰️ PROMPT 1 — Foundation: Database, Auth, RBAC, Seeder

**Context:** Anda Senior Backend Architect Laravel 11. Membangun fondasi data & autentikasi.
**Files:** PRD §3, §4, §5 · Design §1.

### Tugas

**1. Scaffolding**
- `composer create-project laravel/laravel xyz-motors "^11.0"`
- Set `.env.example`: `APP_TIMEZONE=Asia/Jakarta`, `APP_LOCALE=id`.
- Install: `laravel/breeze` (Blade), `intervention/image`, `barryvdh/laravel-dompdf`, `pestphp/pest`, `larastan/larastan`.
- NPM: `tailwindcss@^3.4`, `@tailwindcss/forms`, `alpinejs`, `gsap`, `swiper`.
- Buat `pint.json` (preset laravel), `phpstan.neon` (level 6).
- Konfigurasi `tailwind.config.js` dengan token warna/font/shadow/motion dari Design §1.
- Struktur: `app/{Actions,Enums,Http/{Controllers,Middleware,Requests},Models,Policies,Services,Notifications,Observers}`.

**2. Migrations (lengkap dengan `down()`, FK `onDelete` eksplisit, index sesuai PRD §4)**
- `users` (tambah `phone_number`, `role`, `avatar_path`, `is_active`, `last_login_at`)
- `cars`, `promos`, `services`, `service_items`, `service_status_logs`, `test_drives`
- `notifications` (default Laravel), `audit_logs`, `settings`
- SoftDeletes pada: `cars`, `services`, `test_drives`, `promos`

**3. Enums (PHP 8.1 backed)**
`UserRole`, `CarCategory`, `ServiceStatus`, `TestDriveStatus`, `DiscountType`, `ServiceItemType`.
Setiap enum: `label(): string` (ID) + `color(): string` (kelas Tailwind badge).

**4. Models & Eloquent**
Untuk setiap model: `$fillable` eksplisit (jangan `$guarded=[]`), `$casts` lengkap (JSON→array, enum, decimal:2, datetime), relasi sesuai PRD §4.12, SoftDeletes bila sesuai.
- Scopes: `Car::available()`, `Promo::active()`, `Service::forMechanic($id)`, `TestDrive::forSales($id)`.
- Accessors: `Car::price_formatted`, `Service::status_label`, `Service::status_color`.
- Booted: auto-slug `Car`, auto-ticket `Service`.

**5. Services (Business Logic)**
- `TicketNumberGenerator` — `SRV-YYYYMMDD-XXXX`, transaction + `lockForUpdate`.
- `BookingSlotValidator` — cek kuota maks 3 per `slot_hour`.
- `CarSlugGenerator` — slug unik.
- `ServiceCostCalculator` — hitung `total_cost`.

**6. Observers**
- `ServiceObserver` — tulis `service_status_logs` saat status berubah; recalc `total_cost`.
- `AuditObserver` — catat create/update/delete ke `audit_logs`.
- Daftarkan di `AppServiceProvider`.

**7. Seeder**
- `RoleSeeder` — 1 Super Admin, 1 Sales, 2 Mekanik, 1 Customer (password: `password`).
- `CarSeeder` — 5 unit premium (Mustang GT, BMW M4, Alphard, Tesla Model 3, Ranger Raptor) dengan `engine_specs` JSON lengkap.
- `PromoSeeder` — 2 promo aktif.
- `ServiceSeeder` — 8 servis status bervariasi + logs + items.
- `TestDriveSeeder` — 5 TD status bervariasi.
- `SettingSeeder` — jam operasional, kuota/jam, kontak WA.

**8. RBAC**
- `CheckRole` middleware: `->middleware('role:sales,super_admin')`.
- Policies: `CarPolicy`, `PromoPolicy`, `ServicePolicy`, `TestDrivePolicy`, `UserPolicy` — implementasi semua permission PRD §3.2.
- Daftarkan di `AuthServiceProvider`.

**9. Auth (Breeze)**
- Tambah `phone_number` di register.
- Redirect setelah login berdasarkan role → `/dashboard`.

### Batasan
Tanpa `$guarded=[]` · Tanpa raw SQL (kecuali agregasi dengan binding) · Business logic di Service/Action, bukan Controller · Enum cast, bukan string literal.

### Definition of Done
- [ ] `php artisan migrate:fresh --seed` sukses.
- [ ] Semua index sesuai PRD §4.
- [ ] Tidak ada N+1 (semua relasi pakai `with()`).
- [ ] Login 4 role → redirect benar.
- [ ] Middleware `role:` blokir akses tidak sah (403).
- [ ] Pint & PHPStan clean.

### Verification
```
□ php artisan migrate:fresh --seed
□ tinker: Car::available()->with('promos')->get()
□ tinker: User::where('role','mekanik')->first()->assignedServices
□ Login mekanik → /admin/cars → 403
```

---

## 🅱️ PROMPT 2 — Showroom 2.5D + Booking Engine

**Context:** Anda Creative Frontend Engineer + Full-Stack Developer.
**Files:** Design §1–§6 · PRD §6 Modul 1 & 2.

### Tugas

**1. Route & Controller**
- `GET /` → `HomeController@index` — `Car::available()->orderBy('sort_order')->with('promos')->get()`, cache 5 menit, invalidate via Observer.
- `POST /test-drive` → `TestDriveController@store` (name: `test-drive.store`).
- `POST /service-booking` → `ServiceBookingController@store` (name: `service-booking.store`).

**2. Blade Showroom (`resources/views/showroom/index.blade.php`)**

**Layer 0 (z-0):** Ambient glow `bg-gradient-radial from-blue-50` + Giant typography `font-black uppercase tracking-tighter text-slate-200 mix-blend-multiply select-none pointer-events-none` dengan `font-size: clamp(4rem,16vw,18rem)`, `aria-hidden="true"`, satu per slide.

**Layer 10 (z-10):** `<img>` mobil `absolute inset-0 m-auto max-h-[60vh] object-contain` + `filter: drop-shadow(0 25px 25px rgba(0,0,0,.25))`. Slide 1: `loading="eager" fetchpriority="high"`. Slide lain: `loading="lazy" decoding="async"`. Wajib `width`, `height`, `alt`.

**Layer 20 (z-20):**
- Spec grid desktop: `absolute bottom-24 left-1/2 -translate-x-1/2 grid grid-cols-4 gap-8`. Setiap spec: `<div data-spec-item>` label uppercase kecil + angka `font-mono` besar.
- Mobile: `flex gap-3 overflow-x-auto snap-x snap-mandatory px-6`.
- CTA: "Test Drive" (primary) & "Booking Servis" (secondary), `data-car-id` & `data-car-name`.

**Layer 20 — Nav Slider:** Panah `rounded-full p-3 hover:bg-slate-100` + indikator `<span>01</span> / <span>05</span>` (padStart 2).

**Layer 30 — Navbar:** `fixed top-0 w-full z-30 backdrop-blur-md bg-white/70 border-b border-slate-200/60`. Mobile: hamburger → drawer.

**3. Motion Script (`resources/js/showroom.js`)**
- Swiper config persis Design §3.2.
- `runHeroTimeline()` persis Design §3.3.
- Exit animation: `gsap.to(prevCar,{x:-120,opacity:0,scale:.95,duration:.5,ease:'power2.in'})`.
- Reduced motion fallback Design §3.4.
- Performance guard Design §3.5.
- IIFE/module, tidak ada global, cleanup `swiper.destroy()` + `tl.kill()`.
- Guard `if(!car) return;`.
- Keyboard: panah kiri/kanan. Counter & `aria-live` region update.

**4. Progressive Enhancement**
- Slide 1 render server-side, terlihat tanpa JS.
- Tombol prev/next di-`hidden` jika JS off (`<noscript>`).

**5. SEO**
- JSON-LD `AutoDealer` di `<head>`.
- OG tags · `meta_title` · `meta_description`.

**6. Modal Components (Blade + Alpine.js)**

**Modal A — Test Drive:** `car_id` (hidden), `preferred_date`, `driver_license_id`, `driver_license_image` (opsional), `pickup_location` (opsional), `notes` (opsional). Validasi client: tanggal ≥ hari ini, SIM min 5.

**Modal B — Servis:** `vehicle_plate_number`, `vehicle_model`, `vehicle_year`, `odometer_km`, `service_type`, `service_date`, `complaint_notes`. Validasi: plat uppercase, tanggal ≥ hari ini.

Keduanya: `x-cloak`, focus trap, `Esc` tutup, `aria-modal="true"`, `role="dialog"`, `body overflow-hidden`, fokus kembali ke trigger. Styling frosted glass Design §4.5.

**7. FormRequests**
- `StoreTestDriveRequest`: `car_id` required exists available · `preferred_date` required after:now before:+90 days · `driver_license_id` required min 5 · `driver_license_image` nullable image max 5MB.
- `StoreServiceBookingRequest`: `vehicle_plate_number` regex plat · `service_type` in:regular,major,inspection,warranty · `service_date` after:now · `complaint_notes` min 10.
- Custom messages Bahasa Indonesia.

**8. Controllers**

`TestDriveController@store`:
- Guest → session `pending_test_drive` → redirect login.
- Assign `sales_id` round-robin.
- Upload SIM ke `storage/app/public/licenses/`.
- Status `requested`. Notif ke Sales (in-app + email).
- Return JSON untuk AJAX; redirect+flash untuk non-AJAX.

`ServiceBookingController@store`:
- Guest → session `pending_service_booking` → redirect login.
- `TicketNumberGenerator` (transaction + lock).
- `BookingSlotValidator` (maks 3/jam).
- Anti double-booking (plat + jam sama).
- Status `pending`, tulis `service_status_logs`.
- Notif ke Super Admin.
- Return JSON / redirect.

**9. Session Retention**
```php
if (!auth()->check()) {
    session()->put('pending_test_drive', $request->validated());
    return redirect()->route('login')->with('info','Silakan masuk untuk melanjutkan.');
}
```
Setelah login: cek session, restore ke form.

**10. Feedback & Rate Limit**
- Toast sukses (hijau 4s), error (merah 6s), `role="status" aria-live="polite"`.
- `throttle:10,1` pada route POST.

### Batasan
Jangan card layout konvensional · Z-index sesuai Design §1.8 · Mobil tidak boleh terpotong · Bundle JS awal < 180KB gzip · Validasi di FormRequest · Business logic di Service.

### Definition of Done
- [x] Showroom render 5 mobil.
- [x] Swipe (touch/drag) + panah + keyboard berfungsi.
- [x] GSAP 60fps, no layout shift.
- [x] Counter `01/05` benar.
- [x] Mobile 375px: HUD scroll, mobil center.
- [x] Reduced-motion dihormati.
- [x] Modal buka/tutup, validasi client & server jalan.
- [x] Submit as Guest → redirect login → data ter-restore.
- [x] Servis ke-4 di jam sama → ditolak.
- [x] Ticket format `SRV-YYYYMMDD-XXXX` unik.
- [x] Lighthouse: Perf ≥ 85 (mobile), A11y ≥ 95, CLS < 0.1.

### Verification
```
□ Lighthouse mobile
□ DevTools Performance: no frame drop
□ Test 375px / 768px / 1024px / 1920px
□ Keyboard only + JS disabled + reduced-motion
□ Submit servis 4x di jam sama
□ php artisan test --filter=BookingTest
```

---

## 🅲 PROMPT 3 — Dashboard Role-Based + Work Order + Master Data

**Context:** Anda Lead Laravel Developer. Dashboard adaptif + CRUD master data.
**Files:** PRD §3, §5, §6 Modul 3 · Design §4.9, §4.10, §5.2, §9.

### Tugas

**1. Routing**

```php
Route::middleware(['auth','verified'])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class,'index'])->name('dashboard');
    Route::get('my-services',     [ServiceController::class,'myServices'])->name('services.my');
    Route::get('my-test-drives',  [TestDriveController::class,'myTestDrives'])->name('test-drives.my');

    Route::middleware('role:sales,super_admin')->group(function () {
        Route::resource('cars', CarController::class);
        Route::resource('promos', PromoController::class);
        Route::patch('test-drives/{testDrive}/approve', [TestDriveController::class,'approve'])->name('test-drives.approve');
        Route::patch('test-drives/{testDrive}/reject',  [TestDriveController::class,'reject'])->name('test-drives.reject');
    });

    Route::middleware('role:mekanik,super_admin')->group(function () {
        Route::patch('services/{service}/status',         [ServiceController::class,'updateStatus'])->name('services.update-status');
        Route::post('services/{service}/items',           [ServiceController::class,'addItem'])->name('services.add-item');
        Route::delete('services/{service}/items/{item}',  [ServiceController::class,'removeItem'])->name('services.remove-item');
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('reports',    [ReportController::class,'index'])->name('reports.index');
        Route::get('audit-logs', [AuditLogController::class,'index'])->name('audit.index');
        Route::get('settings',   [SettingController::class,'edit'])->name('settings.edit');
        Route::patch('settings', [SettingController::class,'update'])->name('settings.update');
        Route::patch('services/{service}/assign', [ServiceController::class,'assignMechanic'])->name('services.assign');
    });
});
```

**2. Dashboard Adaptif (`DashboardController@index` → partial per role)**

- **Customer:** kartu status servis aktif + timeline visual (Pending→Confirmed→InProgress→Completed dari `service_status_logs`) · jadwal test drive · riwayat · CTA Booking · empty state.
- **Mekanik:** panel antrean Work Order (tiket, plat, model, keluhan, status, aksi) · filter Hari Ini/Minggu/Semua · aksi cepat inline (Mulai, Selesaikan, Tambah Item) · form modal catatan · **tanpa** menu penjualan.
- **Sales:** panel approval TD (customer, mobil, tanggal, SIM, status, aksi) · Approve/Reject modal · tombol WA `https://wa.me/{phone}?text={encoded}` · daftar lead · statistik ringkas.
- **Super Admin:** 4 KPI card (Total Booking Servis Bulan Ini, Unit Aktif, Lead Baru, Pendapatan Servis) · grafik tren 30 hari · 10 servis terbaru · 10 TD terbaru · quick link laporan & audit.

**3. Action Controllers**

`ServiceController@updateStatus`:
- Validasi transisi (state machine PRD §5.1).
- Guard: tidak boleh `completed` tanpa `service_items`.
- Guard: tidak boleh `cancelled` setelah `in_progress`.
- Set timestamp sesuai.
- Tulis `service_status_logs`. Trigger event notif.

`ServiceController@addItem`:
- Simpan `service_item`, recalc `total_cost` via `ServiceCostCalculator`.
- Return JSON.

`TestDriveController@approve`/`@reject`:
- Policy check. Set `approved_at`/`rejection_reason`. Notif.

`ServiceController@assignMechanic`:
- Super Admin only. Set `assigned_mechanic_id`, status→`confirmed`. Notif.

**4. UI/UX Dashboard**
- Sidebar `w-64` fixed desktop, drawer mobile.
- Topbar: breadcrumb + notif bell + user dropdown.
- Badge status pakai token warna.
- Tabel styling Design §4.9; mobile → card stack.
- Semua aksi async Alpine.js + fetch, optimistic UI.
- Loading: skeleton. Empty: `x-empty-state`.

**5. Master Data CRUD**

**Car CRUD:** Index dengan filter (brand, category, status) + search + pagination 15. Create/Edit form lengkap PRD §4.3. Upload hero (PNG/WebP max 2MB, convert WebP) + gallery (max 10, 3 ukuran 400/800/1600). Engine specs form dinamis Alpine.js. Features tag input. Drag-and-drop `sort_order`. Preview real-time. Soft delete + restore.

**Promo CRUD:** code uppercase unique · discount_type/value · periode · max_usage · banner. Validasi `ends_at > starts_at`.

**Media Pipeline:** Service `ImageOptimizer` (WebP q=85, thumbnail, strip EXIF, max dim 2000). Command `php artisan media:optimize`.

**Settings:** Tab General/Booking/SEO/Contact. Cache, invalidate on save.

**Audit Log Viewer:** tabel + filter + detail modal diff JSON berwarna.

### Batasan
Dashboard clean SaaS · Mekanik **tidak boleh** lihat menu penjualan (hide + block route) · Customer hanya lihat data sendiri (Policy) · Semua mutasi via Policy check · Semua tabel responsif · Semua upload validasi MIME (bukan ekstensi).

### Definition of Done
- [ ] 4 role → dashboard berbeda.
- [ ] Mekanik → `/dashboard/cars` = 403.
- [ ] Customer → hanya data sendiri.
- [ ] Update status → tercatat di `service_status_logs`.
- [ ] `completed` tanpa item → error jelas.
- [ ] Approve TD → notif terkirim.
- [ ] Tombol WA → wa.me dengan pesan ter-encode.
- [ ] KPI Super Admin akurat.
- [ ] Dashboard usable di 375px.
- [ ] Notif bell unread count akurat.
- [ ] CRUD Car berfungsi dengan upload otomatis WebP.
- [ ] CRUD Promo validasi periode.
- [ ] Settings tersimpan & ter-cache.

### Verification
```
□ Screenshot 4 dashboard
□ Mekanik /dashboard/cars → 403
□ Customer /dashboard/my-services → data sendiri
□ Update status → cek service_status_logs
□ Approve TD → cek notifications + mail log
□ Bandingkan KPI vs query manual
□ Responsive 375 / 768 / 1440
□ php artisan test --filter=DashboardTest
```

---

## 🅳 PROMPT 4 — Notifications, Testing, Deployment

**Context:** Anda Backend + QA + DevOps Engineer. Menutup lifecycle.
**Files:** PRD §7, §8 · Design §7.

### Tugas

**1. Notification Classes (ShouldQueue)**
`ServiceCreated`, `ServiceConfirmed`, `ServiceStarted`, `ServiceCompleted`, `ServiceCancelled`, `TestDriveRequested`, `TestDriveApproved`, `TestDriveRejected`, `TestDriveCompleted`.
Setiap: `via()` → `['database','mail']` (+ `whatsapp` jika ada), `toArray()` dengan payload `{title, message, url, icon, color}`.

**2. Events & Listeners**
- `ServiceStatusChanged` → listener kirim notif per transisi.
- `TestDriveStatusChanged` → listener.
- Semua listener `ShouldQueue`, retry 3x, timeout 30s.

**3. WhatsApp Gateway (opsional)**
Interface `WhatsAppGateway` + implementasi `FonnteGateway`/`TwilioGateway`. Template di config. Fallback: log & lanjutkan.

**4. Real-time Status Tracking (Customer)**
Halaman `/dashboard/my-services/{id}`:
- Timeline visual dari `service_status_logs` ascending.
- Setiap node: ikon, status, waktu, aktor, catatan.
- Progress bar atas.
- Auto-refresh polling 30 detik (Alpine.js).
- Upgrade path: Laravel Echo + Pusher/Reverb.

**5. Notification Center**
`/notifications` list + pagination + filter unread/all + mark read (single/bulk). Badge unread di topbar, polling 60 detik.

**6. Testing (Pest)**

*Unit:* `TicketNumberGeneratorTest` (format, uniqueness, concurrency) · `BookingSlotValidatorTest` (kuota 3/jam, edge) · `ServiceCostCalculatorTest` · `CarSlugGeneratorTest` · semua enum `label()`+`color()`.

*Feature:* `AuthTest` (register, login, redirect per role) · `RbacTest` (setiap role akses izin/dilarang) · `BookingTest` (TD & servis, guest flow, validasi, kuota) · `DashboardTest` (data ter-filter) · `WorkOrderTest` (transisi, guard) · `NotificationTest`.

*Browser:* Showroom slider (swipe/keyboard/counter) · Modal (buka/validasi/submit/tutup) · Dashboard (aksi mekanik, approval sales) · Responsive 375/768/1440.

**7. Accessibility Audit**
- axe-core pada showroom, modal, dashboard, form.
- Manual: keyboard only, screen reader.
- Cek kontras semua kombinasi. Cek focus visible.

**8. Performance Audit**
- Lighthouse CI showroom & dashboard.
- Laravel Debugbar / Telescope: cek N+1.
- Bundle analyzer: < 180KB gzip.
- Cek semua gambar < batas.

**9. Security Audit**
- Semua route punya middleware auth/role.
- CSRF di semua form. `$fillable` eksplisit.
- SQL injection di raw query (jika ada).
- File upload validation. Rate limiting.

**10. CI Pipeline (GitHub Actions)**
Pint → PHPStan → Pest → Build → Lighthouse. Block merge jika gagal.

**11. Deployment**
Script: `git pull` → `composer install --no-dev --optimize-autoloader` → `npm ci && npm run build` → `php artisan migrate --force` → `config:cache route:cache view:cache` → `queue:restart`. Zero-downtime via symlink release.
Env prod: `APP_ENV=production APP_DEBUG=false LOG_LEVEL=warning`.
`php artisan storage:link`.

**12. Queue & Scheduler**
- Supervisor: `queue:work --queue=high,default,low`.
- Cron: `* * * * * php artisan schedule:run`.
- Scheduled: `services:remind-upcoming`, `test-drives:remind-upcoming`, `promos:deactivate-expired`, `backup:run`.

**13. Monitoring & Backup**
- Sentry/Flare error tracking.
- Telescope hanya staging.
- Health `/up` + uptime monitor.
- Log rotation harian 14 hari. Alert: error >1%, p95 >1s, queue backlog >100.
- `spatie/laravel-backup` — DB + storage harian, retensi 7/4/3, ke S3. Test restore bulanan.

**14. Documentation**
`README.md` · `docs/ARCHITECTURE.md` · `docs/DEPLOYMENT.md` · `docs/CONTRIBUTING.md` · `CHANGELOG.md`.

**15. Security Hardening**
HTTPS + HSTS · Headers: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, CSP · Rate limit route sensitif · `.env` di secret manager.

### Batasan
Notif wajib queued · Kegagalan notif tidak boleh gagalkan transaksi · Link notif mengarah ke halaman benar per role · Semua endpoint internal pakai auth+role.

### Definition of Done
- [ ] Setiap perubahan status memicu notif benar.
- [ ] Notif masuk DB + email.
- [ ] Timeline customer auto-update.
- [ ] Badge unread akurat.
- [ ] Queue worker stabil 24 jam.
- [ ] Code coverage ≥ 80%.
- [ ] Lighthouse: Perf ≥ 85, A11y ≥ 95, BP ≥ 95, SEO ≥ 90.
- [ ] axe-core: 0 critical, 0 serious.
- [ ] Tidak ada N+1 di halaman utama.
- [ ] CI hijau.
- [ ] Deploy staging sukses tanpa downtime.
- [ ] Backup berjalan & restore teruji.
- [ ] Security headers grade A (securityheaders.com).
- [ ] SSL Labs ≥ A.

### Verification
```
□ php artisan test --coverage (≥ 80%)
□ npx playwright test
□ Lighthouse CI report
□ axe DevTools: 0 critical/serious
□ ./vendor/bin/phpstan analyse
□ ./vendor/bin/pint --test
□ securityheaders.com → A
□ ssllabs.com → A
□ Test restore backup di staging
□ Cek queue failed_jobs kosong setelah 24 jam
```

---

## 📋 URUTAN EKSEKUSI

| Prompt | Fokus | Estimasi | Dependensi |
|:---:|:---|:---:|:---|
| **1** | Foundation (DB, Auth, RBAC, Seeder) | ~8 hari | — |
| **2** | Showroom 2.5D + Booking Engine | ~12 hari | Prompt 1 |
| **3** | Dashboard + Work Order + Master Data | ~12 hari | Prompt 1, 2 |
| **4** | Notif + Testing + Deployment | ~12 hari | Prompt 1–3 |
| **Total** | | **~44 hari** | |
