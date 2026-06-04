FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libsqlite3-dev libpq-dev \
    && docker-php-ext-install pdo_sqlite pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Prepare writable dirs; do not run artisan at build (no .env in git, DB not ready).
# Set APP_KEY and DB_* on Render; key is applied at container start.
RUN mkdir -p database storage/app/public \
    storage/framework/cache/data storage/framework/sessions storage/framework/views \
    storage/logs bootstrap/cache \
    && touch database/database.sqlite \
    && cp .env.example .env \
    && chmod -R 775 storage bootstrap/cache database

EXPOSE 8000
