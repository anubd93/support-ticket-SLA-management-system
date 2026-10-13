FROM php:8.4-fpm-alpine

RUN apk add --no-cache bash git unzip icu-dev libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath intl zip pcntl

RUN { \
        echo 'upload_max_filesize = 25M'; \
        echo 'post_max_size = 30M'; \
        echo 'memory_limit = 256M'; \
        echo 'date.timezone = UTC'; \
    } > /usr/local/etc/php/conf.d/app.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
