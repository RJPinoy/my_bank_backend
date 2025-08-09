#!/bin/bash
set -e

# Wait for the database to be ready
echo "Waiting for database to be ready..."
until php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; do
  sleep 2
done

# Run database migrations
echo "Running Doctrine migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Start Apache
echo "Starting Apache..."
exec apache2-foreground