# syntax=docker/dockerfile:1

# Dependencias de producción (sin herramientas de desarrollo).
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# pdo_mysql se instala en la imagen final; la imagen de Composer no lo trae.
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts \
    --ignore-platform-req=ext-pdo_mysql

# Base común: PHP 8.4 + Apache + pdo_mysql, docroot en public/.
FROM php:8.4-apache AS base
RUN docker-php-ext-install pdo_mysql \
    && a2enmod headers
COPY docker/ports.conf /etc/apache2/ports.conf
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
    && echo 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername
WORKDIR /var/www/app
# Railway inyecta PORT; 8080 es el valor local por defecto.
ENV PORT=8080
EXPOSE 8080
ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]

# Desarrollo: el código se monta con docker compose; incluye Composer.
FROM base AS dev
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/* \
    && cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Producción (target por defecto, el que construye Railway).
FROM base AS prod
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'expose_php = Off\n' > "$PHP_INI_DIR/conf.d/zz-app.ini"
COPY --from=vendor /app/vendor ./vendor
COPY bin ./bin
COPY database ./database
COPY public ./public
COPY src ./src
