#!/usr/bin/env sh
set -eu

SCRIPT_DIR=$(CDPATH= cd -- "$(dirname "$0")" && pwd)

cd "$SCRIPT_DIR"

echo "Deteniendo y eliminando servicios Docker del proyecto..."
docker compose down -v --remove-orphans --rmi local

echo "Limpieza completada."