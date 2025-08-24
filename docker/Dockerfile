FROM composer:2 AS composer_base
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --no-progress || true

FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash icu-dev oniguruma-dev libzip-dev zip unzip git curl supervisor tzdata

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS

RUN docker-php-ext-configure intl \
 && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql opcache mbstring intl zip pcntl

RUN pecl install redis \
 && docker-php-ext-enable redis

RUN apk del .build-deps

RUN { \
  echo "opcache.enable=1"; \
  echo "opcache.validate_timestamps=1"; \
  echo "opcache.memory_consumption=192"; \
  echo "opcache.interned_strings_buffer=16"; \
  echo "opcache.max_accelerated_files=15000"; \
} > /usr/local/etc/php/conf.d/opcache.ini

ENV TZ=Africa/Cairo

WORKDIR /var/www/html

COPY . /var/www/html
COPY --from=composer_base /app/vendor /var/www/html/vendor

RUN addgroup -g 1000 laravel && adduser -G laravel -g "Laravel" -s /bin/sh -D -u 1000 laravel \
 && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
 && chown -R laravel:laravel /var/www/html

USER laravel

CMD ["php-fpm", "-F"]
