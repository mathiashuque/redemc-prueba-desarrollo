#!/bin/sh
set -eu

# Aplica database/schema.sql antes de levantar Apache. Si la base no está
# disponible, se avisa y Apache arranca igual: /api/health.php reportará el error.
if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
    if [ -f vendor/autoload.php ]; then
        php bin/migrate.php || echo "entrypoint: la migración falló; se continúa sin ella" >&2
    else
        echo "entrypoint: falta vendor/ (ejecutá 'make setup'); se omite la migración" >&2
    fi
fi

exec "$@"
