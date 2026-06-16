#!/bin/bash
# Exit immediately if a command exits with a non-zero status
set -e

echo "=========================================================="
echo " Starting Wisata Penglipuran Deployment (Docker Compose) "
echo "=========================================================="

# 1. Check if .env file exists
if [ ! -f ".env" ]; then
    echo "Error: .env file is missing!"
    echo "Please copy .env.example to .env and configure your database and port settings."
    echo "Example:"
    echo "  cp .env.example .env"
    echo "  nano .env"
    exit 1
fi

# 1.5. Pull latest code from the active git branch
if [ -d ".git" ]; then
    CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "main")
    echo "Git repository detected. Pulling latest updates from branch: $CURRENT_BRANCH..."
    git pull origin "$CURRENT_BRANCH"
fi

# 2. Set up OpenRouteService map file (Bali OSM extract)
mkdir -p openrouteservice/config openrouteservice/graphs openrouteservice/files openrouteservice/logs

echo "Checking OpenRouteService map file (Bali OSM extract)..."
OSM_FILE="openrouteservice/files/bali.osm.pbf"
if [ ! -f "$OSM_FILE" ] || [ $(stat -c%s "$OSM_FILE" 2>/dev/null || echo 0) -lt 10000000 ]; then
    echo "Downloading Bali OSM extract (~12MB)..."
    rm -f "$OSM_FILE"
    curl -L -o "$OSM_FILE" https://download.openstreetmap.fr/extracts/asia/indonesia/bali-latest.osm.pbf
    echo "OSM download completed."
else
    echo "Bali OSM extract already exists and is valid."
fi

# 3. Pull/Build and start docker containers
echo "Starting Docker Compose services..."
docker compose up -d --build

# 4. Wait for MySQL to be ready
echo "Waiting for database connection to be established..."
DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_PASS=$(grep DB_PASSWORD .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")

until docker compose exec -e MYSQL_PWD="$DB_PASS" -T penglipuran-db mysqladmin ping -h"127.0.0.1" -u"$DB_USER" --silent; do
    echo -n "."
    sleep 2
done
echo ""
echo "Database is ready!"

# 5. Check and generate APP_KEY if empty
APP_KEY=$(grep '^APP_KEY=' .env | cut -d '=' -f 2)
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "null" ]; then
    echo "Generating Application Key..."
    docker compose exec -T penglipuran-app php artisan key:generate
fi

# 6. Run database migrations
echo "Running database migrations..."
docker compose exec -T penglipuran-app php artisan migrate --force

# 7. Check for seeding request
if [[ "$1" == "--seed" ]]; then
    echo "Seeding the database..."
    docker compose exec -T penglipuran-app php artisan db:seed --force
fi

# 8. Optimize Laravel
echo "Optimizing Laravel configurations and routes..."
docker compose exec -T penglipuran-app php artisan config:cache
docker compose exec -T penglipuran-app php artisan route:cache
docker compose exec -T penglipuran-app php artisan view:cache

echo "=========================================================="
echo " Deployment Successful!"
echo "=========================================================="
echo "Your application is running on port: $(grep APP_PORT .env | cut -d '=' -f2 || echo 80)"
echo "You can check container logs using:"
echo "  docker compose logs -f"
echo "=========================================================="
