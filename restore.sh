#!/bin/bash
# Restore production database from a backup file created by backup.sh
# Usage: ./restore.sh storage/backups/backup_20260711_120000.sql.gz
set -e

if [ -z "$1" ]; then
    echo "Usage: ./restore.sh <path-to-backup.sql.gz>"
    exit 1
fi

if [ ! -f "$1" ]; then
    echo "Error: backup file '$1' not found!"
    exit 1
fi

if [ ! -f ".env" ]; then
    echo "Error: .env file is missing!"
    exit 1
fi

DB_PASS=$(grep DB_ROOT_PASSWORD .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")

read -p "This will OVERWRITE the current '$DB_NAME' database. Continue? [y/N] " CONFIRM
if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
    echo "Aborted."
    exit 1
fi

echo "Restoring '$1' into database '$DB_NAME' ..."
gunzip -c "$1" | docker compose exec -T -e MYSQL_PWD="$DB_PASS" penglipuran-db \
    mysql -u root "$DB_NAME"

echo "Restore complete."
