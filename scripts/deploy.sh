#!/bin/bash
set -e

echo "Starting Deployment..."

# 1. Maintenance Mode
php artisan down || true

# 2. Fix Permissions
# Ensure storage and cache are writable by the web server
sudo chown -R www-data:www-data .
sudo chmod -R 775 storage bootstrap/cache

# 2. Update Code
# git pull origin main (Optional: if run from inside the script)

# 3. Install/Update Dependencies
composer install --no-dev --optimize-autoloader

# 4. Migrate Database
php artisan migrate --force

# 5. Build Assets (Vite)
npm install
npm run build

# 6. Apply Optimizations & Sync Permissions
php artisan shield:generate --all --panel=admin --option=policies_and_permissions --no-interaction
php artisan optimize      # Caches config and routes
php artisan view:cache
php artisan event:cache
php artisan filament:optimize

# 7. Restart Queues
php artisan queue:restart

# 8. Clear old deployments/cache if necessary
# php artisan cache:clear

# 9. Maintenance Mode Off
php artisan up

echo "Deployment Successful!"
