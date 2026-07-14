FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts


FROM php:8.2-apache

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libicu-dev \
        libzip-dev && \
    docker-php-ext-install \
        pdo_pgsql \
        intl \
        zip && \
    a2enmod rewrite headers && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .
COPY .env.prod .env

COPY --from=vendor /app/vendor ./vendor

# Configuration Apache dédiée à Symfony
COPY docker/apache-vhost.conf \
    /etc/apache2/sites-available/000-default.conf

# Script de démarrage : migrations puis Apache
COPY docker/entrypoint.sh \
    /usr/local/bin/manlist-entrypoint

RUN chmod +x /usr/local/bin/manlist-entrypoint && \
    mkdir -p var/cache var/log public/uploads && \
    chown -R www-data:www-data var public/uploads && \
    php bin/console cache:clear --env=prod --no-debug && \
    php bin/console assets:install public --env=prod --no-debug

EXPOSE 80

ENTRYPOINT ["manlist-entrypoint"]