# Étape 1: Build des dépendances PHP
## Build stage: utiliser une image PHP Alpine pour pouvoir apk et docker-php-ext-*
FROM php:8.3-cli-alpine AS composer-build

WORKDIR /app

# Installer utilitaires et dépendances nécessaires pour compiler gd et installer les dépendances
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS curl git \
    && apk add --no-cache freetype-dev libpng-dev libjpeg-turbo-dev freetype libpng libjpeg-turbo \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Installer Composer (version compatible) et activer l'auto-discovery
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copier les fichiers de dépendances d'abord pour tirer parti du cache Docker
COPY composer.json composer.lock ./

# Installer les dépendances en mode production (composer.lock permet l'installation reproductible)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts --no-progress

# Supprimer les dépendances de build conservant les libs runtime
RUN apk del .build-deps || true

# Étape 2: Image finale pour l'application
FROM php:8.3-fpm-alpine

# Installer runtime libs, nginx et utilitaires pour le container final
RUN apk add --no-cache freetype libpng libjpeg-turbo postgresql-libs \
    nginx bash shadow curl gettext \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    freetype-dev libpng-dev libjpeg-turbo-dev postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql \
    && apk del .build-deps

# Créer un utilisateur non-root
RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les dépendances installées depuis l'étape de build
COPY --from=composer-build /app/vendor ./vendor
COPY --from=composer-build /usr/local/bin/composer /usr/local/bin/composer

# Copier la conf nginx adaptée pour Render (template) et le script d'entrée
COPY nginx.render.conf.template /etc/nginx/conf.d/default.conf.template
COPY docker-entrypoint-render.sh /usr/local/bin/docker-entrypoint-render.sh
RUN chmod +x /usr/local/bin/docker-entrypoint-render.sh

# Copier le reste du code de l'application
COPY . .

# Créer les répertoires nécessaires et définir les permissions
RUN mkdir -p storage/framework/{cache,data,sessions,testing,views} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R laravel:laravel /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Changer les permissions du fichier .env pour l'utilisateur laravel
RUN chown laravel:laravel .env

# Générer la clé d'application et optimiser
USER laravel
RUN php artisan key:generate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache
USER root
RUN php artisan passport:keys --force
# Copier le script d'entrée
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Exposer le port HTTP pour Render (nginx écoute 80)
EXPOSE 80

# Entrypoint pour Render : démarre php-fpm et nginx (template nginx.conf -> default.conf)
ENTRYPOINT ["/usr/local/bin/docker-entrypoint-render.sh"]
CMD [ "-g", "daemon off;"]