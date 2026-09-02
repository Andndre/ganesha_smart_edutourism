# Draft Konten Flyer — Ganesha Smart Edutourism

Copy deck siap tempel ke Canva/Figma. Semua angka & fitur diambil dari sistem yang sudah berjalan.
Format acuan: **A5 dua sisi** (atau trifold DL). Tiap `## PANEL` = satu blok visual.

---

## PANEL 1 — Cover (Sisi Depan)

**Eyebrow**
DESA WISATA PENGLIPURAN · BANGLI, BALI

**Judul Besar**
Ganesha Smart Edutourism

**Tagline (pilih satu)**

- Bukan brosur digital. Pemandu wisata yang jalan bareng Anda.
- Jelajahi. Pelajari. Rasakan — lewat satu genggaman.
- Satu aplikasi, seluruh cerita Penglipuran.

**Sub-tagline**
Platform web _smart & sustainable edutourism_ untuk desa wisata terbersih di dunia.
Tanpa install — cukup buka lewat browser HP.

**Badge kecil (chip)**
`Web App` · `Tanpa Install` · `Bahasa Indonesia / English` · `AR 3D` · `Peta Offline-Ready`

**CTA + QR**
Pindai untuk mencoba → [QR ke APP_URL]

---

## PANEL 2 — Masalah yang Dijawab

**Judul:** Wisata budaya sering berhenti di foto.

| Masalah | Yang kami lakukan |
| --- | --- |
| Pengunjung datang, foto, pulang — tanpa tahu makna di baliknya | Konten edukatif muncul otomatis saat tiba di titiknya |
| Cerita budaya hanya hidup kalau ada pemandu | Audio narasi + AR 3D jadi pemandu 24 jam |
| UMKM di ujung jalan jarang dikunjungi | Algoritma rotasi adil meratakan eksposur seluruh UMKM |
| Titik tertentu padat, titik lain sepi | Pemantauan kapasitas real-time + saran rute alternatif |
| Data kunjungan dicatat manual | Scan QR tiket OTA di gerbang → laporan otomatis |

---

## PANEL 3 — Cara Kerja (4 Fase)

**Judul:** Menemani dari sebelum, selama, sampai setelah kunjungan.

**① SEBELUM — Persiapan**
Buka aplikasi (login atau mode tamu) → lihat kalender event budaya, peta desa, dan fasilitas umum → beli tiket lewat OTA (mis. Traveloka).

**② DATANG — Gerbang**
Petugas memindai QR tiket Anda. Sistem mendeteksi tiket ganda secara otomatis, kunjungan tercatat dalam hitungan detik.

**③ DI LOKASI — Jelajah & AR**
Pilih rute edutourism → navigasi turn-by-turn menuntun langkah Anda → tiba di titik, konten terbuka:

- Arahkan kamera ke penanda → model 3D muncul di layar (AR)
- Dengarkan narasi audio sejarah, filosofi, dan nilai
- Selesaikan kuis untuk membuka titik berikutnya
- Sistem mengecek keramaian sebelum Anda melangkah ke titik selanjutnya

**④ SETELAH — UMKM & Umpan Balik**
Rekomendasi produk UMKM di sekitar Anda → rute multi-toko dalam satu perjalanan → beri rating & masukan untuk desa.

> **Kunci sistem:** rute terkunci progresif — titik berikutnya baru terbuka setelah Anda benar-benar sampai di titik saat ini. Belajar sambil berjalan, bukan sambil menggulir layar.

---

## PANEL 4 — Fitur untuk Pengunjung (grid ikon 4×2)

| Ikon | Fitur | Satu kalimat |
| --- | --- | --- |
| 🗺️ | **Peta Interaktif** | Pin objek budaya, UMKM, dan fasilitas; detail dibuka sebagai panel geser ala Google Maps. |
| 🧭 | **Navigasi Turn-by-Turn** | Petunjuk arah jalan kaki di dalam desa, ditenagai mesin rute mandiri (OpenRouteService). |
| 📱 | **AR Scan & Viewer** | Arahkan kamera ke penanda, model 3D benda budaya muncul langsung. Android & iOS. |
| 🏛️ | **Objek Budaya Digital** | Galeri, narasi audio, dan cerita berlapis: sejarah, filosofi, nilai. |
| 🎓 | **Smart Edutourism** | Rute berpemandu bertahap lengkap dengan misi & kuis per titik. |
| 🛍️ | **Katalog UMKM** | Produk lokal dengan rekomendasi berbasis lokasi dan rotasi yang adil. |
| 📅 | **Event Budaya** | Kalender acara desa; pengingat email otomatis H-1. |
| ❤️ | **Profil & Favorit** | Riwayat kunjungan dan daftar favorit tersimpan di akun Anda. |

**Pendukung:** dua bahasa (ID/EN) di seluruh konten · notifikasi push · login Google satu ketuk · info cuaca desa yang diperbarui berkala.

---

## PANEL 5 — Untuk Pengelola Desa & Petugas

**Judul:** Satu dasbor untuk mengelola seluruh desa.

