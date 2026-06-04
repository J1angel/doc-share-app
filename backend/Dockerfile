FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

RUN touch database/database.sqlite \
    && php artisan key:generate --force \
    && chmod -R 775 storage bootstrap/cache database

EXPOSE 8000
