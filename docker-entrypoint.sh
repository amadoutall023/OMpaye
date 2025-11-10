#!/bin/sh

# Attendre que la base de données soit prête
echo "Waiting for database to be ready..."
while ! pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do
  echo "Database is unavailable - sleeping"
  sleep 1
done

echo "Database is up - executing migrations"
php artisan migrate --force

# Générer les clés Passport si elles n'existent pas
if [ ! -f storage/oauth-private.key ]; then
    echo "Generating Passport keys..."
    php artisan passport:install --force
fi

# Vider le cache
echo "Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Générer la documentation Swagger
echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

echo "Starting Laravel application..."
exec "$@"