#!/bin/sh
set -e

# If the user passed a command (like php artisan ...), execute it instead
if [ $# -gt 0 ]; then
    exec "$@"
fi

# Ensure storage links are created
php artisan storage:link --no-interaction --force || true

# Start PHP-FPM as a daemon
echo "Starting PHP-FPM..."
php-fpm -D

# Start Nginx in the foreground
echo "Starting Nginx..."
exec nginx -g "daemon off;"
