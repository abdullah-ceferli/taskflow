FROM php:8.5-fpm-alpine

WORKDIR /var/www/taskflow

RUN apk add --no-cache icu-dev libzip-dev nodejs npm \
    && docker-php-ext-install bcmath intl opcache pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .

RUN composer install --no-dev --classmap-authoritative --no-interaction --prefer-dist \
    && npm ci \
    && npm run build \
    && npm cache clean --force \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

CMD ["php-fpm", "-F"]
