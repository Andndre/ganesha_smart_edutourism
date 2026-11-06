#!/bin/sh
set -e

# If the user passed a command (like php artisan queue:work or reverb:start), execute it instead
if [ $# -gt 0 ]; then
    exec "$@"
fi

# Ensure storage links are created
php artisan storage:link --no-interaction --force || true

# Start Laravel Octane with FrankenPHP Worker Mode in the foreground
echo "Starting Laravel Octane (FrankenPHP Worker Mode)..."
exec php artisan octane:frankenphp \
    --host=0.0.0.0 \
    --port=80 \
    --workers="${OCTANE_WORKERS:-8}" \
    --max-requests="${OCTANE_MAX_REQUESTS:-500}" \
    --caddyfile=/var/www/Caddyfile
