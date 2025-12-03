FROM php:8.3-bullseye as base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libonig-dev libxml2-dev libzip-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy full app and install PHP dependencies
COPY . .
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Node dependencies
FROM node:20-bullseye-slim as node_deps
WORKDIR /app
COPY package*.json ./
RUN npm install

# Final image
FROM php:8.3-bullseye

RUN apt-get update && apt-get install -y libpq-dev libzip-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy app with vendor
COPY --from=base /var/www/html /var/www/html

# Add Node runtime and npm
COPY --from=node:20-bullseye-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:20-bullseye-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# Bring preinstalled node_modules for faster builds
COPY --from=node_deps /app/node_modules /var/www/html/node_modules
COPY --from=node_deps /app/package*.json /var/www/html/

# Ensure storage/bootstrap writable
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["sh", "-c", "\
npm run build && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
php artisan migrate --force && \
php artisan serve --host 0.0.0.0 --port ${PORT:-8000} \
"]
