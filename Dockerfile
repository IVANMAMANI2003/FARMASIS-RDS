# Multi-stage Dockerfile for Laravel (Apache) with Vite build and auto-migrations

# 1) Composer dependencies
FROM composer:2 AS vendor
WORKDIR /app

RUN apk add --no-cache icu-dev && docker-php-ext-install intl

# Copiar TODO el proyecto antes de instalar
COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

# 2) Node build for Vite assets
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --silent
COPY . .
RUN npm run build

# 3) Final runtime: PHP 8.3 + Apache
FROM php:8.3-apache AS app

# Install system dependencies and PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
    && docker-php-ext-install \
        intl \
        pdo_mysql \
        mbstring \
        exif \
        bcmath \
        zip \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite for Laravel
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copy application source
COPY . .

# Copy Composer vendor directory from builder
COPY --from=vendor /app/vendor ./vendor

# Copy built assets from Node builder
COPY --from=assets /app/public/build ./public/build

# Set proper permissions for storage and cache
RUN chown -R www-data:www-data storage bootstrap/cache \
	&& chmod -R 775 storage bootstrap/cache

# Optimize Laravel (routes/config) at build time if desired (safe to skip if env not ready)
# RUN php artisan config:cache || true && php artisan route:cache || true

# Create entrypoint script to: generate key (if missing), link storage, run migrations+seed, then start Apache
RUN printf '%s\n' \
    '#!/usr/bin/env bash' \
    'set -e' \
    '' \
    'cd /var/www/html' \
    '' \
    '# Ensure APP_KEY exists' \
    'if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "" ]; then' \
    '  php artisan key:generate --force || true' \
    'fi' \
    '' \
    '# Storage symlink (idempotent)' \
    'if [ ! -L public/storage ]; then' \
    '  php artisan storage:link || true' \
    'fi' \
    '' \
    '# Clear caches to pick env up' \
    'php artisan config:clear || true' \
    'php artisan route:clear || true' \
    'php artisan view:clear || true' \
    '' \
    '# Run migrations against RDS (expects DB_* env vars) and seed' \
    'if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then' \
    '  php artisan migrate --force || true' \
    '  if [ "${RUN_SEED:-true}" = "true" ]; then' \
    '    php artisan db:seed --force || true' \
    '  fi' \
    'fi' \
    '' \
    '# Start Apache in foreground' \
    'exec apache2-foreground' \
    > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Configure Apache DocumentRoot and overrides for Laravel public dir
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
	&& sed -ri 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf \
	&& printf '\n<Directory /var/www/html/public>\n\tAllowOverride All\n</Directory>\n' >> /etc/apache2/apache2.conf

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
	APP_ENV=production \
	APP_DEBUG=false \
	RUN_MIGRATIONS=true \
	RUN_SEED=true

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

