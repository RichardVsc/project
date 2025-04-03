FROM php:8.2-fpm-alpine

# Instalar dependências e extensões necessárias
RUN apk update && apk add --no-cache \
    bash \
    libpq \
    libzip \
    zip \
    unzip \
    git \
    curl \
    postgresql-dev

# Extensões PHP para o Laravel
RUN docker-php-ext-install pdo pdo_pgsql

# Instalar o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Adicionar o Composer ao PATH manualmente
ENV PATH="/usr/local/bin:$PATH"

# Verificar a instalação do Composer
RUN composer --version

WORKDIR /var/www/html
