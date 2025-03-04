FROM php:8.2-apache

# Enable needed PHP extensions
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    libcurl4-openssl-dev \
    libssl-dev \
    && docker-php-ext-install \
    intl \
    pdo_mysql \
    zip \
    sockets \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Enable Apache rewrite module
RUN a2enmod rewrite ssl headers

# Set correct DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/symfony/public

# Set working directory
WORKDIR /var/www/symfony

# Copy files into container
COPY . /var/www/symfony

# Update permissions (if needed)
RUN chown -R www-data:www-data /var/www/symfony

# Create self-signed certificate for development
RUN mkdir -p /etc/apache2/ssl && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/apache2/ssl/symfony.key \
    -out /etc/apache2/ssl/symfony.crt \
    -subj "/C=FR/ST=France/L=Paris/O=Zoo/OU=Org/CN=localhost"

# Set up Apache VirtualHost
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Apply DocumentRoot change to Apache default site config
RUN sed -ri -e 's!/var/www/html!/var/www/symfony/public!g' /etc/apache2/sites-available/*.conf

# Install composer (optional if not already in project)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install

# Permissions fix
RUN chown -R www-data:www-data /var/www/symfony/var

# Expose Apache port
EXPOSE 80