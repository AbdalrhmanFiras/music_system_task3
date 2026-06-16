#!/bin/sh
set -e

echo "Starting Laravel application..."

# Create storage directories if they don't exist
mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Wait for database connection (max 60 seconds)
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database ($DB_HOST:$DB_PORT)..."
    MAX_RETRIES=30
    RETRY_COUNT=0
    until php -r "
        try {
            \$driver = getenv('DB_CONNECTION') ?: 'mysql';
            \$host = getenv('DB_HOST');
            \$port = getenv('DB_PORT') ?: (\$driver === 'pgsql' ? '5432' : '3306');
            \$db   = getenv('DB_DATABASE');
            \$user = getenv('DB_USERNAME');
            \$pass = getenv('DB_PASSWORD');
            \$dsn  = \"\$driver:host=\$host;port=\$port;dbname=\$db\";
            \$options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3
            ];
            \$pdo  = new PDO(\$dsn, \$user, \$pass, \$options);
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " > /dev/null 2>&1; do
        RETRY_COUNT=$((RETRY_COUNT + 1))
        if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
            echo "WARNING: Could not connect to database after ${MAX_RETRIES} attempts. Continuing anyway..."
            break
        fi
        echo "Database is unavailable - attempt $RETRY_COUNT/$MAX_RETRIES - sleeping..."
        sleep 2
    done
    if [ $RETRY_COUNT -lt $MAX_RETRIES ]; then
        echo "Database is up and running!"
    fi
fi

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Run database migrations
echo "Running migrations..."
php artisan migrate --force || echo "WARNING: Migrations failed, continuing..."

# Cache Laravel config/routes/views (requires env vars - must run at runtime)
echo "Caching configuration and routes..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Create storage symlink
php artisan storage:link --force || true

echo "Application is ready!"

# Start the container process (supervisord)
exec "$@"
