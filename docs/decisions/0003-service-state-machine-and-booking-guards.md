# ADR-003: Mesin Status Servis, Validasi Kuota Slot, dan Generator Tiket Atomik

## Status
Accepted

## Date
2026-10-01

## Context
Proses booking dan pengerjaan servis bengkel memiliki aturan bisnis ketat agar kapasitas bengkel tidak berlebih, kendaraan tidak terdaftar ganda pada jam yang sama, dan alur work order terjaga:
- Kapasitas bengkel dibatasi maksimal 3 kendaraan per jam (`slot_hour`, 08:00–17:00).
- Nomor polisi yang sama tidak boleh didaftarkan dua kali pada tanggal dan slot jam yang sama (selama belum dibatalkan).
- Setiap booking servis harus mendapatkan nomor tiket unik harian berurutan dengan format `SRV-YYYYMMDD-XXXX`.
- Transisi status pengerjaan servis harus mengikuti alur yang sah dan tidak boleh selesai tanpa rincian biaya (`service_items`).

## Decision
Memisahkan logika domain inti ke enum dan service class terdedikasi:
1. **State Machine pada Enum**: [`App\Enums\ServiceStatus`](../../app/Enums/ServiceStatus.php) mendefinisikan `allowedTransitions()` dan `canTransitionTo()` (`Pending → Confirmed/Cancelled`, `Confirmed → InProgress/Cancelled`, `InProgress → Completed`, status terminal `Completed` & `Cancelled` tanpa transisi keluar).
2. **Slot & Double-Booking Validator**: [`App\Services\BookingSlotValidator`](../../app/Services/BookingSlotValidator.php) memusatkan pemeriksaan kuota per tanggal+jam (`MAX_PER_SLOT = 3`) dan keunikan plat nomor.
3. **Generator Tiket Transaksional**: [`App\Services\TicketNumberGenerator`](../../app/Services/TicketNumberGenerator.php) menggunakan `DB::transaction` dengan `lockForUpdate()` dan retry terbatas (`maxRetries = 5`) untuk menghasilkan `SRV-YYYYMMDD-XXXX`.
4. **Kalkulator Biaya Terpusat**: [`App\Services\ServiceCostCalculator`](../../app/Services/ServiceCostCalculator.php) menghitung ulang `total_cost` dari `service_items`.

## Alternatives Considered

### Validasi Langsung di Controller dengan UUID Acak untuk Tiket
- **Pros:** Tidak memerlukan query sequence harian.
- **Cons:** Nomor tiket UUID sulit dibaca pelanggan dan staf bengkel saat komunikasi via telepon/loket; logika kuota di controller sulit diuji secara unit.
- **Rejected:** Format `SRV-YYYYMMDD-XXXX` ramah manusia dan service class mudah diuji terpisah di `tests/Unit/`.

### Paket State Machine Eksternal (mis. Spatie Model States)
- **Pros:** Mendukung class state terpisah untuk tiap status dengan hook transisi bawaan.
- **Cons:** Berlebihan untuk lima status linear dengan aturan transisi sederhana.
- **Rejected:** Method `allowedTransitions()` pada PHP backed enum cukup ringkas, eksplisit, dan mudah dianalisis oleh PHPStan Level 6.

## Consequences
- Logika domain dapat diuji langsung melalui unit test (`BookingSlotValidatorTest`, `TicketNumberGeneratorTest`, `ServiceCostCalculatorTest`, `EnumsTest`).
- **Catatan Teknis (Known Caveat):** Saat ini `ServiceBookingController::store()` memanggil `BookingSlotValidator` dan `TicketNumberGenerator::generate()` sebelum transaksi `Service::create()`. Di bawah konkurensi tinggi pada database produksi (MySQL), validasi kuota dan pembuatan tiket idealnya disatukan ke dalam satu transaksi terkunci beserta constraint unik pada kombinasi tanggal + `slot_hour` + `vehicle_plate_number`.
