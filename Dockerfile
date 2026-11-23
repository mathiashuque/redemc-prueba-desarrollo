# syntax=docker/dockerfile:1

# Versiones fijas: Dependabot propone las actualizaciones (.github/dependabot.yml).
# Dependencias de producción (sin herramientas de desarrollo).
FROM composer:2.11.2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# pdo_mysql se instala en la imagen final; la imagen de Composer no lo trae.
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts \
    --ignore-platform-req=ext-pdo_mysql

# Base común: PHP 8.4 + Apache + pdo_mysql, docroot en public/.
FROM php:8.5.0RC5-apache AS base
# apt-get upgrade toma los parches de seguridad de Debian publicados después de la imagen base.
RUN apt-get update \
    && apt-get upgrade -y --no-install-recommends \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && a2enmod headers
COPY docker/ports.conf /etc/apache2/ports.conf
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
    && echo 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && printf 'ServerTokens Prod\nServerSignature Off\nTraceEnable Off\n' > /etc/apache2/conf-available/zz-security.conf \
    && a2enconf zz-security
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
COPY --from=composer:2.11.2 /usr/bin/composer /usr/bin/composer

# Producción (target por defecto, el que construye Railway).
FROM base AS prod
# Límites ajustados a un formulario de tres campos y sin subida de archivos.
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf '%s\n' 'expose_php = Off' 'post_max_size = 16K' 'max_input_vars = 20' \
        'file_uploads = Off' 'allow_url_fopen = Off' > "$PHP_INI_DIR/conf.d/zz-app.ini" \
    && chown -R www-data:www-data /var/run/apache2 /var/lock/apache2 /var/log/apache2
COPY --from=vendor /app/vendor ./vendor
COPY bin ./bin
COPY database ./database
COPY public ./public
COPY src ./src
# Apache corre sin root: escucha en $PORT (> 1024) y el código queda de solo lectura para él.
USER www-data
