#!/bin/sh

set -e

echo "Préparation du cache Symfony..."

php bin/console cache:clear \
    --env=prod \
    --no-debug

echo "Attente de PostgreSQL..."

attempt=1
max_attempts=15

until php bin/console doctrine:query:sql "SELECT 1" --env=prod >/dev/null 2>&1
do
    if [ "$attempt" -ge "$max_attempts" ]; then
        echo "Impossible de joindre PostgreSQL après ${max_attempts} tentatives."
        exit 1
    fi

    echo "PostgreSQL indisponible, nouvelle tentative ${attempt}/${max_attempts}..."
    attempt=$((attempt + 1))
    sleep 4
done

echo "PostgreSQL est disponible."

echo "Application des migrations..."

php bin/console doctrine:migrations:migrate \
    --env=prod \
    --no-interaction \
    --allow-no-migration

echo "Démarrage d'Apache..."

exec apache2-foreground