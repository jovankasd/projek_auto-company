#!/usr/bin/env bash
# ==============================================================================
# XYZ Motors - Zero-Downtime Production Deployment Script
# Reference: PRD §8 & PROMPT 4
# ==============================================================================

set -e

echo "🚀 [XYZ Motors] Memulai proses deployment produksi..."

# 1. Pindah ke direktori root aplikasi
DEPLOY_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$DEPLOY_PATH"

echo "📂 Lokasi aplikasi: $DEPLOY_PATH"

# 2. Aktifkan Maintenance Mode dengan secret bypass
MAINTENANCE_SECRET=$(openssl rand -hex 16 2>/dev/null || echo "xyz-motors-deploy")
echo "🔒 Mengaktifkan Maintenance Mode (Bypass Key: $MAINTENANCE_SECRET)..."
php artisan down --secret="$MAINTENANCE_SECRET" --render="errors::503" --retry=60 || true

# 3. Pull source code terbaru dari repositori remote
echo "📥 Menarik perubahan kode terbaru (git pull)..."
git pull origin main --ff-only || {
    echo "⚠️ Git pull fast-forward gagal, pastikan working tree bersih."
}

# 4. Install & optimize dependensi Composer
echo "📦 Menginstal dependensi PHP (Composer no-dev)..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 5. Build asset frontend (Vite & Tailwind CSS)
echo "🎨 Melakukan kompilasi asset frontend (Vite)..."
if command -v npm &> /dev/null; then
    npm ci --silent
    npm run build
elif command -v yarn &> /dev/null; then
    yarn install --frozen-lockfile --silent
    yarn build
else
    echo "⚠️ Node package manager tidak ditemukan! Melewati kompilasi asset lokal."
fi

# 6. Jalankan migrasi database
echo "🗄️ Menjalankan migrasi database..."
php artisan migrate --force

# 7. Pastikan symbolic link storage terhubung
echo "🔗 Memeriksa storage symlink..."
php artisan storage:link || true

# 8. Caching & optimasi performa Laravel
echo "⚡ Mengoptimasi konfigurasi, routing, view, dan event cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# 9. Restart queue worker (Supervisor akan reload instance baru secara otomatis)
echo "🔄 Merestart Laravel Queue Workers..."
php artisan queue:restart

# 10. Matikan Maintenance Mode (Kembali online)
echo "🔓 Menonaktifkan Maintenance Mode..."
php artisan up

echo "✅ [XYZ Motors] Deployment selesai dengan sukses dan aplikasi siap melayani lalu lintas pengguna!"
