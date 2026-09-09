# ==============================================================================
# Stage 1: Build Frontend Assets (Vite + Vue 3 + Tailwind CSS)
# ==============================================================================
FROM node:22-alpine AS frontend

WORKDIR /app

# Copy dependency manifests
COPY package.json package-lock.json ./

# Install npm dependencies
RUN npm ci

# Copy project source files required for asset compilation
COPY resources ./resources
COPY public ./public
COPY vite.config.ts tsconfig.json components.json ./

# Compile production bundles
RUN npm run build

# ==============================================================================
# Stage 2: Application Runtime (PHP 8.3 FPM + Nginx + OPcache)
# ==============================================================================
FROM php:8.3-fpm-alpine AS runtime

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    nginx \
    supervisor \
    sqlite \
    sqlite-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    libzip-dev

# Install official PHP extension installer helper
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install required PHP extensions for Laravel, Stancl Tenancy, Filament, and Cashier
RUN install-php-extensions \
    bcmath \
    gd \
    intl \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    redis \
    sqlite3 \
    zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Install Composer dependencies (cached layer)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Copy full application source code
COPY . .

# Copy compiled frontend assets from frontend build stage
COPY --from=frontend /app/public/build ./public/build

# Complete composer autoload generation
RUN composer dump-autoload --optimize --no-dev --no-interaction

# Copy configurations
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php.ini $PHP_INI_DIR/conf.d/99-tenantforge.ini
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Fix permissions for scripts and web directories
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Expose HTTP port
EXPOSE 80

# Configure entrypoint and process manager
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
