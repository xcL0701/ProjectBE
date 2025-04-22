FROM php:8.2-fpm-alpine

RUN rm -rf /var/cache/apk/*
RUN apk update && apk upgrade

RUN apk add --no-cache libpng-dev
RUN apk add --no-cache libjpeg-dev
RUN apk add --no-cache libwebp-dev
RUN apk add --no-cache freetype-dev
RUN apk add --no-cache mysql-client

# Instal ekstensi PHP yang dibutuhkan
RUN docker-php-ext-install -j$(nproc) intl zip

# Konfigurasi dan instal ekstensi gd
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Salin kode aplikasi
COPY . /var/www/html

# Install dependency Composer
RUN composer install --no-dev --optimize-autoloader

# Generate key aplikasi jika belum ada
RUN php artisan key:generate --ansi

# Optimize aplikasi
RUN php artisan optimize:clear && php artisan optimize

# Set permission untuk storage dan bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 9000 untuk PHP-FPM
EXPOSE 9000

# Jalankan PHP-FPM
CMD ["php-fpm"]
