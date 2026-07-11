#!/bin/bash
# Backup production database to storage/backups/ (gitignored)
set -e

if [ ! -f ".env" ]; then
    echo "Error: .env file is missing!"
    exit 1
fi

BACKUP_DIR="storage/backups"
mkdir -p "$BACKUP_DIR"

DB_PASS=$(grep DB_ROOT_PASSWORD .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")

OUT_FILE="$BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql.gz"

echo "Backing up database '$DB_NAME' to $OUT_FILE ..."
docker compose exec -T -e MYSQL_PWD="$DB_PASS" penglipuran-db \
    mysqldump -u root "$DB_NAME" | gzip > "$OUT_FILE"

echo "Done: $OUT_FILE"
