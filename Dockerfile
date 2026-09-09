# ==============================================================================
# Application Runtime (FrankenPHP + PHP 8.3 Alpine)
# ==============================================================================
FROM dunglas/frankenphp:1-php8.3-alpine AS runtime

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    sqlite \
    sqlite-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    libzip-dev

# Install required PHP extensions for Laravel, Stancl Tenancy, Filament, and Cashier
RUN install-php-extensions \
    bcmath \
    exif \
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

# Set Composer environment
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# Copy application source code (including composer manifests and pre-built public/build assets)
COPY . .

# Install Composer dependencies and generate optimized autoloader
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

# Copy custom configurations
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/php.ini $PHP_INI_DIR/conf.d/99-tenantforge.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Fix permissions for scripts and web directories
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Expose HTTP and HTTPS ports (TCP and UDP for HTTP/3)
EXPOSE 80 443 443/udp

# Configure entrypoint and default FrankenPHP process
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
