#!/bin/bash
set -e

echo "=========================================================="
echo " Starting Local Development Deployment (Docker) "
echo "=========================================================="

# Set up trap to stop docker compose services when the script is stopped/exited
trap "echo ''; echo 'Stopping Docker compose services...'; docker compose down" EXIT INT TERM

echo "Starting Docker Compose services..."
docker compose up -d --build

echo "Waiting for MySQL database..."
sleep 5

echo "Installing dependencies (including dev)..."
docker compose exec -T penglipuran-app composer install

echo "Linking storage..."
docker compose exec -T penglipuran-app php artisan storage:link || true

echo "Running database migrations..."
docker compose exec -T penglipuran-app php artisan migrate --force

echo "Running database seeders..."
docker compose exec -T penglipuran-app php artisan db:seed --force

echo "=========================================================="
echo " Running Cloudflare Tunnel & Vite (docker:share)..."
echo "=========================================================="
composer docker:share
