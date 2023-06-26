# Start from PHP 8 FPM image
FROM php:8-fpm

# Install necessary packages for PHP and mPDF
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libxslt1-dev \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip xsl pdo_mysql mysqli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html
COPY . /var/www/html
RUN composer install

# Copy the application files to the container
COPY . /var/www/html

# Install PHP dependencies
RUN composer install

# Change owner of project directory to www-data (default PHP-FPM user)
RUN chown -R www-data:www-data /var/www/html

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
