# Stage 1: PHP Dependencies
FROM php:8.3-fpm-alpine AS composer-builder
WORKDIR /app
RUN apk add --no-cache git unzip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --no-dev --optimize

# Stage 2: Build Assets
FROM node:20-alpine AS assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=composer-builder /app/resources ./resources
COPY --from=composer-builder /app/vite.config.js ./
RUN npm run build

# Stage 2: PHP Application
FROM php:8.3-fpm-alpine

# Install System Dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev \
    mysql-client \
    zip \
    unzip \
    git \
    curl

# Install PHP Extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql mbstring zip exif pcntl bcmath intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set Working Directory
WORKDIR /var/www/html

# Copy Application Code
COPY . .

# Copy vendor and assets from builders
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=assets-builder /app/public/build ./public/build

# Set Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Nginx & Supervisor Configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Optimization Commands
RUN php artisan filament:optimize
RUN php artisan view:cache
RUN php artisan event:cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
