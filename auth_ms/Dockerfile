FROM php:8.0.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip\
    wkhtmltopdf\
    xvfb\
    libzip-dev
# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1000 -d /home/auth-ms auth-ms
RUN mkdir -p /home/auth-ms/.composer && \
    chown -R auth-ms:auth-ms /home/auth-ms &&\
    mkdir /var/tym

# Set working directory
WORKDIR /var/tym

COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

COPY . .
RUN composer dump-autoload
RUN chown -R www-data:www-data storage && \
    chown -R www-data:www-data vendor
RUN chmod -R 777 /var/tym/storage
USER auth-ms
