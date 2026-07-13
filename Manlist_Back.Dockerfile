# Manlist_Back.Dockerfile
FROM php:8.2-apache

# Activer le module Apache rewrite
RUN a2enmod rewrite

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql intl zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier l'application
COPY . /var/www/html
WORKDIR /var/www/html

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Configurer Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Définir les permissions
RUN chown -R www-data:www-data var

# Variables d'environnement
ENV APP_ENV=prod
ENV APP_SECRET=your_secret_here
ENV DATABASE_URL=postgresql://db_user:db_password@db_host:5432/db_name

# Exposer le port
EXPOSE 80