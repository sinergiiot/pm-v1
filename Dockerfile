# Stage 1: PHP Dependencies
FROM composer:latest AS composer-builder
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

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
    sqlite-dev \
    mysql-client \
    zip \
    unzip \
    git \
    curl

# Install PHP Extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql pdo_sqlite mbstring zip exif pcntl bcmath intl

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
# We use a dummy SQLite connection to allow booting the app during build
RUN export APP_KEY=base64:$(php -r 'echo base64_encode(random_bytes(32));') && \
    export DB_CONNECTION=sqlite && \
    export DB_DATABASE=:memory: && \
    php artisan package:discover --ansi && \
    php artisan filament:upgrade && \
    php artisan filament:optimize && \
    php artisan view:cache && \
    php artisan event:cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
