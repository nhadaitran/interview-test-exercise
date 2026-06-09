FROM php:8.3-fpm-alpine

# Install Dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy Projects
COPY . .

# Install PHP dependencies & Permissions for Laravel
RUN composer update --no-interaction --optimize-autoloader --no-dev || true
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Custom Configurations
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 9000

CMD ["php-fpm"]