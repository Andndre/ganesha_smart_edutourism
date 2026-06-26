# Prompt untuk Claude Code: Generate Dokumentasi Lengkap Docsify

Gunakan prompt ini di Claude Code untuk men-generate semua file dokumentasi Docsify.

---

## INSTRUKSI UNTUK CLAUDE CODE

Kamu diminta untuk **membuat dokumentasi lengkap** aplikasi **Ganesha Smart Edutourism (Desa Wisata Penglipuran)** menggunakan format Docsify. Semua file markdown harus ditulis ke folder `docs/`.

---

## KONTEKS APLIKASI

Aplikasi ini adalah platform pariwisata edukasi cerdas untuk Desa Wisata Penglipuran (Bali). Ada **5 role pengguna** dengan akses dan fitur berbeda:

1. **Guest (Tamu)** — Pengunjung tidak login
2. **User (Wisatawan Terdaftar)** — Login dengan email/Google
3. **Admin** — Pengelola sistem penuh (`/admin/*`)
4. **UMKM Owner (Pemilik Toko)** — Kelola produk & profil (`/owner/*`)
5. **Ticket Officer (Petugas Tiket)** — Scan QR, walk-in ticketing (`/staff/*`)

---

## DAFTAR FILE YANG HARUS DIBUAT

Buat **semua** file berikut di folder `docs/`:

### Struktur File

```
docs/
├── _sidebar.md
├── _navbar.md
├── _coverpage.md
├── index.html
├── README.md
├── panduan-tamu.md
├── panduan-wisatawan.md
├── panduan-admin.md
├── panduan-owner.md
├── panduan-petugas.md
├── fitur/
│   ├── peta-eksplorasi.md
│   ├── ar-scan.md
│   ├── edutourism.md
│   ├── umkm-katalog.md
│   ├── paket-wisata.md
│   ├── objek-budaya.md
│   ├── events.md
│   ├── feedback.md
│   └── profil-akun.md
├── admin/
│   ├── dashboard.md
│   ├── kapasitas-zona.md
│   ├── objek-budaya.md
│   ├── fasilitas.md
│   ├── umkm.md
│   ├── kategori-umkm.md
│   ├── owner-umkm.md
│   ├── petugas-tiket.md
│   ├── map-manager.md
│   ├── ar-manager.md
│   ├── event.md
│   ├── tour-route.md
│   ├── paket-wisata.md
│   ├── feedback.md
│   └── laporan.md
├── owner/
│   ├── dashboard.md
│   ├── profil-toko.md
│   ├── lokasi-toko.md
│   └── produk.md
├── petugas/
│   ├── ticketing.md
│   ├── scan-qr.md
│   ├── walk-in.md
│   └── check-in.md
└── faq.md
```

---

## PANDUAN PENULISAN SETIAP FILE

### docs/index.html

File ini adalah entry point Docsify. Gunakan konfigurasi berikut:

```html
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/docsify@4/lib/themes/vue.css" />
    <title>Dokumentasi - Ganesha Smart Edutourism</title>
  </head>
  <body>
    <div id="app"></div>
    <script>
      window.$docsify = {
        name: '🏛️ Penglipuran Edutourism',
        repo: '',
        loadSidebar: true,
        loadNavbar: true,
        coverpage: true,
        subMaxLevel: 3,
        search: 'auto',
        auto2top: true,
      }
    </script>
    <script src="//cdn.jsdelivr.net/npm/docsify@4"></script>
    <script src="//cdn.jsdelivr.net/npm/docsify/lib/plugins/search.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/docsify-copy-code/dist/docsify-copy-code.min.js"></script>
  </body>
</html>
```

---

### docs/README.md

Halaman utama dokumentasi. Tulis dalam Bahasa Indonesia. Harus mencakup:
- Apa itu Ganesha Smart Edutourism
- Siapa saja pengguna aplikasi ini (5 role)
- Link cepat ke masing-masing panduan role
- Teknologi yang digunakan (Laravel 13, TailwindCSS, AR.js, Midtrans)

---

### docs/_sidebar.md

Sidebar navigasi Docsify. Struktur:

