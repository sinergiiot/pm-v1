# Production Setup Guide for VPS

Dokumen ini berisi langkah-langkah untuk melakukan optimasi dan setup `filament-pm` di VPS.

## 1. Persyaratan Sistem
Pastikan VPS Anda memiliki komponen berikut:
- **PHP 8.2+** (Direkomendasikan 8.3/8.4)
- **Ekstensi PHP**: `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `hash`, `iconv`, `intl`, `json`, `libxml`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `xmlwriter`, `zip`
- **Database**: MySQL 8.0+ atau MariaDB 10.4+
- **Web Server**: Nginx (direkomendasikan) atau Apache
- **Composer** (v2+)
- **Node.js & NPM** (untuk build assets)

---

## 2. Optimasi PHP
Edit file `php.ini` Anda (biasanya di `/etc/php/8.x/fpm/php.ini`):

```ini
# Aktifkan OPcache
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0 # Set ke 1 saat testing, 0 untuk production maksimal
opcache.revalidate_freq=0

# Memory Limit
memory_limit = 512M
upload_max_filesize = 64M
post_max_size = 64M
```

---

## 3. Konfigurasi Environment (`.env`)
Pastikan variabel berikut diset dengan benar di production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

# Database (Gunakan password yang kuat)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=filament_pm
DB_USERNAME=filament_user
DB_PASSWORD=your_strong_password

# Cache & Session (Rekomendasi gunakan redis jika tersedia)
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

---

## 4. Langkah Deployment & Optimasi
Kami telah menyediakan script dan command composer untuk memudahkan proses ini.

### Menggunakan Composer (Direkomendasikan)
Jalankan command ini di folder root project setiap kali ada update code:
```bash
composer deploy
```
Command ini akan melakukan:
1. `composer install --no-dev --optimize-autoloader`
2. `php artisan migrate --force`
3. `npm install`
4. `npm run build`
5. **Optimasi Caching** (Config, Route, View, Event, Icons, Filament Components)

### Menggunakan Script Shell
Anda juga bisa menggunakan script di `scripts/deploy.sh`:
```bash
chmod +x scripts/deploy.sh
./scripts/deploy.sh
```

---

## 5. Supervisor (Queue Worker)
Karena project ini menggunakan Queue (database) untuk Google Calendar sync dan notifikasi, Anda PERLU menjalankan queue worker secara persisten.

Install Supervisor:
```bash
sudo apt-get install supervisor
```

Buat file konfigurasi `/etc/supervisor/conf.d/filament-pm-worker.conf`:
```ini
[program:filament-pm-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/filament-pm/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/filament-pm/storage/logs/worker.log
stopwaitsecs=3600
```

Update dan start:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start filament-pm-worker:*
```

---

## 6. Cron Job (Scheduler)
Project ini memiliki scheduled task (`remind:incomplete-tasks`). Tambahkan cron job ini ke user server (biasanya `www-data` atau user Anda):

```bash
crontab -e
```

Tambahkan baris berikut:
```bash
* * * * * cd /var/www/filament-pm && php artisan schedule:run >> /dev/null 2>&1
```

---

## 7. Izin Directory (Permissions)
Pastikan folder `storage` dan `bootstrap/cache` dapat ditulis oleh web server:

```bash
sudo chown -R www-data:www-data /var/www/filament-pm
sudo chmod -R 775 /var/www/filament-pm/storage
sudo chmod -R 775 /var/www/filament-pm/bootstrap/cache
```

🚀 Selesai! Aplikasi Anda sekarang sudah teroptimasi untuk production di VPS.
