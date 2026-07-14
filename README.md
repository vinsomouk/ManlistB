# 🎬 Manlist Backend

![Symfony](https://img.shields.io/badge/Symfony-7-black?logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?logo=php)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-17-336791?logo=postgresql)
![Docker](https://img.shields.io/badge/Docker-2496ED?logo=docker&logoColor=white)
![Jenkins](https://img.shields.io/badge/Jenkins-D24939?logo=jenkins&logoColor=white)
![Render](https://img.shields.io/badge/Render-46E3B7)
![License](https://img.shields.io/badge/License-Educational-green)

---

# 📖 Présentation

Manlist est une plateforme permettant aux utilisateurs de gérer leur watchlist d'animés, de répondre à des questionnaires et d'obtenir des recommandations personnalisées.

Ce dépôt contient le **backend** développé avec **Symfony**.

L'application expose une API REST consommée par le frontend React.

---

# 🚀 Fonctionnalités

| Fonctionnalité | Statut |
|---------------|:------:|
| Authentification | ✅ |
| Inscription | ✅ |
| Déconnexion | ✅ |
| Vérification de session | ✅ |
| Gestion du profil | ✅ |
| Upload d'image de profil | ✅ |
| Watchlist | ✅ |
| Questionnaires | ✅ |
| Réponses utilisateur | ✅ |
| Recommandations | ✅ |
| API REST | ✅ |
| PostgreSQL | ✅ |

---

# 🏗 Architecture

```
                 React Frontend
                       │
                HTTP / REST API
                       │
                 Symfony Backend
                       │
                 Doctrine ORM
                       │
                  PostgreSQL
```

---

# 🛠 Technologies

- Symfony 7
- PHP 8.2
- Doctrine ORM
- PostgreSQL
- Composer
- Apache
- Docker
- Jenkins
- Docker Hub
- Render

---

# 📂 Structure du projet

```
Manlist-Back
│
├── bin/
├── config/
├── migrations/
├── public/
├── src/
│   ├── Controller/
│   ├── Entity/
│   ├── Repository/
│   ├── Security/
│   ├── Service/
│   ├── DataFixtures/
│   └── Command/
│
├── tests/
├── var/
├── vendor/
│
├── Jenkinsfile
├── Manlist_Back.Dockerfile
├── compose.yaml
└── composer.json
```

---

# ⚙ Installation

## Prérequis

- PHP 8.2+
- Composer
- PostgreSQL
- Docker (optionnel)

## Cloner le dépôt

```bash
git clone https://github.com/vinsomouk/ManlistB.git

cd Manlist-Back
```

## Installer les dépendances

```bash
composer install
```

## Configurer les variables d'environnement

Créer un fichier `.env.local` si nécessaire puis configurer :

```
APP_ENV=dev

APP_SECRET=

DATABASE_URL=

CORS_ALLOW_ORIGIN=
```

## Créer la base de données

```bash
php bin/console doctrine:database:create
```

## Exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate
```

## Lancer Symfony

```bash
symfony server:start
```

---

# 🐳 Docker

Construire l'image :

```bash
docker build \
-f Manlist_Back.Dockerfile \
-t manlist-back .
```

Lancer :

```bash
docker run \
-p 8000:80 \
manlist-back
```

---

# 🧪 Tests

Exécuter tous les tests :

```bash
php bin/phpunit
```

Résultat actuel :

```
7 tests
58 assertions

OK
```

---

# 🔒 Sécurité

Le projet intègre plusieurs niveaux de sécurité.

## Authentification

- Login Symfony
- Sessions sécurisées
- Cookies HttpOnly
- SameSite=None
- Secure Cookies

## Mots de passe

- Hash automatique Symfony

## CORS

Configuration avec NelmioCorsBundle.

Autorisation du frontend Render.

## Analyse des dépendances

Composer Audit :

```bash
composer audit
```

## Scan Docker

Analyse automatique avec Trivy dans Jenkins.

Les scans recherchent :

- vulnérabilités système
- dépendances
- bibliothèques PHP

---

# 🔄 CI/CD

Le pipeline Jenkins automatise :

```
Checkout

↓

Installation

↓

Validation Symfony

↓

Base PostgreSQL de test

↓

Migrations

↓

Tests PHPUnit

↓

Build Docker

↓

Scan Trivy

↓

Push Docker Hub
```

---

# 🐳 Docker Hub

Image publiée :

```
vmk700/manlist-back
```

Tags :

```
latest

BUILD_NUMBER
```

---

# ☁ Déploiement

Le backend est hébergé sur Render.

Au démarrage :

- préparation du cache Symfony
- attente de PostgreSQL
- exécution automatique des migrations
- démarrage Apache

---

# 🌍 Variables d'environnement

| Variable | Description |
|----------|-------------|
| APP_ENV | Environnement Symfony |
| APP_SECRET | Clé de chiffrement |
| DATABASE_URL | Connexion PostgreSQL |
| CORS_ALLOW_ORIGIN | URL du frontend |
| APP_DEBUG | Mode debug |

---

# 📌 Commandes utiles

Installation

```bash
composer install
```

Tests

```bash
php bin/phpunit
```

Migration

```bash
php bin/console doctrine:migrations:migrate
```

Cache

```bash
php bin/console cache:clear
```

Validation des services

```bash
php bin/console lint:container
```

---

# 🚀 Déploiement continu

Le projet utilise :

- GitHub
- Jenkins
- Docker
- Docker Hub
- Render

Architecture du pipeline :

```
Developer

↓

GitHub

↓

Webhook (compatible)

↓

Jenkins

↓

Tests

↓

Build Docker

↓

Trivy

↓

Docker Hub

↓

Render
```

---

# 📈 Évolutions possibles

- Authentification OAuth (Google, Discord)
- Notifications
- API AniList complète
- Cache Redis
- Pagination
- Tableau de bord administrateur
- Déploiement Kubernetes

---

# 👨‍💻 Auteur

Projet réalisé dans le cadre du **Titre Professionnel Concepteur Développeur d'Applications (CDA)**.

Développé par **Khalidou Diakité**.