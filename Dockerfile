# Utiliser une image PHP officielle avec FPM
FROM php:8.2-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    nginx \
    && docker-php-ext-install zip intl mbstring

# Installer l'extension MongoDB
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /app

# Copier les fichiers de configuration
COPY composer.json composer.lock ./

# Installer les dépendances
RUN composer install --no-scripts --no-autoloader --no-dev

# Copier le reste du code
COPY . .

# Créer les dossiers nécessaires
RUN mkdir -p /var/log/nginx \
    && mkdir -p /var/cache/nginx \
    && mkdir -p var/cache \
    && mkdir -p var/log

# Permissions
RUN chown -R www-data:www-data var

# Finaliser Composer
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Configuration de PHP pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Exposer le port
EXPOSE 9000

# Commande de démarrage
CMD ["php-fpm"] 