#!/bin/bash
set -e

# Buat direktori yang dibutuhkan
mkdir -p config graphs files logs

# Download OSM extract Bali terbaru langsung dari OSM.fr
echo "Downloading Bali OSM extract..."
# Bali PBF should be > 10MB. If file is missing or less than 10MB, download it.
if [ ! -f "files/bali.osm.pbf" ] || [ $(stat -c%s "files/bali.osm.pbf" 2>/dev/null || echo 0) -lt 10000000 ]; then
    rm -f files/bali.osm.pbf
    # Gunakan curl -L untuk mengikuti redirect
    curl -L -o files/bali.osm.pbf https://download.openstreetmap.fr/extracts/asia/indonesia/bali-latest.osm.pbf
else
    echo "Bali OSM extract already exists and is valid."
fi

# Jalankan OpenRouteService
echo "Starting OpenRouteService..."
docker compose down
docker compose up -d

echo "=========================================================="
echo "OpenRouteService is starting up. It may take a few minutes"
echo "to build the graphs for the first time."
echo "Check progress with: docker compose logs -f"
echo "Check health: curl http://localhost:8080/ors/v2/health"
echo "=========================================================="
