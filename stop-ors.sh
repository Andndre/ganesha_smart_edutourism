#!/bin/bash
set -e

cd "$(dirname "${BASH_SOURCE[0]}")/openrouteservice"

echo "Stopping OpenRouteService..."
docker compose down
echo "OpenRouteService stopped."
