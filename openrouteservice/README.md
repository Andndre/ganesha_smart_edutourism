# Self-Hosted OpenRouteService (ORS)

Aplikasi ini menggunakan instalasi lokal OpenRouteService untuk menghasilkan rute pejalan kaki yang akurat di dalam area Desa Penglipuran. ORS dipilih karena mesin routingnya (GraphHopper) lebih permisif terhadap gerbang yang ditandai dengan `access=permissive`, tidak seperti OSRM publik.

## Instalasi

1. Masuk ke direktori ini:
   ```bash
   cd openrouteservice
   ```

2. Jalankan script setup:
   ```bash
   bash setup.sh
   ```
   Script ini akan:
   - Membuat folder `graphs`, `files`, dan `logs`.
   - Mendownload data peta `.osm.pbf` Indonesia terbaru.
   - Memotong data khusus untuk pulau Bali.
   - Menjalankan container Docker.

3. Tunggu hingga ORS selesai melakukan *graph building*. Bisa dicek via `docker compose logs -f`.

4. Cek endpoint:
   ```bash
   curl http://localhost:8080/ors/v2/health
   # Harus me-return {"status": "ready"}
   ```

## Catatan Konfigurasi

- **RAM**: Alokasi RAM (XMX) adalah 1GB. Jika build gagal karena OOM, naikkan di `docker-compose.yml`.
- **`min_network_size`**: Diatur ke `20` di `ors-config.yml` agar jaringan jalan kecil di Penglipuran tidak dibuang.
- Hanya mengaktifkan profil `foot-walking`.
