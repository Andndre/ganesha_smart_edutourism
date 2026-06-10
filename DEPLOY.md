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
# 1. Port Web Aplikasi & phpMyAdmin
APP_PORT=80
PMA_PORT=8081

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

