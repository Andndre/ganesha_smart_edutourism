# Panduan Deploy Wisata Penglipuran Menggunakan Docker Compose

Repository ini sudah dikonfigurasi agar bisa berjalan sepenuhnya menggunakan Docker Compose di VPS Anda. Seluruh stack (Laravel, Nginx, MySQL, dan OpenRouteService) akan berjalan dalam container yang saling terisolasi.

---

## Prasyarat di VPS Anda
Pastikan VPS Anda sudah terpasang:
1. **Docker** dan **Docker Compose** (Jika menggunakan Hostinger dengan template Docker, ini biasanya sudah langsung aktif).
2. **Git** (Untuk mengambil source code).

---

## Langkah-langkah Deployment

### 1. Masuk ke VPS
Gunakan terminal komputer Anda untuk SSH ke VPS, atau klik tombol **"Terminal"** di kanan atas halaman Docker Manager Hostinger Anda.
```bash
ssh root@ip_address_vps_anda
```

### 2. Clone Repository ke VPS
Clone project ini ke direktori pilihan Anda di VPS (misalnya `/var/www/wisata-penglipuran`):
```bash
git clone <URL_REPOSITORY_GIT_ANDA> /var/www/wisata-penglipuran
cd /var/www/wisata-penglipuran
```

### 3. Konfigurasi File `.env`
Salin file template `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Edit file `.env` menggunakan editor teks (misalnya `nano`):
```bash
nano .env
```
Sesuaikan beberapa konfigurasi penting berikut agar kompatibel dengan Docker:

```env
# 1. Port Web Aplikasi, phpMyAdmin, & WebSocket
APP_PORT=80
PMA_PORT=8082
REVERB_PORT=8081

# 2. Pengaturan Database (WAJIB menggunakan nama container db: penglipuran-db)
DB_CONNECTION=mysql
DB_HOST=penglipuran-db
DB_PORT=3306
DB_DATABASE=db_ganesha_smart_edutourism
DB_USERNAME=admin
DB_PASSWORD=masukkan_password_aman_di_sini
DB_ROOT_PASSWORD=masukkan_password_root_mysql_di_sini

# 3. URL OpenRouteService (Mengarah ke container ORS)
ORS_BASE_URL=http://penglipuran-ors:8082
```
*Catatan: Tekan `CTRL+O` lalu `Enter` untuk menyimpan di nano, dan `CTRL+X` untuk keluar.*

### 4. Jalankan Script Deployment Otomatis
Kami telah menyediakan script `deploy.sh` yang akan otomatis:
- Mendownload peta OSM regional Bali terbaru untuk rute pejalan kaki.
- Membuild images Docker untuk Laravel & Nginx.
- Menjalankan seluruh container.
- Menunggu database MySQL siap.
- Membuat Application Key (`APP_KEY`) baru jika belum ada.
- Menjalankan migrasi database.
- Melakukan optimasi cache Laravel (config, route, view).

Jalankan perintah ini:
```bash
./deploy.sh
```

> [!TIP]
> Jika Anda baru pertama kali deploy dan ingin langsung mengisi database dengan data seeders default (seperti user admin default, event, dll.), jalankan perintah dengan opsi `--seed`:
> ```bash
> ./deploy.sh --seed
> ```

---

## Cara Monitoring dan Troubleshooting

### Memeriksa Status Container
Anda bisa melihat status container yang sedang berjalan langsung dari GUI **Docker Manager Hostinger** atau lewat terminal VPS:
```bash
docker compose ps
```

### Melihat Log Aplikasi / Error
Jika ada masalah, periksa log container:
```bash
# Untuk semua container
docker compose logs -f

# Untuk container aplikasi Laravel saja
docker compose logs -f penglipuran-app

