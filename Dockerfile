FROM php:8.4-cli

RUN docker-php-ext-install pdo_mysql pcntl
RUN apt-get update && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
