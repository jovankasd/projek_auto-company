# ADR-006: Siklus Hidup Voucher, Validasi Atomik Servis, dan Penawaran Offline Unit Kendaraan

## Status
Accepted

## Date
2026-11-14

## Context
Pada evaluasi showroom dan audit teknis (#12 & audit UI #2 R-26), ditemukan bahwa program penawaran dan voucher resmi (seperti `XYZSUPER2024` dan `M4LUXURY50JT`) dipromosikan di halaman publik dengan tombol "Salin Kode", namun form booking servis maupun test drive tidak menyediakan input voucher, kolom kuota `max_usage`/`used_count` tidak pernah divalidasi, dan tidak ada alur penukaran riil.

Di sisi lain, PRD §1.5 secara tegas menetapkan bahwa sistem XYZ Motors v1.0 **tidak memiliki payment gateway** dan pembelian unit kendaraan dilakukan secara langsung melalui konsultasi Sales di dealer fisik. Memaksakan alur checkout mobil secara online bertentangan dengan batasan PRD, sedangkan membiarkan kode promo servis tanpa alur penukaran merusak kepercayaan pengguna (*broken promise*).

## Decision
Menerapkan pendekatan hibrida yang jujur (*hybrid honest architecture*) untuk memisahkan domain servis dan penjualan unit kendaraan:

1. **Penukaran Digital Atomik untuk Servis Bengkel (`car_id == null`):**
   - Menambahkan field `promo_code` opsional pada form reservasi servis (baik interaksi AJAX Alpine.js maupun fallback form native non-JS).
   - Menambahkan relasi nullable `promo_id` dan kolom pencatatan `discount_amount` pada tabel `services`.
   - Memvalidasi voucher di dalam database transaction dengan pessimistic row locking (`Promo::where('code', ...)->lockForUpdate()`) untuk memastikan status `is_active = true`, tanggal berada dalam rentang `starts_at` dan `ends_at`, kuota `used_count < max_usage` (PRD BR-10), serta bukan voucher khusus unit mobil.
   - Menginkremen `used_count` secara atomik saat tiket servis diterbitkan, dan mengembalikan kuota (*decrement*) jika booking dibatalkan sebelum dikerjakan.
   - Mengintegrasikan penghitungan diskon ke dalam `ServiceCostCalculator::recalculate()` sehingga `discount_amount` dan `total_cost` bersih tercatat akurat pada lembar kerja (*work order*).

2. **Transparansi Penawaran Offline untuk Unit Mobil (`car_id != null`):**
   - Kartu promo mobil di showroom secara eksplisit dilabeli sebagai *"Penawaran Dealer Fisik"* yang diklaim langsung saat konsultasi dengan Sales atau pembuatan SPK di dealer.
   - Tombol pada kartu promo mobil mengarahkan pengguna ke *"Salin Kode"* dan CTA *"Jadwalkan Test Drive & Konsultasi Sales"*, memberikan ekspektasi yang jujur tanpa menjanjikan checkout diskon instan online.

## Alternatives Considered

### Opsi 1: Menjadikan Seluruh Promo Sekadar Informasi Offline (Tanpa Fitur Input)
- **Pros:** Tidak membutuhkan migrasi database dan perubahan controller minim.
- **Cons:** Menjadikan kolom `max_usage` dan `used_count` pada database sebagai kode mati; mengabaikan otomatisasi purnajual yang menjadi target utama PRD §1.2.
- **Rejected:** Mengurangi nilai fungsional sistem aftersales XYZ Motors.

### Opsi 2: Membuat Sistem Keranjang Belanja & Checkout Mobil Daring Penuh
- **Pros:** Seluruh jenis promo bisa langsung dipotong di web.
- **Cons:** Melanggar batasan eksplisit PRD §1.5 (*Payment gateway is OUT OF SCOPE*), memerlukan verifikasi legalitas faktur kendaraan, pembiayaan leasing, dan sistem pembayaran yang belum didukung arsitektur saat ini.
- **Rejected:** *Hypothetical scale* dan pelanggaran terhadap batasan proyek.

## Consequences
- Diperlukan migrasi database non-destruktif untuk tabel `services` (`promo_id`, `discount_amount`).
- Seluruh penukaran kode voucher servis kini terverifikasi aman terhadap race condition dan kuota tercatat akurat.
- Antarmuka publik showroom memenuhi kepatuhan Anti-Slop (R-26: tidak ada tombol buntu atau janji palsu).
- Kalkulasi biaya bengkel pada status selesai secara otomatis mempertimbangkan hak diskon pelanggan yang tertera pada tiket.
