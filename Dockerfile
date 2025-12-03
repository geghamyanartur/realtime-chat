FROM php:8.3-fpm-bullseye as base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libonig-dev libxml2-dev libzip-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pdo_sqlite \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set workdir
WORKDIR /var/www/html

# Copy app source and install PHP deps
COPY . .
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Build frontend
FROM node:20-bullseye-slim as frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY resources resources
COPY vite.config.js ./
RUN npm run build

# Final image
FROM php:8.3-fpm-bullseye

RUN apt-get update && apt-get install -y libpq-dev libzip-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy vendor from build stage
COPY --from=base /var/www/html /var/www/html

# Copy built frontend
COPY --from=frontend /app/public ./public

# Environment
ENV APP_ENV=production \
    APP_DEBUG=false \
    QUEUE_CONNECTION=sync \
    CACHE_STORE=database \
    SESSION_DRIVER=database \
    BROADCAST_CONNECTION=pusher

# Ensure storage/bootstrap writable
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose FPM port
EXPOSE 8000

CMD ["sh", "-c", "\
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
php artisan migrate --force && \
php artisan serve --host 0.0.0.0 --port ${PORT:-8000} \
"]