```markdown
- **Panduan Per Role**
  - [🧑 Tamu (Guest)](panduan-tamu.md)
  - [🎒 Wisatawan Terdaftar](panduan-wisatawan.md)
  - [🔧 Admin](panduan-admin.md)
  - [🏪 Pemilik UMKM](panduan-owner.md)
  - [🎫 Petugas Tiket](panduan-petugas.md)

- **Fitur Umum**
  - [🗺️ Peta Eksplorasi](fitur/peta-eksplorasi.md)
  - [📷 AR Scan](fitur/ar-scan.md)
  - [🎓 Smart Edutourism](fitur/edutourism.md)
  - [🛍️ Katalog UMKM](fitur/umkm-katalog.md)
  - [📦 Paket Wisata](fitur/paket-wisata.md)
  - [🏺 Objek Budaya](fitur/objek-budaya.md)
  - [🎉 Event](fitur/events.md)
  - [💬 Feedback](fitur/feedback.md)
  - [👤 Profil Akun](fitur/profil-akun.md)

- **Admin Panel**
  - [📊 Dashboard](admin/dashboard.md)
  - [🏗️ Kapasitas Zona](admin/kapasitas-zona.md)
  - [🏺 Objek Budaya](admin/objek-budaya.md)
  - [🏢 Fasilitas](admin/fasilitas.md)
  - [🛍️ Manajemen UMKM](admin/umkm.md)
  - [📂 Kategori UMKM](admin/kategori-umkm.md)
  - [👥 Owner UMKM](admin/owner-umkm.md)
  - [🎫 Petugas Tiket](admin/petugas-tiket.md)
  - [🗺️ Map Manager](admin/map-manager.md)
  - [📷 AR Manager](admin/ar-manager.md)
  - [🎉 Event](admin/event.md)
  - [🚶 Tour Route](admin/tour-route.md)
  - [📦 Paket Wisata](admin/paket-wisata.md)
  - [💬 Feedback Admin](admin/feedback.md)
  - [📈 Laporan](admin/laporan.md)

- **Owner Panel**
  - [📊 Dashboard Owner](owner/dashboard.md)
  - [🏪 Profil Toko](owner/profil-toko.md)
  - [📍 Lokasi Toko](owner/lokasi-toko.md)
  - [📦 Produk](owner/produk.md)

- **Petugas Tiket**
  - [🎫 Ticketing](petugas/ticketing.md)
  - [📷 Scan QR](petugas/scan-qr.md)
  - [🚶 Walk-In](petugas/walk-in.md)
  - [✅ Check-In](petugas/check-in.md)

- [❓ FAQ](faq.md)
```

---

### docs/_coverpage.md

Cover page Docsify. Tampilkan:
- Nama aplikasi dengan emoji yang relevan
- Tagline menarik
- Badge versi
- Tombol "Mulai Baca" dan "App ↗"

---

### docs/_navbar.md

Navbar Docsify:
```markdown
- [🏠 App](https://penglipuran.digowave.com)
- Panduan
  - [Tamu](panduan-tamu.md)
  - [Wisatawan](panduan-wisatawan.md)
  - [Admin](panduan-admin.md)
  - [Owner UMKM](panduan-owner.md)
  - [Petugas Tiket](panduan-petugas.md)
```

---

## DETAIL ISI SETIAP FILE PANDUAN

### `docs/panduan-tamu.md` — Panduan untuk Tamu (Guest)

Tulis panduan lengkap untuk pengguna yang **tidak login**. Sertakan semua route yang bisa diakses tamu:

**Route yang bisa diakses tamu:**
- `GET /` — Halaman utama: bento grid fitur, cuaca, event terbaru
- `GET /explore` — Peta interaktif Leaflet: lihat objek budaya, UMKM, fasilitas
- `GET /ar-scan` — Kamera AR: arahkan ke marker untuk lihat model 3D
- `GET /ar/scan/{arMarkerId}` — Redirect dari QR code ke viewer
- `GET /ar/viewer/{arMarkerId}` — Viewer AR untuk objek spesifik
- `GET /umkm` — Daftar toko UMKM dengan filter kategori
- `GET /umkm/store/{id}` — Detail toko: produk, lokasi di peta, kontak
- `GET /cultural` — Daftar semua objek budaya
- `GET /cultural/{slug}` — Detail objek budaya: cerita, galeri, AR, quiz
- `GET /events` — Daftar event/acara di desa
- `GET /edutourism` — Daftar rute wisata edukasi
- `GET /edutourism/routes/{id}/preview` — Preview rute: peta + waypoint
- `GET /tour-packages` — Daftar paket wisata
- `GET /tour-package/{id}` — Detail paket: deskripsi, harga, fasilitas
- `GET /login` — Halaman login
- `GET /register` — Halaman registrasi
- `GET /auth/google` — Login dengan Google
- `GET /forgot-password` — Lupa password
- `GET /terms` — Syarat & ketentuan
- `GET /privacy` — Kebijakan privasi
- `GET /lang/{locale}` — Ganti bahasa (id/en)

Untuk **setiap fitur/route**, jelaskan:
1. Apa fungsinya
2. Langkah-langkah penggunaan (step by step)
3. Informasi apa yang tampil di halaman tersebut
4. Tips penggunaan (misal: gunakan AR di luar ruangan)

---

### `docs/panduan-wisatawan.md` — Panduan untuk Wisatawan Terdaftar (Auth User)

Tulis panduan untuk pengguna yang sudah **login**. Tambahkan semua fitur eksklusif setelah login:

**Route tambahan setelah login (selain semua route tamu):**
- `GET /feedback` — Form tulis feedback/ulasan
- `GET /feedback/user` — Daftar feedback saya
- `POST /feedback` — Submit feedback (form fields: judul, isi, rating, kategori)
- `GET /feedback/{id}` — Lihat detail feedback
- `GET /feedback/{id}/edit` — Edit feedback
- `GET /feedback/thank-you/{id}` — Halaman terima kasih
- `GET /tour-package/{id}/book` — Halaman checkout booking paket wisata
  - Form fields: nama pemesan, email, nomor HP, jumlah orang, tanggal kunjungan, catatan khusus
- `POST /tour-package/{id}/process` — Proses pembayaran Midtrans
- `GET /profile` — Halaman profil pengguna
- `GET /profile/edit` — Edit profil
  - Form fields: nama lengkap, nomor HP, preferensi bahasa (id/en)
- `PUT /profile` — Simpan perubahan profil
- `POST /profile/avatar` — Upload foto profil (maks 2MB, JPG/PNG)
- `DELETE /profile/avatar` — Hapus foto profil (kembali ke avatar default)
- `GET /profile/bookings` — Riwayat booking & e-tiket (status: pending/paid/checked-in/cancelled)
- `GET /profile/favorites` — Daftar UMKM & objek budaya yang difavoritkan
- `POST /favorites/toggle` — Toggle favorit (AJAX)
- `GET /profile/visited` — Objek budaya yang sudah dikunjungi selama edutourism
- `GET /profile/settings` — Pengaturan akun (sama dengan edit profil)
- `GET /profile/help` — Halaman bantuan
- `GET /edutourism/active` — Rute edutourism yang sedang aktif/berjalan
- `GET /edutourism/arrive/{pointId}` — Tiba di waypoint: tampilkan konten + kuis
- `POST /edutourism/quiz/{quizId}/submit` — Submit jawaban kuis (JSON: answer)
- `POST /edutourism/routes/{id}/start` — Mulai rute edutourism
- `POST /umkm/recommend` — Minta rekomendasi UMKM (body: lat, lng)
- `GET /umkm/recommended/{id}` — Lihat UMKM yang direkomendasikan
- `GET /umkm/multi-route` — Rute multi-UMKM
- `POST /logout` — Logout

Untuk setiap fitur, sertakan:
1. Penjelasan fitur
2. Langkah-langkah detail
3. **Tabel form fields** (jika ada form): nama field, tipe, apakah wajib, contoh nilai
4. Apa yang terjadi setelah submit/aksi
5. Pesan sukses/error yang mungkin muncul

---

### `docs/panduan-admin.md` — Panduan untuk Admin

Tulis panduan lengkap untuk **Admin** yang mengakses `/admin/*`. Sertakan semua fitur manajemen:

#### A. Dashboard Admin (`GET /admin/dashboard`)
- Statistik: total pengunjung, booking, UMKM, kapasitas zona
- Grafik aktivitas kunjungan harian/bulanan
- Alert kapasitas zona jika melebihi threshold
- Link cepat ke modul yang sering digunakan

#### B. Kapasitas Zona (`GET /admin/capacity`)
Route:
- `GET /admin/capacity` — Daftar zona kapasitas (peta + tabel)
- `POST /admin/capacity` — Buat zona baru
  - Form fields:
    | Field | Tipe | Wajib | Keterangan |
    |-------|------|-------|-----------|
    | Nama Zona | Text | ✅ | Contoh: "Area Parkir Utama" |
    | Koordinat Geofence | Polygon di peta | ✅ | Gambar area dengan klik-klik di peta |
    | Kapasitas Maksimum | Number | ✅ | Jumlah orang maksimal |
    | Threshold Peringatan | Percentage | ✅ | Misal: 80% → kirim alert |
    | Threshold Kritis | Percentage | ✅ | Misal: 95% → broadcast emergency |
- `PUT /admin/capacity/{id}/thresholds` — Update threshold (form: warning %, critical %)
- `DELETE /admin/capacity/{id}` — Hapus zona

Jelaskan: apa itu kapasitas zona, bagaimana sistem peringatan bekerja (Laravel Reverb broadcast), cara menggambar geofence di peta Leaflet.

#### C. Objek Budaya
Route: `POST /admin/cultural-objects`, `PUT /admin/cultural-objects/{id}`, `DELETE /admin/cultural-objects/{id}`
(Diakses via modal di halaman publik `/cultural` setelah login admin)

