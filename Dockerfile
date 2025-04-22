FROM php:8.2-fpm-alpine

# Install ekstensi PHP yang dibutuhkan (sesuaikan dengan kebutuhan aplikasi Anda)
RUN docker-php-ext-install pdo pdo_mysql gd

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