# Untuk container OpenRouteService saja
docker compose logs -f penglipuran-ors
```

### Masuk ke dalam Container (Tinker / Perintah Artisan)
Jika Anda ingin menjalankan perintah artisan secara manual:
```bash
docker compose exec penglipuran-app php artisan tinker
```

---

## Mengapa Konfigurasi Ini Aman & Praktis?
1. **Penyimpanan Data Terisolasi & Awet (Persistent)**: Database MySQL disimpan di volume `penglipuran-db-data` dan file upload disimpan di `penglipuran-storage`. Keduanya aman meskipun container Anda di-restart, dihapus, atau di-update.
2. **Mudah Diupdate**: Jika Anda membuat perubahan kode di lokal dan mem-push ke Git, Anda cukup melakukan `git pull` dan `./deploy.sh` di VPS untuk merilis versi terbaru.
3. **Multi-Project Ready**: Jika nanti Anda menambahkan project lain di VPS ini, Anda hanya perlu mengganti variabel `APP_PORT` (misalnya jadi `8081`) dan `DB_PORT` di `.env` project baru agar tidak terjadi tabrakan port.

> [!IMPORTANT]
> Pastikan port `8081` (WebSocket Reverb) terbuka di firewall VPS / Hostinger Anda agar fitur *real-time tracking* dapat berfungsi di internet publik!

---

## Alur Kerja (Workflow) Lengkap

Berikut adalah perbandingan alur kerja ketika Anda melakukan development (di komputer lokal) dan production (di VPS):

### A. Alur Kerja di Komputer Lokal (Development)

Di komputer lokal Anda, Anda **tidak harus** menjalankan seluruh aplikasi di dalam Docker. Anda bisa membukanya secara native agar proses koding terasa instan:

1. **Setup Awal (Hanya Sekali)**:
   - Clone repository dari GitHub: `git clone <repo-url>`
   - Masuk ke folder: `cd ganesha_smart_edutourism`
   - Copy file environment: `cp .env.example .env`
   - Edit `.env` lokal Anda (biasanya `DB_CONNECTION=mysql` ke database lokal Anda atau `DB_CONNECTION=sqlite`).
   - Install dependency PHP: `composer install`
   - Install dependency Node.js: `npm install`
   - Generate Key: `php artisan key:generate`
   - Jalankan migrasi dan seeder: `php artisan migrate --seed`
   - Download & jalankan OpenRouteService lokal menggunakan Docker: `bash start-ors.sh`

2. **Koding Sehari-hari**:
   - Jalankan server development: `npm run dev` (ini akan menjalankan `artisan serve` dan Vite compiler).
   - Tulis kode Anda, buat fitur baru, ubah file Blade/CSS/JS.
   - Perubahan akan langsung terlihat di `http://localhost:8000`.
   - Jalankan test cases sebelum push: `php artisan test`.

3. **Kirim Perubahan (Push ke GitHub)**:
   ```bash
   git add .
   git commit -m "feat: deskripsi perubahan"
   git push origin main
   ```

---

### B. Alur Kerja di VPS (Production)

Di VPS, semua dijalankan di dalam Docker Compose agar jalannya stabil dan tidak memerlukan instalasi PHP/Node/MySQL langsung di VPS host:

1. **Setup Awal / Rilis Pertama (Hanya Sekali)**:
   - SSH ke VPS: `ssh root@vps_ip_address`
   - Clone repository: `git clone <repo-url> /var/www/wisata-penglipuran`
   - Masuk ke folder: `cd /var/www/wisata-penglipuran`
   - Copy file environment: `cp .env.example .env`
   - Edit `.env` untuk server produksi menggunakan `nano .env` (pastikan `APP_PORT=80` dan `DB_HOST=penglipuran-db`).
   - Jalankan script deployment otomatis dengan seeding database:
     ```bash
     ./deploy.sh --seed
     ```