Form fields untuk buat/edit objek budaya:
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| Nama Objek | Text | ✅ | Nama resmi objek budaya |
| Slug | Text | ✅ | Auto-generate dari nama, bisa diedit |
| Deskripsi | Rich Text (TipTap) | ✅ | Mendukung teks, gambar, heading |
| Gambar Utama | File Upload | ✅ | JPG/PNG, maks 2MB |
| Galeri | Multiple File | ❌ | Hingga 10 foto |
| Koordinat Lokasi | Map Click | ✅ | Klik peta untuk set pin |
| AR Marker | Dropdown | ❌ | Pilih marker dari daftar |
| Audio Guide | File Upload | ❌ | MP3, maks 10MB |
| Status | Select | ✅ | draft / published |

Fitur tambahan:
- Upload gambar di editor TipTap: `POST /admin/cultural-objects/upload-image`
- Download template import Excel: `GET /admin/cultural-objects/import-template`
- Import massal: `POST /admin/cultural-objects/import` (upload file .xlsx)

#### D. Fasilitas
Route: `POST /admin/facilities`, `PUT /admin/facilities/{facility}`, `DELETE /admin/facilities/{facility}`

Form fields:
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| Nama Fasilitas | Text | ✅ | Contoh: "Toilet Area Timur" |
| Jenis | Select | ✅ | toilet / parkir / info / mushola / atm |
| Koordinat | Map Click | ✅ | Klik peta |
| Deskripsi | Textarea | ❌ | Info tambahan |
| Jam Operasional | Text | ❌ | Format: 08:00 - 17:00 |

#### E. Manajemen UMKM (`GET /admin/umkm`)
Produk UMKM:
- `POST /admin/umkm/products` — Tambah produk
  | Field | Tipe | Wajib |
  |-------|------|-------|
  | Nama Produk | Text | ✅ |
  | Deskripsi | Textarea | ✅ |
  | Harga | Number (IDR) | ✅ |
  | Gambar | File Upload | ✅ |
  | Kategori | Dropdown | ✅ |
  | UMKM Profile | Dropdown | ✅ |
  | Status | Select (aktif/nonaktif) | ✅ |

#### F. Profil UMKM
- `POST /admin/umkm/profiles` — Tambah profil toko
  | Field | Tipe | Wajib |
  |-------|------|-------|
  | Nama Toko | Text | ✅ |
  | Deskripsi | Textarea | ✅ |
  | Gambar Toko | File Upload | ✅ |
  | Jam Buka | Text | ✅ |
  | Nomor WhatsApp | Text | ✅ |
  | Link Instagram | URL | ❌ |
  | Koordinat | Map Click | ✅ |

#### G. Kategori UMKM (`GET /admin/umkm/categories`)
- `POST /admin/umkm/categories` — Form: nama kategori, deskripsi, ikon (emoji/gambar)
- `PUT /admin/umkm/categories/{id}`, `DELETE /admin/umkm/categories/{id}`

#### H. Owner UMKM (`GET /admin/umkm/owners`)
- `POST /admin/umkm/owners` — Buat akun owner
  | Field | Tipe | Wajib |
  |-------|------|-------|
  | Nama Lengkap | Text | ✅ |
  | Email | Email | ✅ |
  | Password | Password (min 8 karakter) | ✅ |
  | UMKM Profile | Dropdown (pilih toko yang dikelola) | ✅ |

#### I. Petugas Tiket (`GET /admin/ticket-officers`)
- `POST /admin/ticket-officers` — Buat akun petugas
  | Field | Tipe | Wajib |
  |-------|------|-------|
  | Nama Lengkap | Text | ✅ |
  | Email | Email | ✅ |
  | Password | Password (min 8 karakter) | ✅ |

#### J. Map Manager (`GET /admin/map-manager`)
- Peta interaktif Leaflet dengan semua lokasi (objek budaya, UMKM, fasilitas)
- Kelola pin: tambah, edit, hapus lokasi langsung dari peta
- Filter tampilan per jenis lokasi
- Preview perubahan sebelum disimpan

#### K. AR Manager (`GET /admin/ar-manager`)
- Daftar model AR yang tersedia
- `POST /admin/ar-manager/models` — Upload model AR
  | Field | Tipe | Wajib | Keterangan |
  |-------|------|-------|-----------|
  | Nama Model | Text | ✅ | Nama objek 3D |
  | File GLB | File Upload | ✅ | Android WebXR, maks 50MB |
  | File USDZ | File Upload | ✅ | iOS AR Quick Look, maks 50MB |
  | File PATT | File Upload | ✅ | Marker pattern untuk AR.js |
  | Thumbnail | File Upload | ❌ | Preview gambar model |
  | Objek Budaya | Dropdown | ❌ | Hubungkan ke objek budaya |

#### L. Event (`GET /admin/events`)
- `GET /admin/events/create` — Form buat event:
  | Field | Tipe | Wajib |
  |-------|------|-------|
  | Judul Event | Text | ✅ |
  | Deskripsi | Rich Text | ✅ |
  | Tanggal Mulai | Datetime | ✅ |
  | Tanggal Selesai | Datetime | ✅ |
  | Gambar Banner | File Upload | ✅ |
  | Lokasi | Text | ✅ |
  | Kapasitas | Number | ❌ |
  | Status | draft / published | ✅ |
