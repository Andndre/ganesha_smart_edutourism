#!/bin/bash
# Exit immediately if a command exits with a non-zero status
set -e

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "----------------------------------------------------"
echo "Stopping OpenRouteService..."
echo "----------------------------------------------------"

cd "$PROJECT_ROOT/openrouteservice"
docker compose down

echo "----------------------------------------------------"
echo "OpenRouteService stopped successfully."
echo "----------------------------------------------------"
