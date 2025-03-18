# Use an official PHP image as the base image
FROM php:8.1-fpm

# Set working directory inside the container
WORKDIR /var/www

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip git && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd pdo pdo_mysql

# Install Composer (PHP package manager)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the Laravel project files into the container
COPY . .

# Install PHP dependencies (using Composer)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set appropriate permissions for Laravel's storage and bootstrap/cache directories
RUN chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www/storage && \
    chmod -R 775 /var/www/bootstrap/cache

# Expose port 9000 (FPM will be listening on this port)
EXPOSE 9000

# Start PHP-FPM (FastCGI Process Manager)
CMD ["php-fpm"]
