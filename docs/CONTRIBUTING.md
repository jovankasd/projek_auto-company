# Panduan Kontribusi XYZ Motors

Terima kasih atas minat Anda untuk berkontribusi pada pengembangan platform otomotif **XYZ Motors**. Panduan ini memastikan standar kualitas kode, keamanan, dan konsistensi arsitektur tetap terjaga.

---

## 1. Alur Kerja Git & Percabangan (Branching Strategy)

Kami menerapkan **Trunk-Based Development** agar branch `main` selalu siap deploy dan melewati seluruh checks CI:

- `main`: Branch utama produksi yang selalu stabil dan terverifikasi CI.
- `feature/<nama-fitur>`: Branch pendek untuk fitur baru (contoh: `feature/whatsapp-interactive-buttons`).
- `fix/<nama-bug>`: Branch pendek untuk perbaikan bug (contoh: `fix/booking-quota-race-condition`).
- `docs/<topik>` / `chore/<topik>`: Branch untuk pembaruan dokumentasi, ADR, konfigurasi, atau tooling.

### Alur Pull Request (PR):
1. Buat branch baru dari `main` dan jaga tetap berumur pendek (1–3 hari).
2. Jika mengubah arsitektur, kontrak API, atau keputusan teknis signifikan, baca dan perbarui ADR di [`docs/decisions/`](decisions/README.md).
3. Tulis commit atomik yang fokus pada satu perubahan logis dan jelaskan alasan perubahan (*why*).
4. Jalankan rangkaian tes lokal, analisis statis, dan formatting sebelum commit/PR:
   ```bash
   ./vendor/bin/pint --test
   ./vendor/bin/phpstan analyse --memory-limit=1G
   php artisan test --compact
   ```
5. Buat PR ke `main` dengan ringkasan perubahan yang dibuat serta hasil verifikasinya.

---

## 2. Standar Kode & Format (Code Style)

Proyek ini menerapkan standar resmi **Laravel Pint** (berbasis PSR-12) dan analisa statis **PHPStan (Larastan) Level 6**:

- **Formatting Otomatis**:
  ```bash
  ./vendor/bin/pint
  ```
- **Analisis Statis**:
  ```bash
  ./vendor/bin/phpstan analyse --memory-limit=1G
  ```
- **Aturan Pengetikan Ketat**: Selalu sertakan deklarasi tipe data untuk parameter fungsi dan return type (`string`, `int`, `bool`, `array`, `void`, dll.).

---

## 3. Konvensi Pesan Commit (Conventional Commits)

Format judul commit yang disarankan:
```
<tipe>(<lingkup>): <deskripsi singkat dalam imperatif>
```

Contoh:
- `feat(services): tambah visual real-time status tracker dengan polling Alpine.js`
- `fix(booking): cegah double booking plat nomor pada slot jam yang sama`
- `test(notifications): tambah pengujian unit dan fitur untuk notifikasi work order`
- `refactor(models): bersihkan redundant relationships pada model Service`
- `docs(deployment): perbarui panduan konfigurasi supervisor worker`

---

## 4. Pengujian Otomatis (Testing with Pest)

Setiap perubahan fitur baru atau perbaikan bug **wajib disertai pengujian otomatis**:

- Jalankan seluruh rangkaian tes:
  ```bash
  php artisan test
  ```
- Jalankan pengujian spesifik fitur:
  ```bash
  php artisan test tests/Feature/NotificationTest.php
  ```
- **Prinsip Pest**: Gunakan ekspektasi deklaratif (`expect($val)->toBe(...)`) dan pastikan setiap skenario happy-path maupun edge-case (unauthorized, invalid slot, quota full) tercakup.

---

## 5. Keamanan & Kebijakan Data

1. **Prinsip Least Privilege**: Pastikan endpoint dibatasi menggunakan Policy (`$this->authorize(...)`) dan Middleware peran.
2. **Sanitasi & Validasi**: Semua input form wajib melalui `FormRequest` atau `$request->validate(...)` dengan aturan validasi eksplisit.
3. **Hindari Kebocoran Kredensial**: Jangan pernah melakukan commit file `.env`, API key WhatsApp, atau kredensial database ke repositori Git.
