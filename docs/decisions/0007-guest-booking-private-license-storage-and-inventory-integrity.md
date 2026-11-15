# ADR-007: Reservasi Guest Tanpa Wajib Login, Penyimpanan Privat Foto SIM, dan Integritas Stok Work Order

## Status
Accepted

## Date
2026-11-15

## Context
Pada pemeriksaan akhir lintas alur (*Final Release Gate*), aplikasi **XYZ Motors (Dealer Pro)** membutuhkan beberapa penyempurnaan arsitektural agar alur pelanggan publik mudah digunakan sekaligus aman secara data dan operasional bengkel:

1. **Hambatan Konversi Pengunjung Publik**: Mewajibkan pengunjung membuat akun dan login sebelum mengajukan test drive atau booking servis menimbulkan friksi tinggi di showroom, padahal pengunjung tetap perlu memantau status pengajuan secara mandiri.
2. **Keamanan Data Identitas Sensitif (Foto SIM A)**: Foto SIM yang sebelumnya tersimpan pada disk `public` berisiko diakses langsung lewat URL publik tanpa pemeriksaan otorisasi, serta seluruh *Sales* berpotensi melihat prospek milik rekan lain (*horizontal IDOR*).
3. **Integritas Harga dan Stok Suku Cadang pada Work Order**: Jika penambahan item suku cadang mempercayai harga dari payload klien dan tidak memotong stok gudang secara atomik, total tagihan dan jumlah stok di katalog `SparePart` menjadi tidak konsisten.
4. **Penguncian Siklus Hidup Tiket & Keamanan Tepi (*Edge Security*)**: Tiket servis/test drive yang sudah berstatus terminal (`completed`, `cancelled`, `rejected`) tidak boleh dimutasi ulang, dan endpoint publik maupun autentikasi perlu perlindungan terhadap *brute-force*, *spam*, serta *reverse proxy*.

## Decision

1. **Alur Guest-First & Pelacakan Tiket Ganda (`SRV-` / `TD-`)**:
   - Pengunjung dapat langsung mengajukan test drive (`POST /test-drive`) maupun booking servis (`POST /service-booking`) tanpa login terlebih dahulu.
   - `User::resolveGuestCustomer()` mencari akun berdasarkan email (termasuk yang *soft-deleted*) atau membuat entitas kontak pelanggan pasif dengan `role = UserRole::Customer` dan `is_active = false` semata-mata untuk integritas relasi *foreign key* tanpa membuka akses login portal (lihat [ADR-008](0008-staff-only-portal-and-guest-ticket-tracking.md)).
   - `TicketNumberGenerator` menghasilkan nomor tiket unik harian terkunci (`DB::transaction` + `lockForUpdate`) untuk servis (`SRV-YYYYMMDD-XXXX`) dan test drive (`TD-YYYYMMDD-XXXX`, melalui kolom `test_drives.ticket_number`).
   - Endpoint publik `GET /track-ticket` (`HomeController::trackTicket`, dilindungi `throttle:30,1`) melayani pencarian progres baik tiket servis maupun test drive pada bagian `#lacak-tiket` showroom.

2. **Isolasi Foto SIM di Penyimpanan Privat & State Machine Test Drive**:
   - `TestDriveController::store()` menyimpan unggahan foto SIM ke disk `local` (`licenses/`), bukan disk `public`.
   - Akses foto SIM dilayani melalui rute terotentikasi `GET /dashboard/test-drives/{testDrive}/license` (`TestDriveController::viewLicense`) dengan pemeriksaan `TestDrivePolicy::viewLicense` (`SuperAdmin`, `Sales` yang ditugaskan / prospek belum bertuan, serta kompatibilitas internal `customer_id`).
   - `TestDriveStatus` menerapkan transisi formal (`allowedTransitions()` & `canTransitionTo()`) untuk status `Requested → Approved/Rejected/Cancelled` dan `Approved → Completed/Cancelled`, melarang perubahan dari status terminal.

3. **Integritas Harga Server-Side & Pengurangan Stok Atomik pada Work Order**:
   - `ServiceController::addItem()` dan `ServiceController::removeItem()` membungkus mutasi dalam `DB::transaction()` dengan `SparePart::where('part_number', ...)->lockForUpdate()`.
   - Untuk item bertipe `ServiceItemType::Part` dengan nomor part terdaftar, harga satuan diambil langsung dari `SparePart::$price` di server (mengabaikan manipulasi harga dari klien), stok divalidasi dan dikurangi secara atomik saat item ditambahkan, serta dikembalikan ketika item dihapus.
   - `ServicePolicy` dan `ServiceController` menolak perubahan item, status, maupun penugasan ulang mekanik jika servis berada pada status terminal (`Completed` atau `Cancelled`).

4. **Penguatan Keamanan Tepi & Anti-Bot**:
   - `SecurityHeaders` menambahkan header `Content-Security-Policy` (CSP) dan mengaktifkan HSTS di lingkungan `production` maupun HTTPS; `bootstrap/app.php` mengaktifkan `trustProxies(at: '*')`.
   - Rute registrasi, lupa/reset kata sandi, verifikasi email, booking publik, pelacakan tiket, dan formulir kontak (`POST /contact` dengan *honeypot* `website_url`) dilindungi middleware `throttle`.

## Alternatives Considered

### Mewajibkan Login Penuh Sebelum Pengajuan Booking & Test Drive
- **Pros:** Tidak perlu resolusi entitas pelanggan sementara (`is_active = false`).
- **Cons:** Menurunkan tingkat konversi pengunjung showroom yang hanya ingin menjadwalkan test drive atau servis cepat.
- **Rejected:** Alur guest-first dengan nomor tiket `SRV-` / `TD-` tetap menjaga integritas relasi `customer_id` tanpa menghalangi pengunjung.

### Menyimpan Foto SIM di Disk `public` dengan Nama File Acak (UUID)
- **Pros:** Tidak memerlukan endpoint streaming PHP di controller.
- **Cons:** Berkas tetap dapat diakses oleh siapa saja yang mengetahui atau menemukan URL statisnya tanpa autentikasi dan pengecekan kepemilikan.
- **Rejected:** Penyimpanan di disk `local` dengan policy-gated streaming memenuhi prinsip perlindungan data pribadi dan mencegah kebocoran dokumen identitas.

### Mengambil Harga Suku Cadang Langsung dari Input Form Work Order
- **Pros:** Fleksibel untuk penyesuaian harga ad-hoc di sisi browser.
- **Cons:** Rawan manipulasi payload di klien dan membuat nilai inventaris `SparePart` menyimpang dari pemakaian aktual bengkel.
- **Rejected:** Sumber kebenaran tunggal (*single source of truth*) harga dan stok harus berada di server dalam transaksi terkunci.

## Consequences
- Pengunjung publik dapat melakukan reservasi dan memantau tiket melalui `/track-ticket` tanpa akun aktif, sementara akun placeholder tidak dapat dipakai login karena `is_active = false` ditolak oleh `LoginRequest` dan `EnsureUserIsActive`.
- Foto SIM lama yang pernah diunggah ke `storage/app/public` (jika ada pada lingkungan lama) tidak otomatis dipindahkan dan perlu dimigrasikan secara manual bila berasal dari instalasi pra-rilis ini.
- Mutasi stok suku cadang pada Work Order menuntut kesesuaian `part_number` terhadap tabel `spare_parts`; item jasa (*Labor*) dan komponen non-katalog (*Consumable* atau part tanpa kode katalog) tetap memakai harga satuan tervalidasi dari input mekanik/admin.
