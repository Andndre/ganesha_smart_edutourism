#!/bin/bash
set -e

echo "=========================================================="
echo " Starting Local Development Deployment (Docker) "
echo "=========================================================="

echo "Starting Docker Compose services..."
docker compose up -d --build

echo "Waiting for MySQL database..."
sleep 5

echo "Installing dependencies (including dev)..."
docker compose exec -T penglipuran-app composer install

echo "Linking storage..."
docker compose exec -T penglipuran-app php artisan storage:link || true

echo "Running database migrations and seeders..."
docker compose exec -T penglipuran-app php artisan migrate:fresh --seed

echo "=========================================================="
echo " Local Deployment Successful!"
echo "=========================================================="
echo "Now run 'composer docker:share' to expose the app via Cloudflare and start Vite."