- **Dasbor & Laporan** — ringkasan kunjungan, komposisi asal wisatawan (domestik/mancanegara), unduh laporan PDF.
- **Ticketing** — pemindaian QR tiket OTA di gerbang, deteksi tiket ganda, riwayat & statistik.
- **Manajemen Konten** — editor teks kaya untuk objek budaya, cerita, galeri, dan audio; impor massal lewat XLSX.
- **AR Manager** — unggah model 3D berukuran besar secara stabil (unggah berpotong), kelola penanda AR, tautkan ke titik peta.
- **Kapasitas Real-Time** — tentukan zona dengan batas aman; peringatan langsung saat zona mendekati penuh.
- **Manajemen UMKM** — kelola profil usaha, produk, kategori, dan akun pemilik.
- **Portal Pemilik UMKM** — pelaku usaha mengelola katalog dan lokasi tokonya sendiri.
- **Terjemahan Otomatis** — isi konten dua bahasa sekali kerja lewat mesin terjemahan lokal.

**5 peran pengguna, akses terpisah:** Tamu · Wisatawan · Petugas Tiket · Pemilik UMKM · Admin.

---

## PANEL 6 — Spesifikasi Teknis (blok monospace, boks abu)

**Arsitektur**
Aplikasi web _mobile-first_, berjalan di browser HP tanpa instalasi.

| Lapisan | Teknologi |
| --- | --- |
| Backend | Laravel 13 · PHP 8.4 |
| Frontend | TailwindCSS v4 · Alpine.js v3 · Vite |
| Basis data | MySQL |
| Cache & antrean | Redis (cache bertag + _stale-while-revalidate_) |
| Real-time | Laravel Reverb (WebSocket) + Laravel Echo |
| Autentikasi | Sesi + Google OAuth |
| AR | AR.js / A-Frame (WebAR), iOS AR Quick Look (USDZ) |
| Peta & rute | Leaflet + OpenRouteService (mandiri, tanpa biaya per-panggilan) |
| Unggah besar | TUS chunked upload |
| Dwibahasa | spatie/laravel-translatable (konten tersimpan per bahasa) |
| Infrastruktur | Docker |

**Kebutuhan pengguna:** HP Android/iOS dengan browser modern, kamera (untuk AR), dan GPS. Koneksi internet ringan — halaman dioptimalkan dengan cache berlapis.

---

## PANEL 7 — Yang Membedakan (3 kartu)

**⚖️ Distribusi Ekonomi yang Adil**
Rekomendasi UMKM tidak mengikuti siapa yang paling populer. Sistem mencatat kapan tiap usaha terakhir direkomendasikan dan berapa kali muncul, lalu merotasinya — agar warung di ujung gang punya peluang yang sama dengan yang di depan gerbang.

**☀️ Dirancang untuk Dipakai di Bawah Matahari**
Kontras tinggi, area sentuh minimal 44×44 px, tanpa animasi berputar yang membingungkan. Diuji untuk kondisi luar ruangan, bukan untuk layar kantor.

**📈 Keputusan Berbasis Data**
Setiap pemindaian tiket, kunjungan, dan masukan menjadi laporan yang bisa dipakai desa untuk mengatur jadwal, kapasitas, dan promosi.

---

## PANEL 8 — Penutup (Sisi Belakang Bawah)

**Judul:** Coba sekarang.

1. Pindai QR di samping — atau buka `APP_URL`
2. Masuk dengan Google, atau lanjut sebagai tamu
3. Pilih rute edutourism dan mulai berjalan

**Kontak**
Desa Wisata Penglipuran, Kubu, Bangli, Bali
[email] · [telepon] · [instagram]

**Footer kecil**
Dikembangkan oleh Tim Ganesha Smart Edutourism · [tahun]

---

# Varian Ringkas (kalau ruang flyer sempit)

**Headline:** Ganesha Smart Edutourism — Pemandu Digital Desa Wisata Penglipuran

**Isi tiga kolom:**

> **JELAJAHI**
> Peta interaktif, navigasi jalan kaki, dan info fasilitas seluruh desa dalam satu layar.

> **PELAJARI**
> Rute edutourism bertahap dengan narasi audio, model 3D AR, dan kuis di tiap titik.

> **DUKUNG**
> Katalog UMKM dengan rekomendasi bergilir yang adil, plus rute belanja multi-toko.

**Cara pakai:** Pindai QR → masuk atau lanjut sebagai tamu → pilih rute → jalan.

**Footer:** Web app · tanpa install · ID/EN · Android & iOS

---

# Catatan untuk Desainer

- Warna: Penglipuran Green `#1E5128` (utama/CTA) · Bali Gold `#D4AF37` (aksen) · Off-White `#FAF9F6` (latar) · Charcoal `#191A19` (teks) · Alert Amber `#E65100` (khusus blok kapasitas).
- Font: **Plus Jakarta Sans / Inter** untuk semua teks UI dan spesifikasi; **Playfair Display** hanya untuk judul bernuansa budaya (PANEL 1 judul besar, PANEL 3 subjudul fase).
- Panel 6 (spesifikasi) sebaiknya jadi blok terkecil dan paling padat — pembaca umum melewatinya, penilai teknis mencarinya.
- Sediakan ruang aman QR minimal 3×3 cm dan area kosong di sekelilingnya.
- Isi placeholder sebelum cetak: `APP_URL`, email, telepon, instagram, tahun.
