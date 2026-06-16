# 🐳 Laravel + Docker Deployment Guide
### Deploy a Laravel (PostgreSQL) app to any VPS using Docker & Dokploy

---

## 📁 Required File Structure

Add these files to the **root of your Laravel project**:

```
your-project/
├── Dockerfile
├── docker-compose.yml
├── .dockerignore
└── docker/
    ├── entrypoint.sh
    ├── supervisord.conf
    └── nginx/
        └── default.conf
```

---

## 1. `Dockerfile`

> Builds the app image: PHP 8.4 + nginx + supervisor all in one container.

```dockerfile
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    nginx \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    bcmath \
    mbstring \
    exif \
    pcntl \
    gd \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application files
COPY . .

# Fallback .env for build-time artisan calls
RUN [ -f .env ] || cp .env.example .env

# Install PHP dependencies
# --no-scripts: stops artisan running during build (no DB at this stage)
# COMPOSER_MEMORY_LIMIT=-1: prevents OOM on low-memory VPS
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-scripts

# Run package discovery (safe, no DB needed)
RUN php artisan package:discover --ansi

# Create required Laravel storage directories
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    /var/log/supervisor

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy nginx and supervisor configs into image
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy and enable entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

---

## 2. `docker-compose.yml`

> Orchestrates 4 containers: `app`, `nginx`, `postgres`, `redis`.

```yaml
version: '3.9'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: laravel_app
    restart: unless-stopped
    working_dir: /var/www
    env_file:
      - .env
    depends_on:
      - postgres
      - redis
    networks:
      - laravel

  nginx:
    image: nginx:stable-alpine
    container_name: nginx
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - .:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - laravel

  postgres:
    image: postgres:16
    container_name: postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: ${DB_DATABASE}
      POSTGRES_USER: ${DB_USERNAME}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - laravel

  redis:
    image: redis:alpine
    container_name: redis
    restart: unless-stopped
    networks:
      - laravel

volumes:
  postgres_data:

networks:
  laravel:
```

---

## 3. `.dockerignore`

> Keeps the Docker image lean by excluding unnecessary files.

```
.git
vendor
node_modules
storage/logs
storage/framework/cache
storage/framework/sessions
storage/framework/views
.phpunit.result.cache
.editorconfig
.gitattributes
README.md
tests
```

> ⚠️ Do NOT add `.env` here — your `.env` must be copied into the image.

---

## 4. `docker/nginx/default.conf`

> Nginx config — proxies PHP requests to the app container on port 9000.

```nginx
server {
    listen 80;
    index index.php index.html;
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
    root /var/www/public;

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
        gzip_static on;
    }
}
```

---

## 5. `docker/supervisord.conf`

> Runs both `php-fpm` and `nginx` inside the single app container.

```ini
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=php-fpm -F
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
priority=5

[program:nginx]
command=nginx -g "daemon off;"
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
priority=10
```

---

## 6. `docker/entrypoint.sh`

> Runs automatically on container start — waits for DB, runs migrations, caches config.

```sh
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
            \$host = getenv('DB_HOST');
            \$port = getenv('DB_PORT') ?: '5432';
            \$db   = getenv('DB_DATABASE');
            \$user = getenv('DB_USERNAME');
            \$pass = getenv('DB_PASSWORD');
            \$dsn  = \"pgsql:host=\$host;port=\$port;dbname=\$db\";
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
```

> Make sure to make it executable: `chmod +x docker/entrypoint.sh`

---

## 7. `.env` — Required Values for Docker

Update your `.env` file with these values before deploying:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://YOUR_SERVER_IP_OR_DOMAIN

# ⚠️ MUST use service names, not 127.0.0.1
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

REDIS_HOST=redis
REDIS_PORT=6379
```

> ⚠️ `DB_HOST=postgres` and `REDIS_HOST=redis` — these are the **Docker service names**, not `127.0.0.1`. This is a very common mistake.

---

## 🚀 Deploying on Dokploy

### Step 1 — Push your code to GitHub
```bash
git add .
git commit -m "add docker deployment config"
git push origin main
```

### Step 2 — Create a new app in Dokploy
1. Open Dokploy → **"Create Application"**
2. Connect your GitHub repository
3. Set **Branch**: `main`
4. Set **Build Type**: `Docker Compose`
5. Set **Compose file path**: `./docker-compose.yml`

### Step 3 — Add environment variables
In Dokploy → **Environment** tab, paste all your `.env` values:
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_KEY_HERE
DB_HOST=postgres
DB_DATABASE=your-db-name
...
```

### Step 4 — Deploy
Click **Deploy** and watch the build logs. The first build takes ~3-5 minutes.

---

## 🔧 Common Errors & Fixes

| Error | Cause | Fix |
|-------|-------|-----|
| `open DockerFile: no such file or directory` | Platform expects `DockerFile` not `Dockerfile` | Change dockerfile name in Dokploy settings to `./Dockerfile` |
| `composer install exit code 2` — symfony requires php >=8.4 | `composer.lock` has Symfony 8.x which needs PHP 8.4+ | Use `FROM php:8.4-fpm` in Dockerfile |
| `composer install exit code 2` — artisan fails during build | `post-autoload-dump` runs `php artisan` but no DB available | Add `--no-scripts` flag to composer install |
| `502 Bad Gateway` | nginx can't reach php-fpm | Check `fastcgi_pass app:9000` matches the service name in docker-compose |
| `DB connection refused` | Wrong DB host in .env | Change `DB_HOST=127.0.0.1` → `DB_HOST=postgres` |
| `Redis connection refused` | Wrong Redis host in .env | Change `REDIS_HOST=127.0.0.1` → `REDIS_HOST=redis` |
| `No application encryption key has been specified` | `APP_KEY` missing | Add `APP_KEY=base64:...` to env variables in Dokploy |

---

## 📋 Checklist Before Deploying

- [ ] `Dockerfile` uses `php:8.4-fpm` (matches your `composer.lock` PHP requirements)
- [ ] `docker/nginx/default.conf` exists with `root /var/www/public`
- [ ] `docker/supervisord.conf` exists
- [ ] `docker/entrypoint.sh` exists and is executable
- [ ] `.env` has `DB_HOST=postgres` (not `127.0.0.1`)
- [ ] `.env` has `REDIS_HOST=redis` (not `127.0.0.1`)
- [ ] `.env` has `APP_ENV=production` and `APP_DEBUG=false`
- [ ] `.dockerignore` does NOT include `.env`
- [ ] Dokploy build type is set to `Docker Compose`
- [ ] Dokploy dockerfile path is `./Dockerfile` (lowercase `f`)
