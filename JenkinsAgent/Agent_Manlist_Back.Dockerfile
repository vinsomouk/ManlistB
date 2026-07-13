FROM docker:27-cli AS docker-cli

FROM jenkins/inbound-agent:latest-jdk21

USER root

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        jq \
        postgresql-client \
        php-cli \
        php-curl \
        php-intl \
        php-mbstring \
        php-pgsql \
        php-xml \
        php-zip \
        php-xdebug && \
    rm -rf /var/lib/apt/lists/*

# Copier le client Docker officiel
COPY --from=docker-cli /usr/local/bin/docker /usr/local/bin/docker

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer

# Vérifications pendant le build
RUN docker --version && \
    php --version && \
    composer --version

USER jenkins

WORKDIR /home/jenkins/agent

ENV APP_ENV=test