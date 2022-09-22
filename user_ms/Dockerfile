FROM php:8.1.0-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip\
    xvfb\
    libzip-dev\
    librdkafka-dev
# Clear cache
RUN pecl install rdkafka && docker-php-ext-enable rdkafka

RUN apt-get clean && rm -rf /var/lib/apt/lists/* 

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pcntl

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1000 -d /home/userms userms
RUN mkdir -p /home/userms/.composer && \
    chown -R userms:userms /home/userms &&\
    mkdir /var/virtualbank

# Set working directory
WORKDIR /var/virtualbank

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
RUN chown -R userms:userms /var/virtualbank

USER userms

CMD ["php","artisan","serve","--port=9200"]