- `GET /admin/events/{id}/edit` — Edit event
- `PUT /admin/events/{id}`, `DELETE /admin/events/{id}`

#### M. Tour Route (`GET /admin/tour-routes`)
- `GET /admin/tour-routes/create` — Form buat rute:
  | Field | Tipe | Wajib |
  |-------|------|-------|
  | Nama Rute | Text | ✅ |
  | Deskripsi | Textarea | ✅ |
  | Tingkat Kesulitan | Select (mudah/sedang/sulit) | ✅ |
  | Estimasi Durasi | Number (menit) | ✅ |
  | Status Aktif | Toggle | ✅ |
  | Waypoints | Dynamic list | ✅ |

  Untuk setiap waypoint:
  | Field | Tipe | Wajib |
  |-------|------|-------|
  | Nama Titik | Text | ✅ |
  | Koordinat | Map Click | ✅ |
  | Konten Edukasi | Rich Text | ✅ |
  | Audio Guide | File Upload | ❌ |
  | Kuis | Dynamic form (pertanyaan + pilihan) | ❌ |

- `PATCH /admin/tour-routes/{id}/toggle-active` — Aktif/nonaktifkan rute
- `GET /admin/tour-routes/{id}/edit`, `PUT /admin/tour-routes/{id}`, `DELETE /admin/tour-routes/{id}`

#### N. Paket Wisata (`GET /admin/packages`)
- `GET /admin/packages/create` — Form buat paket:
  | Field | Tipe | Wajib |
  |-------|------|-------|
  | Nama Paket | Text | ✅ |
  | Deskripsi | Rich Text | ✅ |
  | Harga per Orang | Number (IDR) | ✅ |
  | Gambar | File Upload | ✅ |
  | Durasi | Number (jam) | ✅ |
  | Kapasitas per Booking | Number | ✅ |
  | Fasilitas Termasuk | Checkboxes | ❌ |
  | Status | aktif / nonaktif | ✅ |

#### O. Feedback Admin (`GET /admin/feedback`)
- Tabel semua feedback wisatawan dengan filter status
- `POST /admin/feedback/{id}/reply` — Form: isi balasan
- `PATCH /admin/feedback/{id}/toggle-public` — Tampilkan/sembunyikan feedback di halaman publik
- `DELETE /admin/feedback/{id}` — Hapus feedback

#### P. Laporan (`GET /admin/reports`)
- Filter: rentang tanggal (from - to), jenis laporan (kunjungan/booking/pendapatan)
- Tampilan: tabel dan grafik
- `GET /admin/reports/download` — Download sebagai PDF
  - Query params: `?from=YYYY-MM-DD&to=YYYY-MM-DD&type=visits`

---

### `docs/panduan-owner.md` — Panduan untuk Pemilik UMKM

Route prefix: `/owner/*`

#### A. Dashboard (`GET /owner/dashboard`)
- Statistik toko: jumlah produk, tampilan profil, jumlah rekomendasi diterima
- Grafik produk terlaris

#### B. Profil Toko (`GET /owner/profile`)
Form edit profil: `PUT /owner/profile`
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| Nama Toko | Text | ✅ | Nama usaha Anda |
| Deskripsi | Textarea | ✅ | Cerita singkat tentang toko |
| Gambar Toko | File Upload | ❌ | JPG/PNG, maks 2MB |
| Jam Buka | Text | ✅ | Format: 08:00 - 17:00 |
| Nomor WhatsApp | Text | ✅ | Format: 0812-xxxx-xxxx |
| Link Instagram | URL | ❌ | Tanpa @, contoh: instagram.com/namatoko |

#### C. Lokasi Toko (`GET /owner/location`)
- Peta interaktif untuk set lokasi toko
- Klik pada peta untuk menempatkan pin
- `PUT /owner/location` — Simpan koordinat
- Penting: lokasi akurat mempengaruhi rekomendasi berbasis jarak kepada wisatawan

#### D. Produk (`GET /owner/products`)
- Daftar semua produk toko dengan gambar, harga, status
- `POST /owner/products` — Form tambah produk:
  | Field | Tipe | Wajib | Keterangan |
  |-------|------|-------|-----------|
  | Nama Produk | Text | ✅ | Nama jelas & deskriptif |
  | Deskripsi | Textarea | ✅ | Bahan, kegunaan, keunikan |
  | Harga | Number (IDR) | ✅ | Harga satuan |
  | Gambar | File Upload | ✅ | JPG/PNG, maks 2MB |
  | Kategori | Dropdown | ✅ | Pilih dari kategori yang tersedia |
  | Tersedia | Toggle | ✅ | Aktif = tampil di katalog |
