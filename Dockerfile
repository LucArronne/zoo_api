FROM php:8.3-fpm

RUN apt update && apt install -y \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    libfreetype6-dev \
    libonig-dev \
    libxslt1-dev \
    build-essential \
    unzip \
    git \
    nginx \
    libssl-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable opcache \
    && docker-php-ext-install zip \
    && docker-php-ext-install xsl 

RUN docker-php-ext-install  pdo_mysql \
    && docker-php-ext-enable pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/php/conf.d/* /usr/local/etc/php/conf.d/

COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www

COPY . .

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log && \
    chown -R www-data:www-data /var/www/html/var

RUN mkdir -p /var/www/html/public/uploads && \
    chown -R www-data:www-data /var/www/html/public/uploads && \
    chmod -R 775 /var/www/html/public/uploads

RUN composer install

EXPOSE 10000
CMD ["php-fpm"]