2. **Memperbarui Aplikasi (Update Code / Perubahan Baru)**:
   Setiap kali Anda selesai membuat fitur di komputer lokal dan telah mem-push ke GitHub, lakukan langkah berikut di VPS untuk merilis update:
   - Masuk ke folder project di VPS: `cd /var/www/wisata-penglipuran`
   - Tarik kode terbaru dari GitHub:
     ```bash
     git pull origin main
     ```
   - Jalankan kembali script deployment otomatis (tanpa `--seed` agar data lama tidak tertimpa):
     ```bash
     ./deploy.sh
     ```
     *Apa yang terjadi saat `./deploy.sh` dijalankan ulang?*
     - Docker akan mendeteksi perubahan file dan membuild ulang Docker image untuk aplikasi Anda (Vite otomatis memcompile ulang asset CSS & JS di dalam container).
     - Container `penglipuran-app` dan `penglipuran-queue` akan dimatikan sebentar lalu dijalankan kembali dengan image terbaru.
     - Database MySQL **tetap aman** karena datanya disimpan di volume persisten.
     - Migrasi database baru akan otomatis dijalankan jika ada penambahan tabel baru (`php artisan migrate --force`).
     - Cache Laravel akan dibersihkan dan dibuat ulang secara otomatis untuk mempercepat performa.

---

## C. Multi-Environment Deployment di Satu VPS (Production & Staging/Dev)

Jika Anda ingin menjalankan dua environment secara bersamaan di satu VPS (misalnya, Production menggunakan branch `main` dan Development/Testing menggunakan branch `dev`), Anda bisa melakukannya dengan sangat mudah tanpa adanya tabrakan port atau routing database.

Docker Compose akan secara otomatis memisahkan container, network, dan volume berdasarkan variabel `COMPOSE_PROJECT_NAME` yang Anda definisikan di dalam file `.env` masing-masing folder project.

### Langkah-langkah Setup:

1. **Clone repository ke folder berbeda di VPS**:
   - Folder Production (misal: `/var/www/wisata-penglipuran`):
     ```bash
     git clone <URL_REPO> /var/www/wisata-penglipuran
     cd /var/www/wisata-penglipuran
     git checkout main
     ```
   - Folder Development (misal: `/var/www/wisata-penglipuran-dev`):
     ```bash
     git clone <URL_REPO> /var/www/wisata-penglipuran-dev
     cd /var/www/wisata-penglipuran-dev
     git checkout dev
     ```

2. **Konfigurasi file `.env` masing-masing environment**:
   Salin `.env.example` ke `.env` di masing-masing folder dan sesuaikan variabel konfigurasi di bawah ini agar **tidak terjadi tabrakan port & subdomain**:

   #### A. File `.env` untuk Production:
   ```env
   # Identitas Project Compose (Menentukan prefix nama container & volume)
   COMPOSE_PROJECT_NAME=penglipuran-prod

   # Domain Utama & phpMyAdmin
   APP_DOMAIN=penglipuran.digowave.com
   PMA_DOMAIN=pma-penglipuran.digowave.com

   # Port Binding Host (Gunakan port default)
   REVERB_PORT=8081
   FORWARD_DB_PORT=3306
   ORS_PORT=8080

   # Database Settings
   DB_HOST=penglipuran-db
   DB_PORT=3306
   DB_DATABASE=db_ganesha_smart_edutourism
   DB_USERNAME=admin
   DB_PASSWORD=password_produksi_anda
   ```

   #### B. File `.env` untuk Development / Staging:
   ```env
   # Identitas Project Compose (PENTING: Harus berbeda dari production!)
   COMPOSE_PROJECT_NAME=penglipuran-dev

   # Subdomain Dev & phpMyAdmin Dev
   APP_DOMAIN=dev.penglipuran.digowave.com
   PMA_DOMAIN=pma-dev.penglipuran.digowave.com

   # Port Binding Host (PENTING: Gunakan port unik agar tidak bentrok di host VPS!)
   REVERB_PORT=8083
   FORWARD_DB_PORT=3308
   ORS_PORT=8085

   # Database Settings (Tetap arahkan ke 'penglipuran-db' karena diselesaikan via Docker DNS internal di internal network stack ini)
   DB_HOST=penglipuran-db
   DB_PORT=3306
   DB_DATABASE=db_ganesha_smart_edutourism_dev
   DB_USERNAME=admin
   DB_PASSWORD=password_dev_anda
   ```

