FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader


FROM php:8.2-apache

ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN apt-get update && \
    apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libicu-dev \
    libzip-dev && \
    docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    zip \
    opcache && \
    rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers expires

COPY . /var/www/html

COPY --from=composer /app/vendor /var/www/html/vendor

WORKDIR /var/www/html

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf

RUN chown -R www-data:www-data var

EXPOSE 80

CMD ["apache2-foreground"]