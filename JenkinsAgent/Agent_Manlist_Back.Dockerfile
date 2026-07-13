# Agent_Manlist_Back.Dockerfile
FROM jenkins/inbound-agent:latest

# Installer les dépendances système
USER root
RUN apt-get update && apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release \
    software-properties-common

# Installer Docker
RUN curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
RUN echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/debian \
    $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
RUN apt-get update && apt-get install -y docker-ce docker-ce-cli containerd.io

# Installer PHP et extensions
RUN apt-get install -y \
    php8.2 \
    php8.2-cli \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-zip \
    php8.2-pdo \
    php8.2-pgsql \
    php8.2-intl \
    php-xdebug

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installer Node.js (pour les assets)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# Installer outils supplémentaires
RUN apt-get install -y \
    git \
    unzip \
    postgresql-client \
    jq

# Configurer l'utilisateur jenkins pour Docker
RUN usermod -aG docker jenkins

# Configurer Xdebug pour les tests
RUN echo "xdebug.mode=coverage" >> /etc/php/8.2/cli/conf.d/20-xdebug.ini

USER VMK