- `PUT /owner/products/{id}` — Edit produk
- `DELETE /owner/products/{id}` — Hapus produk (konfirmasi diperlukan)

---

### `docs/panduan-petugas.md` — Panduan untuk Petugas Tiket

Route prefix: `/staff/*`

#### A. Dashboard Ticketing (`GET /staff/ticketing`)
- Tampilkan reservasi hari ini dengan status real-time
- Filter: Semua / Pending Pembayaran / Sudah Check-In / Dibatalkan
- Statistik harian: total tamu, pendapatan hari ini
- Aksi cepat: Scan QR, Walk-In baru

#### B. Statistik Real-Time (`GET /staff/ticketing/stats`)
- Jumlah pengunjung aktif di area (dari visitor log)
- Status kapasitas zona (hijau/kuning/merah)
- Update otomatis setiap 30 detik via WebSocket (Laravel Reverb)

#### C. Walk-In Ticketing (`POST /staff/ticketing/walk-in`)
Proses tamu tanpa reservasi online:
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| Nama Tamu | Text | ✅ | Nama kepala rombongan |
| Nomor HP | Text | ✅ | Untuk kirim link tiket |
| Jumlah Orang | Number | ✅ | Total anggota rombongan |
| Paket Wisata | Dropdown | ✅ | Pilih paket yang tersedia |
| Metode Pembayaran | Select | ✅ | Tunai / Transfer / QRIS |

Setelah submit:
1. Sistem membuat reservasi baru
2. Kirim SMS/WA berisi link tiket ke nomor HP tamu
3. Tamu bisa akses tiket via: `GET /guest-access/{reservation}/{hash}`
4. Jika metode QRIS: tampilkan QR Midtrans di layar

#### D. Scan QR (`GET /staff/ticketing/scan`)
- Kamera aktif untuk scan QR code di tiket tamu
- `POST /staff/ticketing/verify` — Verifikasi QR
  - Input: data QR code
  - Response sukses: nama tamu, paket, tanggal booking, status, jumlah orang
  - Response gagal: "Tiket tidak valid" / "Tiket sudah digunakan" / "Tiket expired"

#### E. Check-In (`POST /staff/ticketing/check-in/{reservation}`)
- Setelah QR terverifikasi, tombol "Check-In" aktif
- Tekan tombol untuk konfirmasi kedatangan tamu
- Sistem update status reservasi → "checked_in"
- Catat waktu dan lokasi entry di VisitorLog
- Tampilkan konfirmasi sukses

#### F. Sinkronisasi Status (`POST /staff/ticketing/sync/{reservation}`)
- Gunakan jika status pembayaran tidak update otomatis dari Midtrans
- Sistem query ulang status transaksi ke Midtrans API
- Update status reservasi sesuai hasil

#### G. Pembayaran di Counter (`POST /staff/ticketing/pay/{reservation}`)
- Generate link pembayaran Midtrans Snap untuk tamu yang belum bayar
- Tampilkan QR code / URL pembayaran
- Tamu bisa bayar langsung via HP mereka

#### H. Batalkan Reservasi (`POST /staff/ticketing/cancel/{reservation}`)
- Input: alasan pembatalan (wajib)
- Sistem ubah status reservasi → "cancelled"
- Proses refund otomatis jika sudah bayar (sesuai kebijakan)

---

### File Fitur (`docs/fitur/*.md`)

**`fitur/peta-eksplorasi.md`:**
- Cara buka peta (`/explore`)
- Tombol filter: semua / budaya / fasilitas / UMKM
- Klik pin untuk lihat detail dalam popup
- Rute ke lokasi: tap "Navigasi" → pilih moda transport → mulai navigasi turn-by-turn
- Pencarian lokasi di peta
- GPS tracking posisi saat ini

**`fitur/ar-scan.md`:**
- Persyaratan: HP dengan kamera, izinkan akses kamera, koneksi internet
- Dua cara scan:
  1. Buka `/ar-scan` dan arahkan kamera ke marker fisik
  2. Scan QR code di papan informasi → redirect ke `/ar/scan/{id}`
- Model 3D muncul di atas marker (teknologi AR.js + A-Frame)
- iOS khusus: model USDZ membuka AR Quick Look native Apple
- Android: WebXR di browser
- Tips:
  - Pastikan marker bersih dan tidak tertutup
  - Pencahayaan cukup (hindari silau langsung)
  - Jarak optimal 30-50cm
  - Gunakan Chrome/Safari terbaru

