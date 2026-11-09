# Panduan Deployment Produksi XYZ Motors

Dokumen ini memandu proses pemasangan, konfigurasi server, dan peluncuran (zero-downtime deployment) untuk aplikasi **XYZ Motors** pada lingkungan produksi Linux (Ubuntu 22.04 / 24.04 LTS).

---

## 1. Persyaratan Server

| Komponen | Versi Minimum | Rekomendasi |
|---|---|---|
| **Sistem Operasi** | Ubuntu 22.04 LTS | Ubuntu 24.04 LTS |
| **PHP** | 8.2+ | 8.2 / 8.3 dengan FPM & OPcache |
| **Ekstensi PHP** | `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `tokenizer`, `xml`, `zip` |
| **Database** | MySQL 8.0+ / MariaDB 10.6+ | MySQL 8.0+ |
| **Web Server** | Nginx 1.20+ | Nginx 1.24+ |
| **Process Manager** | Supervisor 4.2+ | Supervisor |
| **Node.js** | Node 18+ | Node 20 LTS & NPM |
| **Composer** | Composer 2.5+ | Composer 2.7+ |

---

## 2. Pemasangan Pertama Kali (Initial Setup)

### A. Clone Repositori & Hak Akses Direktori
```bash
sudo mkdir -p /var/www/xyz-motors
sudo chown -R $USER:www-data /var/www/xyz-motors
cd /var/www/xyz-motors
git clone https://github.com/jovankasd/projek_auto-company.git .

# Setup permission untuk storage dan cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### B. Konfigurasi Environment (`.env`)
```bash
cp .env.example .env
nano .env
```
Pastikan variabel lingkungan berikut disesuaikan untuk produksi:
```ini
APP_NAME="XYZ Motors"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://xyzmotors.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xyz_motors_prod
DB_USERNAME=xyz_user
DB_PASSWORD=SuperSecretDatabasePassword123!

QUEUE_CONNECTION=database
SESSION_DRIVER=database

# WhatsApp Gateway (log, fonnte, atau twilio)
WHATSAPP_DRIVER=fonnte
FONNTE_TOKEN=your_fonnte_production_token
```

### C. Install Dependensi & Generate Key
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force --seed
php artisan storage:link
npm ci
npm run build
```

---

## 3. Konfigurasi Supervisor (Queue Worker)

Untuk memproses antrean notifikasi (Email, WhatsApp, Database Log) secara asinkron tanpa membebani thread HTTP:

1. Salin konfigurasi Supervisor:
```bash
sudo cp docs/supervisor/supervisord.conf /etc/supervisor/conf.d/xyz-motors-worker.conf
```
2. Reload dan aktifkan worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start xyz-motors-worker:*
```
3. Cek status worker:
```bash
sudo supervisorctl status
```

---

## 4. Konfigurasi Cron Scheduler

Jadwalkan perintah otomatis Laravel (pengingat servis, pengingat test drive, dan penonaktifan promo kedaluwarsa):

Buka editor crontab pengguna `www-data`:
```bash
sudo crontab -u www-data -e
```
Tambahkan baris berikut:
```cron
* * * * * cd /var/www/xyz-motors && php artisan schedule:run >> /dev/null 2>&1
```

Perintah yang dieksekusi oleh scheduler secara berkala:
- `services:remind-upcoming`: Berjalan setiap hari pukul 08:00 WIB.
- `test-drives:remind-upcoming`: Berjalan setiap hari pukul 08:30 WIB.
- `promos:deactivate-expired`: Berjalan setiap jam.

---

## 5. Konfigurasi Web Server (Nginx & SSL)

1. Salin konfigurasi Virtual Host Nginx:
```bash
sudo cp docs/nginx/xyz-motors.conf /etc/nginx/sites-available/xyz-motors.conf
sudo ln -s /etc/nginx/sites-available/xyz-motors.conf /etc/nginx/sites-enabled/
```
2. Pasang sertifikat SSL gratis via Certbot Let's Encrypt:
```bash
sudo certbot --nginx -d xyzmotors.com -d www.xyzmotors.com
```
3. Uji sintaks dan restart Nginx:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## 6. Prosedur Zero-Downtime Deployment (`deploy.sh`)

Untuk memperbarui aplikasi ke versi terbaru tanpa downtime bagi pengguna:

```bash
chmod +x deploy.sh
./deploy.sh
```

Skrip `deploy.sh` secara otomatis mengeksekusi tahapan:
1. Mengaktifkan Maintenance Mode dengan secret key.
2. Melakukan `git pull` perubahan kode terbaru.
3. Menjalankan `composer install --no-dev --optimize-autoloader`.
4. Melakukan kompilasi aset `npm ci && npm run build`.
5. Menjalankan migrasi database `php artisan migrate --force`.
6. Melakukan warm-up cache konfigurasi, rute, view, dan event.
7. Merestart worker antrean `php artisan queue:restart`.
8. Membuka kembali aplikasi dari status maintenance.
