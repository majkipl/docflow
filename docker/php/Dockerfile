FROM php:8.2-fpm

# Instalacja zależności systemowych
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

# Czyszczenie cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalacja rozszerzeń PHP
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Instalacja i włączenie Redis
RUN pecl install redis && docker-php-ext-enable redis

# Pobranie najnowszego Composera
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html