#!/bin/sh
set -e

# Substitute PORT into nginx config template
if [ -f /etc/nginx/conf.d/default.conf.template ]; then
  echo "Rendering nginx config from template"
  envsubst '$PORT' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf
fi

# Ensure folders exist and permissions
mkdir -p /run/nginx /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html || true

# Start php-fpm in background (use php-fpm binary available in official images)
php-fpm -F &

# Wait a moment for php-fpm to start (simple probe)
sleep 1

# Exec nginx in foreground to keep container running
exec nginx -g 'daemon off;'