**`fitur/edutourism.md`:**
- Apa itu Smart Edutourism (rute wisata edukatif dengan kuis & AR)
- Cara mulai (harus login):
  1. Buka `/edutourism` → pilih rute
  2. Preview peta dan informasi rute
  3. Tap "Mulai Rute" → `POST /edutourism/routes/{id}/start`
  4. Ikuti navigasi ke waypoint pertama
  5. Saat tiba: buka `/edutourism/arrive/{pointId}`
  6. Baca konten edukasi, dengarkan audio guide
  7. Jawab kuis: `POST /edutourism/quiz/{quizId}/submit`
  8. Lanjut ke waypoint berikutnya
- Tracking progress di `/edutourism/active`
- Selesaikan semua waypoint → dapatkan sertifikat digital

**`fitur/umkm-katalog.md`:**
- Browse UMKM (`/umkm`): grid toko dengan gambar, nama, kategori
- Filter per kategori produk
- Sistem rekomendasi fair: algoritma rotasi memastikan semua UMKM mendapat eksposur setara
- Cara minta rekomendasi berbasis lokasi (login diperlukan):
  1. Aktifkan GPS
  2. Tap "Rekomendasi Terdekat"
  3. Sistem tampilkan 3-5 UMKM terdekat yang belum lama direkomendasikan
- Detail toko: produk, lokasi di peta, jam buka, kontak WhatsApp
- Rute multi-UMKM: kunjungi beberapa toko dalam satu rute optimal

**`fitur/paket-wisata.md`:**
- Browse paket (`/tour-packages`)
- Detail paket: harga, durasi, fasilitas yang termasuk, deskripsi lengkap
- Proses booking step-by-step (harus login):
  1. Pilih paket → "Pesan Sekarang"
  2. Isi form booking (lihat tabel form fields)
  3. Review pesanan dan harga total
  4. Pilih metode pembayaran
  5. Selesaikan via Midtrans Snap (popup)
  6. Tunggu konfirmasi email (1-5 menit)
  7. Tunjukkan e-tiket di pintu masuk

**`fitur/objek-budaya.md`:**
- Browse semua objek (`/cultural`): grid dengan filter
- Halaman detail: cerita budaya panjang, galeri foto, peta lokasi
- Tombol AR: arahkan ke marker untuk lihat model 3D
- Audio guide: dengarkan narasi sambil berjalan
- Tandai sebagai dikunjungi (selama sesi edutourism)
- Simpan ke favorit (login diperlukan)
- Bagikan ke media sosial (tombol share)

**`fitur/events.md`:**
- Lihat daftar event (`/events`): kartu event dengan tanggal, lokasi
- Detail event: deskripsi lengkap, waktu, kapasitas tersisa
- Tambahkan ke kalender Google/Apple: tombol "Add to Calendar" (format .ics)
- Notifikasi event (jika sudah login): reminder 1 hari sebelum event via email

**`fitur/feedback.md`:**
- Cara tulis feedback (login diperlukan): akses `/feedback`
- Form fields:
  | Field | Tipe | Wajib | Keterangan |
  |-------|------|-------|-----------|
  | Judul | Text | ✅ | Ringkasan pengalaman |
  | Kategori | Select | ✅ | layanan / fasilitas / umkm / budaya / lainnya |
  | Rating | Stars (1-5) | ✅ | Tap bintang untuk nilai |
  | Isi Ulasan | Textarea | ✅ | Ceritakan pengalaman detail |
- Lihat daftar feedback saya: `/feedback/user`
- Status feedback: menunggu review / publik / private
- Admin bisa membalas → notifikasi muncul di akun Anda
- Edit feedback yang belum dijawab: `/feedback/{id}/edit`

**`fitur/profil-akun.md`:**
- Cara daftar (email atau Google):
  - Email: isi nama, email, password (min 8 karakter) di `/register`
  - Google: tap "Login dengan Google" di `/login` atau `/register`
- Edit profil (`/profile/edit`): nama, nomor HP, bahasa
- Ganti avatar: upload foto baru (JPG/PNG maks 2MB) atau hapus
- Tab Riwayat Booking: lihat semua pemesanan, status, download e-tiket
- Tab Favorit: daftar UMKM dan objek budaya yang disimpan
- Tab Sudah Dikunjungi: jejak perjalanan edutourism
- Ganti bahasa: pilihan Indonesia / English (tersimpan di akun)
- Hapus akun (hubungi admin melalui feedback)

---

### `docs/faq.md` — FAQ (Frequently Asked Questions)

Buat minimal 20 pertanyaan & jawaban yang relevan dan lengkap:

**Grup 1: Umum**
- Apa itu Ganesha Smart Edutourism?
- Apakah aplikasi ini gratis?
- Bahasa apa saja yang didukung?
- Apakah perlu koneksi internet?

**Grup 2: Registrasi & Login**
- Bagaimana cara mendaftar?
- Saya lupa password, bagaimana cara reset?
- Bisakah login dengan Google?
- Bisakah masuk tanpa daftar (sebagai tamu)?

