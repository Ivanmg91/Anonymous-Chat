#!/usr/bin/env sh
set -eu

SCRIPT_DIR=$(CDPATH= cd -- "$(dirname "$0")" && pwd)
SQL_FILE="$SCRIPT_DIR/../sql/database.sql"
UPLOADS_DIR="$SCRIPT_DIR/../frontend/uploads"

if [ ! -f "$SCRIPT_DIR/.env" ]; then
    cp "$SCRIPT_DIR/.env.example" "$SCRIPT_DIR/.env"
    echo "Creado docker/.env a partir de docker/.env.example"
fi

. "$SCRIPT_DIR/.env"

echo "Deteniendo contenedores y borrando volumenes de datos..."
cd "$SCRIPT_DIR"
docker compose down -v --remove-orphans

echo "Levantando el entorno de nuevo..."
docker compose up --build -d --force-recreate

echo "Esperando a que MySQL acepte conexiones..."
until docker compose exec -T db mysqladmin ping -h 127.0.0.1 -uroot -p"$MYSQL_ROOT_PASSWORD" --silent >/dev/null 2>&1; do
    sleep 2
done

echo "Reaplicando esquema y datos iniciales..."
docker compose exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" < "$SQL_FILE"

echo "Eliminando archivos subidos..."
if [ -d "$UPLOADS_DIR" ]; then
    docker compose exec -T php sh -c 'find /var/www/html/frontend/uploads -mindepth 1 -maxdepth 1 -type f -delete'
fi

echo "Reset completado. Estado limpio disponible en:"
echo "- App: http://localhost:8080"
echo "- phpMyAdmin: http://localhost:8081"
