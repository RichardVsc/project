FROM php:8.2-fpm-alpine

RUN apk update && apk add --no-cache \
    bash \
    libpq \
    libzip \
    zip \
    unzip \
    git \
    curl \
    postgresql-dev \
    autoconf \
    build-base \
    libtool \
    make \
    pcre-dev

RUN docker-php-ext-install pdo pdo_pgsql

RUN pecl install redis && docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV PATH="/usr/local/bin:$PATH"

RUN composer --version

WORKDIR /var/www/html
