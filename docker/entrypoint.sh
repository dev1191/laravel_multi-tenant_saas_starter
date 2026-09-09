#!/bin/sh
set -e

# Ensure storage and bootstrap directories exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public \
         /var/www/html/bootstrap/cache \
         /var/www/html/database

# Create SQLite database file if using SQLite and not present
if [ "${DB_CONNECTION}" = "sqlite" ] || [ -z "${DB_CONNECTION}" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        touch /var/www/html/database/database.sqlite
    fi
fi

# Ensure correct file permissions
chown -R www-data:www-data /var/www/html/storage \
                           /var/www/html/bootstrap/cache \
                           /var/www/html/database || true

chmod -R 775 /var/www/html/storage \
             /var/www/html/bootstrap/cache \
             /var/www/html/database || true

# Create storage symlink if missing
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link || true
fi

# Discover Laravel and package service providers
php artisan package:discover --ansi || true

# Run database migrations if RUN_MIGRATIONS is set to true
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true
fi

# Clear and optimize configuration and views if in production
if [ "${APP_ENV}" = "production" ]; then
    echo "Optimizing Laravel for production..."
    php artisan route:clear || true
    php artisan config:cache || true
    php artisan view:cache || true
fi

# Execute main process (FrankenPHP or artisan command)
exec "$@"
