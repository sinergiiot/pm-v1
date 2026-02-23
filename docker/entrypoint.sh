#!/bin/sh
set -e

# Run migrations if enabled (usually good for first time or auto-deploy)
# php artisan migrate --force

# Run optimizations with the actual .env values
php artisan package:discover --ansi
php artisan filament:upgrade
php artisan filament:optimize
php artisan view:cache
php artisan event:cache

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisord.conf
