FROM jenkins/inbound-agent:latest-jdk21

USER root

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    docker.io \
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

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer

RUN usermod -aG docker jenkins

USER jenkins

WORKDIR /home/jenkins/agent

ENV APP_ENV=test