#!/bin/bash
# =============================================================================
# SIMBM - Ubuntu Server Install Script
# Laravel 11 + Filament 3 + PostgreSQL
#
# Jalankan sebagai root atau dengan sudo:
#   chmod +x deploy/ubuntu-install.sh
#   sudo ./deploy/ubuntu-install.sh
#
# Sebelum menjalankan, edit variabel di bawah ini.
# =============================================================================

set -euo pipefail

# --- KONFIGURASI (EDIT INI) -----------------------------------------------
APP_NAME="simbm"
APP_DIR="/var/www/${APP_NAME}"
APP_URL="http://your-domain.com"          # ganti dengan domain/IP server
GIT_REPO="https://github.com/afs10112509/simbm.git"
GIT_BRANCH="main"

# PostgreSQL lokal (install di server yang sama)
INSTALL_LOCAL_POSTGRES=true
DB_NAME="simbm"
DB_USER="simbm_user"
DB_PASSWORD="GantiPasswordKuat123!"       # ganti password database

# Atau pakai PostgreSQL eksternal (SumoPod Managed DB) — set INSTALL_LOCAL_POSTGRES=false
DB_HOST="127.0.0.1"
DB_PORT="5432"

# Admin default setelah seed (RoleAndUserSeeder)
# Email: admin@gmail.com | Password: password  → SEGERA GANTI SETELAH LOGIN!
# ---------------------------------------------------------------------------

export DEBIAN_FRONTEND=noninteractive

echo "=========================================="
echo "  SIMBM - Instalasi Ubuntu Server"
echo "=========================================="

# 1. Update sistem
echo "[1/10] Update sistem..."
apt-get update -y
apt-get upgrade -y

# 2. Install paket dasar
echo "[2/10] Install paket dasar..."
apt-get install -y software-properties-common curl git unzip acl

# 3. Install PostgreSQL (opsional)
if [ "$INSTALL_LOCAL_POSTGRES" = true ]; then
    echo "[3/10] Install PostgreSQL..."
    apt-get install -y postgresql postgresql-contrib
    systemctl enable postgresql
    systemctl start postgresql

    sudo -u postgres psql -tc "SELECT 1 FROM pg_roles WHERE rolname='${DB_USER}'" | grep -q 1 \
        || sudo -u postgres psql -c "CREATE USER ${DB_USER} WITH PASSWORD '${DB_PASSWORD}';"
    sudo -u postgres psql -tc "SELECT 1 FROM pg_database WHERE datname='${DB_NAME}'" | grep -q 1 \
        || sudo -u postgres psql -c "CREATE DATABASE ${DB_NAME} OWNER ${DB_USER};"
    sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};"
    DB_HOST="127.0.0.1"
else
    echo "[3/10] Lewati install PostgreSQL (pakai database eksternal)..."
fi

# 4. Install PHP 8.2
echo "[4/10] Install PHP 8.2..."
add-apt-repository ppa:ondrej/php -y
apt-get update -y
apt-get install -y \
    php8.2-fpm php8.2-cli php8.2-pgsql php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
    php8.2-intl php8.2-readline php8.2-tokenizer php8.2-fileinfo php8.2-dom

# 5. Install Composer
echo "[5/10] Install Composer..."
if ! command -v composer &>/dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# 6. Install Nginx
echo "[6/10] Install Nginx..."
apt-get install -y nginx
systemctl enable nginx

# 7. Clone / update aplikasi
echo "[7/10] Deploy aplikasi..."
if [ -d "$APP_DIR/.git" ]; then
    cd "$APP_DIR"
    git pull origin "$GIT_BRANCH"
else
    git clone -b "$GIT_BRANCH" "$GIT_REPO" "$APP_DIR"
    cd "$APP_DIR"
fi

# 8. Install dependency Laravel
echo "[8/10] Composer install..."
composer install --no-dev --optimize-autoloader --no-interaction

# Setup .env
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Update .env production
sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|" .env

grep -q "^DB_HOST=" .env && sed -i "s|^DB_HOST=.*|DB_HOST=${DB_HOST}|" .env || echo "DB_HOST=${DB_HOST}" >> .env
grep -q "^DB_PORT=" .env && sed -i "s|^DB_PORT=.*|DB_PORT=${DB_PORT}|" .env || echo "DB_PORT=${DB_PORT}" >> .env
grep -q "^DB_DATABASE=" .env && sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env || echo "DB_DATABASE=${DB_NAME}" >> .env
grep -q "^DB_USERNAME=" .env && sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env || echo "DB_USERNAME=${DB_USER}" >> .env
grep -q "^DB_PASSWORD=" .env && sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env || echo "DB_PASSWORD=${DB_PASSWORD}" >> .env

php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

# Permission
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 storage bootstrap/cache

# 9. Konfigurasi Nginx
echo "[9/10] Konfigurasi Nginx..."
SERVER_NAME=$(echo "$APP_URL" | sed -e 's|https\?://||' -e 's|/.*||')

cat > "/etc/nginx/sites-available/${APP_NAME}" <<NGINX
server {
    listen 80;
    server_name ${SERVER_NAME};
    root ${APP_DIR}/public;

    index index.php;
    charset utf-8;

    client_max_body_size 50M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

ln -sf "/etc/nginx/sites-available/${APP_NAME}" "/etc/nginx/sites-enabled/${APP_NAME}"
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
systemctl restart php8.2-fpm

# 10. Queue worker (systemd)
echo "[10/10] Setup queue worker..."
cat > "/etc/systemd/system/${APP_NAME}-queue.service" <<SERVICE
[Unit]
Description=SIMBM Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=5
ExecStart=/usr/bin/php ${APP_DIR}/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
SERVICE

systemctl daemon-reload
systemctl enable "${APP_NAME}-queue"
systemctl restart "${APP_NAME}-queue"

echo ""
echo "=========================================="
echo "  INSTALASI SELESAI!"
echo "=========================================="
echo "  URL Admin : ${APP_URL}/admin"
echo "  Login     : admin@gmail.com"
echo "  Password  : password  (SEGERA GANTI!)"
echo "=========================================="
echo ""
echo "Opsional - HTTPS dengan Certbot:"
echo "  apt install certbot python3-certbot-nginx -y"
echo "  certbot --nginx -d ${SERVER_NAME}"
echo ""