**Grup 3: AR & 3D**
- Apa persyaratan untuk menggunakan fitur AR?
- Marker AR tidak terdeteksi, apa yang harus dilakukan?
- Browser apa yang didukung untuk AR?
- Apakah AR bisa digunakan di dalam ruangan?

**Grup 4: Booking Paket Wisata**
- Bagaimana cara memesan paket wisata?
- Metode pembayaran apa yang tersedia?
- Apakah e-tiket bisa digunakan untuk lebih dari satu orang?
- Bagaimana jika pembayaran gagal?
- Apakah ada refund jika saya batalkan?

**Grup 5: UMKM**
- Bagaimana cara menghubungi toko UMKM?
- Apa itu sistem rekomendasi UMKM?
- Apakah saya bisa membeli produk secara online?

**Grup 6: Smart Edutourism**
- Apakah harus login untuk mengikuti rute edutourism?
- Bagaimana jika saya keluar dari rute di tengah jalan?
- Apakah kuis bisa diulang?

**Grup 7: Teknis**
- Mengapa lokasi saya tidak terdeteksi?
- Aplikasi terasa lambat, apa yang bisa dilakukan?
- Bagaimana cara menghubungi tim support?

---

## ATURAN PENULISAN

1. **Bahasa**: Gunakan Bahasa Indonesia yang ramah dan mudah dipahami
2. **Format**: Gunakan heading `##` dan `###`, bullet points, numbered lists untuk step-by-step
3. **Tips & Catatan**: Gunakan blockquote `> **💡 Tips:**` untuk tips penting
4. **Peringatan**: Gunakan `> **⚠️ Perhatian:**` untuk peringatan
5. **Kode/URL**: Gunakan backtick untuk URL dan technical terms
6. **Emoji**: Gunakan emoji secara konsisten untuk navigasi visual
7. **Screenshot placeholder**: Tambahkan `![Nama Screenshot](screenshots/nama-file.png)` sebagai placeholder di tempat yang membutuhkan screenshot
8. **Cross-reference**: Berikan link antar halaman yang relevan menggunakan format `[teks link](nama-file.md)`
9. **Tabel form fields**: Gunakan tabel markdown untuk semua form

---

## CONTOH FORMAT STEP-BY-STEP

```markdown
## Cara Memesan Paket Wisata

### Langkah-langkah

1. **Buka halaman Paket Wisata**
   - Dari menu bawah, tap ikon 🎒
   - Atau akses langsung: `penglipuran.digowave.com/tour-packages`

2. **Pilih paket yang diinginkan**
   - Browse daftar paket yang tersedia
   - Tap kartu paket untuk lihat detail lengkap

3. **Isi form pemesanan**

   | Field | Keterangan | Wajib |
   |-------|-----------|-------|
   | Nama Pemesan | Nama lengkap sesuai KTP | ✅ |
   | Email | Untuk pengiriman e-tiket | ✅ |
   | Nomor HP | Format: 08xx-xxxx-xxxx | ✅ |
   | Jumlah Orang | Min 1, Maks sesuai kapasitas | ✅ |
   | Tanggal Kunjungan | Pilih dari date picker | ✅ |
   | Catatan Khusus | Permintaan diet, aksesibilitas, dll | ❌ |

4. **Pilih metode pembayaran**
   - Transfer Bank (BCA, Mandiri, BNI, BRI)
   - Kartu Kredit/Debit
   - GoPay / OVO / DANA
   - QRIS

5. **Konfirmasi & Bayar**
   - Review ringkasan pesanan
   - Tap **Bayar Sekarang**
   - Selesaikan pembayaran dalam 15 menit

> **💡 Tips:** E-tiket akan dikirim ke email dalam 1-5 menit setelah pembayaran berhasil.

> **⚠️ Perhatian:** Pembayaran yang tidak diselesaikan dalam 15 menit akan dibatalkan otomatis.
```

---

## CATATAN TAMBAHAN UNTUK CLAUDE CODE

- Baca file `CLAUDE.md` di root proyek untuk memahami arsitektur lengkap
- Baca file `routes/web.php` untuk daftar route lengkap
- Lihat folder `resources/views/` untuk memahami tampilan setiap halaman
- Lihat folder `app/Http/Controllers/` untuk memahami logika bisnis
- Pastikan setiap screenshot placeholder menggunakan path `screenshots/nama-screenshot.png`
- Folder `docs/screenshots/` sudah ada di repo
- Setiap file harus **lengkap dan detail**, bukan hanya outline

---

## PERINTAH SETUP DOCSIFY

Setelah semua file dibuat, jalankan:

```bash
# Install docsify CLI (jika belum ada)
npm i docsify-cli -g

# Preview lokal
docsify serve docs

# Akses di: http://localhost:3000
```

---

**Mulai generate sekarang. Buat semua file secara lengkap dan detail. Jangan tinggalkan placeholder kosong kecuali untuk screenshot.**
