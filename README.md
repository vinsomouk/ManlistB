# Manlist - Plateforme de Watchlist Interactive pour Animés

Application Symfony permettant aux utilisateurs de gérer leur watchlist d'animés et de répondre à des questionnaires pour obtenir des recommandations personnalisées.

## 🚀 Démarrage rapide

### Prérequis
- **Docker et Docker Compose**
- **PHP 8.2+** (optionnel pour développement local)
- **Composer** (optionnel pour développement local)

### 1. Lancement avec Docker Compose

```bash
git clone https://github.com/votre-username/manlist-back.git
cd manlist-back

# Lancer tous les services
docker-compose up -d --build

# Initialiser la base de données
docker-compose exec php bin/console doctrine:migrations:migrate -n
docker-compose exec php bin/console doctrine:fixtures:load -n

# Accéder à l'application
http://localhost:8080

2. Commandes Docker utiles

# Accéder au conteneur PHP
docker-compose exec php 

# Voir les logs
docker-compose logs -f php

# Arrêter les conteneurs
docker-compose down

🧪 Tests
Lancer tous les tests

# Avec Docker
docker-compose exec php bin/phpunit

# Avec Docker (avec couverture de code)
docker-compose exec php XDEBUG_MODE=coverage bin/phpunit --coverage-html var/report

# Localement (sans Docker)
APP_ENV=test php bin/console doctrine:database:create
APP_ENV=test php bin/console doctrine:migrations:migrate -n
./bin/phpunit

Accéder au rapport de couverture
Après avoir lancé les tests avec couverture :

open var/report/index.html  # Sur Mac
xdg-open var/report/index.html  # Sur Linux

🐳 Images Docker
1. Application Backend (Manlist_Back)
Fichier : Dockerfile

Construire l'image :

docker build -f Dockerfile -t manlist-back .

Lancer l'image :

cd JenkinsAgent
docker build -f Dockerfile -t agent-manlist-back .

🔧 Pipeline Jenkins
Fichier : Jenkinsfile

Configuration requise
Plugins Jenkins :

Docker Pipeline
JUnit
HTML Publisher
Configuration :

# Créer un agent Jenkins
docker run -d --name jenkins-agent \
  -e JENKINS_URL=http://jenkins-host:8080 \
  -e JENKINS_AGENT_NAME=manlist-agent \
  agent-manlist-back

  💻 Développement local
Sans Docker

composer install
npm install

# Démarrer la base de données
docker run -d --name manlist-db -e POSTGRES_PASSWORD=password -p 5432:5432 postgres:15

# Configurer la base
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Lancer le serveur
symfony server:start -d

🤝 Contribution
Forker le dépôt
Créer une branche : git checkout -b feature/ma-fonctionnalite
Commiter les changements : git commit -am 'Ajout ma fonctionnalite'
Pusher : git push origin feature/ma-fonctionnalite
Créer une Pull Request