#!/bin/bash
# Update aplikasi SIMBM setelah git pull
# Jalankan: sudo ./deploy/update.sh

set -euo pipefail

APP_DIR="/var/www/simbm"

cd "$APP_DIR"

echo "Maintenance mode ON..."
php artisan down || true

git pull origin main

composer install --no-dev --optimize-autoloader --no-interaction

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

chown -R www-data:www-data "$APP_DIR"
chmod -R 775 storage bootstrap/cache

systemctl restart php8.2-fpm
systemctl restart simbm-queue

echo "Maintenance mode OFF..."
php artisan up

echo "Update selesai!"
