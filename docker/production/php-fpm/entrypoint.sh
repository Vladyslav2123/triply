#!/bin/sh
# Use 'set -e' to exit on error, but we'll handle errors manually for better diagnostics
# set -e

echo "🔧 Laravel Entrypoint starting..."

# Initialize or update storage directory
# -----------------------------------------------------------
echo "Updating storage directory structure..."
mkdir -p /app/storage/api-docs
cp -f /app/storage-init/api-docs/api-docs.json /app/storage/api-docs/api-docs.json
chmod 664 /app/storage/api-docs/api-docs.json

# Continue with other storage directories
cp -Rn /app/storage-init/. /app/storage
chmod -R 775 /app/storage

# Remove storage-init directory
rm -rf /app/storage-init

# Run Laravel migrations
# -----------------------------------------------------------
php artisan migrate --force

# Clear and cache configurations
# -----------------------------------------------------------
php artisan optimize:clear
php artisan storage:link

# Redis connection check
# -----------------------------------------------------------
if [ "${RUN_REDIS_CHECK:-true}" = "true" ]; then
    echo "Testing Redis connection..."

    # Simple Redis connection check that won't fail the container
    php -r "try {
        \$redis = new Redis();
        \$redis->connect(getenv('REDIS_HOST') ?: 'redis', getenv('REDIS_PORT') ?: 6379, 2);
        echo \"✅ Redis connection successful\n\";
    } catch(Exception \$e) {
        echo \"⚠️ Redis connection warning: {\$e->getMessage()}\n\";
    }" || echo "⚠️ Redis check script failed but continuing startup"

    # Only run the full check if STRICT_REDIS_CHECK is enabled
    if [ "${STRICT_REDIS_CHECK:-false}" = "true" ]; then
        echo "Running comprehensive Redis check..."
        php redis-check.php || echo "⚠️ Warning: Comprehensive Redis check failed but continuing startup"
    fi
fi

# Ensure PHP-FPM can start
# -----------------------------------------------------------
echo "Checking PHP-FPM configuration..."
php-fpm -t || echo "⚠️ Warning: PHP-FPM configuration test failed but continuing startup"

# Run the default command
echo "✅ All checks completed. Starting PHP-FPM..."
exec "$@"