3. **Jalankan Deployment**:
   Karena script `./deploy.sh` sudah mendukung auto-pull branch yang aktif, Anda hanya perlu menjalankan:
   - Di folder Production:
     ```bash
     ./deploy.sh --seed  # (untuk setup pertama kali) atau ./deploy.sh (update biasa)
     ```
   - Di folder Development:
     ```bash
     ./deploy.sh --seed  # (untuk setup pertama kali dengan data simulasi) atau ./deploy.sh (update biasa)
     ```

### Mengapa workflow ini aman & terisolasi?
- **Routing Domain**: Traefik secara dinamis akan meroute traffic `penglipuran.digowave.com` ke container `penglipuran-prod-app`, dan `dev.penglipuran.digowave.com` ke container `penglipuran-dev-app`.
- **WebSocket Reverb**: Masing-masing aplikasi menggunakan port Reverb yang berbeda pada host (8081 dan 8083), sehingga tracking real-time di kedua environment bisa aktif secara independen.
- **Database & Data**: Volume database yang dibuat akan terpisah secara otomatis oleh Docker menjadi `penglipuran-prod_penglipuran-db-data` and `penglipuran-dev_penglipuran-db-data`. Data testing Anda di dev tidak akan pernah mengganggu database production.

---

## Integrasi dengan Traefik Hostinger (HTTPS / SSL Otomatis)

Hostinger menyediakan template **Traefik** bawaan untuk mengelola HTTPS dan routing multi-project. Berikut cara menghubungkan project ini ke Traefik setelah Anda membeli domain:

### Langkah 1: Hubungkan Domain ke VPS
Di control panel domain Anda, arahkan DNS A Record ke IP VPS Anda:
- Host: `@` -> Value: `IP_VPS_ANDA`
- Host: `www` -> Value: `IP_VPS_ANDA`

### Langkah 2: Deploy Traefik di Hostinger
1. Masuk ke **Docker Manager Hostinger**.
2. Klik tombol **Deploy Traefik** (masukkan email ACME Anda untuk pemberitahuan sertifikat Let's Encrypt).
3. Setelah deploy, Traefik akan membuat network external bernama `traefik-proxy` yang berjalan di port 80 & 443 VPS Anda.

### Langkah 3: Konfigurasi File `docker-compose.yml`
Buka file `docker-compose.yml` di VPS Anda (`nano docker-compose.yml`):

1. **Matikan Port Fisik App & phpMyAdmin** (berikan tanda `#` di depannya):
   ```yaml
   # ports:
   #   - "${APP_PORT:-80}:80"
   ```
   *(Hal ini dilakukan agar port 80 tidak bentrok dengan Traefik yang berjalan di host).*

2. **Aktifkan Labels Traefik** (hapus tanda `#` di depannya) pada service `penglipuran-app`:
   ```yaml
   labels:
     - "traefik.enable=true"
     - "traefik.http.routers.penglipuran.rule=Host(`domain-anda.com`)"
     - "traefik.http.routers.penglipuran.entrypoints=websecure"
     - "traefik.http.routers.penglipuran.tls.certresolver=letsencrypt"
     - "traefik.http.services.penglipuran.loadbalancer.server.port=80"
   ```
   *(Ganti `domain-anda.com` dengan nama domain yang sudah Anda beli).*

*(Catatan: Karena Traefik bawaan Hostinger dikonfigurasi menggunakan mode network `host`, kita tidak perlu menambahkan network external tambahan seperti `traefik-proxy` ke dalam container. Traefik otomatis terhubung ke IP bridge container).*

### Langkah 4: Terapkan Perubahan
Jalankan kembali script deploy untuk merestart container dengan konfigurasi baru:
```bash
./deploy.sh
```
Traefik Hostinger akan otomatis mendeteksi perubahan ini, meminta sertifikat SSL Let's Encrypt untuk domain Anda, dan meroute trafik HTTPS dengan aman ke aplikasi Anda!
