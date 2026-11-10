# **Panduan Desain UI/UX (Mobile-First)**

## **Proyek: Smart Sustainable Edutourism Desa Wisata Penglipuran**

Dokumen ini adalah _single source of truth_ untuk pedoman desain antarmuka aplikasi web Desa Wisata Penglipuran. Aplikasi ini mengadopsi gaya **"Super App / Modular Grid-Based Utility"** (terinspirasi dari Grab/Gojek) yang dioptimalkan sepenuhnya untuk penggunaan luar ruangan (_outdoor mobile-first_).

## **1\. Filosofi & Gaya Desain Utama**

Gaya desain difokuskan pada **Efisiensi Navigasi, _Progressive Disclosure_ (menyembunyikan kepadatan data), dan Aksesibilitas Tinggi**. Aplikasi harus terasa cepat, rapi, dan fungsional seperti alat bantu serbaguna (_utility tool_), bukan brosur digital yang lambat.

### **Karakteristik Visual Utama:**

- **Menu Bento-Grid (Atas):** Layar utama langsung menyajikan matriks ikon (4x2 atau 3x2) untuk akses instan ke fitur spesifik (Peta, UMKM, Kamera AR).
- **Card-Based Content (Bawah):** Informasi promo, _event_, atau rekomendasi rute dibungkus dalam kartu-kartu putih dengan bayangan tipis (_drop shadow_) yang bisa digulir ke bawah tanpa mengganggu grid menu utama.
- **Whitespace & Kontras:** Menghindari penggunaan foto ukuran penuh (_edge-to-edge_) sebagai latar belakang. Latar belakang aplikasi harus warna netral padat untuk memastikan kartu konten dan teks sangat kontras dan mudah dibaca di bawah terik matahari.

## **2\. Palet Warna (Color System)**

Sistem warna mengacu pada **Guideline Visual Desa Wisata Penglipuran (Bab 3: Sistem Warna)** dengan komposisi **60% Dominan – 30% Sekunder – 10% Aksen**, dipadukan dengan kanvas terang untuk keterbacaan luar ruangan (_outdoor mobile-first_).

> **Aturan Teknis:** Jangan pernah menulis kode hex secara _hardcode_ di file Blade. Selalu gunakan _utility class_ Tailwind v4 (`bg-primary`, `text-primary`, `bg-secondary`, `bg-accent`, `text-charcoal`, `bg-surface`) atau _CSS Custom Properties_ (`var(--color-primary)`, `var(--color-secondary)`, `var(--color-accent)`) yang didefinisikan secara terpusat di `resources/css/app.css`.

| Kategori                 | Nama Warna      | Kode Hex  | Token CSS / Tailwind               | Penggunaan Utama                                                                                  |
| :----------------------- | :-------------- | :-------- | :--------------------------------- | :------------------------------------------------------------------------------------------------ |
| **Primary (60% Brand)**  | Hijau Bambu     | `#293518` | `--color-primary` / `primary`      | Warna dominan identitas, tombol utama (CTA), _toggle_ aktif, ikon menu terpilih, _sidebar_ admin. |
| **Primary Hover**        | Deep Bamboo     | `#1B2410` | `--color-primary-hover`            | _Hover_ & _active state_ pada tombol utama.                                                       |
| **Secondary (30%)**      | Hijau Olive     | `#989A4B` | `--color-secondary` / `secondary`  | Warna sekunder pendukung, kategori upacara/budaya, _badge_ sekunder, aksen visual natural.        |
| **Accent (10%)**         | Bilah Bambu     | `#E3C994` | `--color-accent` / `accent`        | _Highlight_ hangat, _badge_ unggulan, _border_ dekoratif, aksen di atas latar Hijau Bambu.        |
| **Background (Canvas)**  | Clean Off-White | `#FAF9F6` | `--color-surface` / `surface`      | Latar belakang kanvas aplikasi (_body background_) agar kontras di bawah terik matahari.          |
| **Surface (Card)**       | Solid White     | `#FFFFFF` | `--color-card` / `white`           | Latar belakang _cards_, _bottom sheet_, dan _navbar_.                                             |
| **Typography / Dark**    | Abu Batu        | `#191A19` | `--color-charcoal` / `charcoal`    | Teks judul (_Headline_), teks tubuh (_Body_), dan elemen gelap untuk kontras maksimal.            |
| **Utility / Alert**      | Alert Amber     | `#E65100` | `--color-warning` / `warning`      | Peringatan keramaian (_Heatmap/Crowd Warning_), rute darurat (SOS).                               |

### **Aturan Kombinasi & Kontras Warna (Sesuai Guideline Bab 3.2–3.6):**

1. **Di atas latar Hijau Bambu (`#293518`):** Gunakan teks/ikon berwarna **Putih (`#FFFFFF`)**, **Bilah Bambu (`#E3C994`)**, atau **Hijau Olive (`#989A4B`)**.
2. **Di atas latar Hijau Olive (`#989A4B`):** Gunakan teks/ikon berwarna **Hijau Bambu (`#293518`)** atau **Putih (`#FFFFFF`)**.
3. **Di atas latar Bilah Bambu (`#E3C994`):** **WAJIB** menggunakan teks/ikon gelap **Abu Batu (`#191A19`)** atau **Hijau Bambu (`#293518`)**. Dilarang menggunakan teks putih di atas `#E3C994` karena rasio kontrasnya rendah (gagal standar keterbacaan WCAG).

## **3\. Tipografi**

Sistem tipografi mengacu pada **Guideline Visual Desa Wisata Penglipuran (Bab 4: Tipografi)** yang mengombinasikan karakter humanis-klasik untuk _headline_ dan keterbacaan bersih untuk _body text_.

- **Primary Font (Headline & Sub-Headline — `--font-display` / `font-display`):** **Libertinus Sans** (dibundel secara lokal melalui `@fontsource/libertinus-sans`).
    - _Karakter:_ Sans-serif humanis dengan proporsi klasik dan elegan yang merepresentasikan wibawa budaya desa adat tanpa mengorbankan keterbacaan di layar digital.
    - _Penggunaan:_
        - **Headline:** **Libertinus Sans Bold (700)** — skala referensi `35pt` (`--text-display: 2.1875rem`, `line-height: 1.15`). Digunakan untuk judul utama halaman, _hero banner_, dan judul objek budaya.
        - **Sub-Headline:** **Libertinus Sans Regular (400)** — skala referensi `24pt` (`1.5rem`, `line-height: 1.25`). Digunakan untuk sub-judul seksi dan pengantar naratif.
- **Secondary Font (UI, Body Text & Caption — `--font-sans` / `font-sans`):** **Inter**.
    - _Karakter:_ Karakter huruf tegak dengan _x-height_ besar, netral, dan sangat mudah dibaca pada ukuran kecil di layar ponsel.
    - _Penggunaan:_
        - **Body Text:** **Inter Regular (400)** — skala referensi `16pt` (`1rem`, `line-height: 1.5`). Digunakan untuk paragraf cerita budaya, deskripsi UMKM/rute, dan kontrol formulir.
        - **Caption & UI Label:** **Inter Light (300) / Regular (400)** — skala referensi `12pt` (`0.75rem`, `line-height: 1.4`). Digunakan untuk keterangan foto, label navigasi bawah, _badge_, dan metadata.

## **4\. Arsitektur Komponen UI Kunci**

### **A. Bottom Navigation Bar (Jangkar Utama)**

Mengadopsi pola Super App, navbar bawah memegang kendali global.

- **Warna Latar:** Putih solid dengan bayangan lembut ke atas (membatasi area konten tanpa garis _border_ yang kaku).
- **Ikon Aktif/Non-aktif:** Ikon menggunakan _inline_ SVG. Saat tidak aktif berwarna abu-abu muda (text-gray-400); saat aktif berubah menjadi Penglipuran Green dengan ketebalan garis (stroke) yang sedikit menebal.
- **Pengecualian Tombol AR:** Tombol "Kamera AR" terletak di tengah, diangkat/melayang sedikit lebih tinggi dari _navbar_ (efek _floating/cutout_), dan memiliki latar belakang Hijau solid untuk menandakan bahwa ini adalah fitur andalan.

### **B. Modul Kamera AR (Overlay)**

Saat tab AR ditekan, antarmuka berubah drastis:

- Pindah ke mode _fullscreen_ (height: 100dvh).
- **Glassmorphism HUD:** Semua teks petunjuk (_"Arahkan ke Marker"_) dan tombol keluar (X) diletakkan di dalam kapsul semi-transparan (backdrop-blur-md) agar tidak menutupi area pandang (_viewfinder_) kamera.

### **C. Bottom Sheet / Drawer**

**Haram hukumnya** membuka halaman baru untuk informasi detail yang singkat (misal: deskripsi patung budaya).

- Ketuk pin Peta \-\> Buka _Bottom Sheet_ (seperempat layar dari bawah).
- Pengguna bisa men-_swipe_ panel tersebut ke atas untuk menjadikannya setengah atau penuh layar, dan men-_swipe_ ke bawah untuk menutupnya. (Pola interaksi ala Google Maps).

## **5\. Aksesibilitas & Faktor Lapangan (_On-Site UX_)**

Aplikasi ini tidak dipakai di kamar yang nyaman, melainkan di jalanan desa wisata.

1. **Anti-Fatigue Layout:** Maksimal hanya ada **dua** warna mencolok dalam satu layar. Sisanya adalah ruang putih dan abu-abu.
2. **Fat-Finger Friendly:** Area sentuh minimum untuk semua ikon dan tombol (_tap target_) adalah 44px x 44px.
3. **Skeleton Loading:** Jangan gunakan _spinner loader_ bulat. Gunakan _Skeleton_ abu-abu yang berkedip pelan (_pulse_) saat menunggu koneksi memuat gambar UMKM/Peta, memberikan ilusi bahwa aplikasi memuat lebih cepat.
4. **Haptic Feedback:** Tombol aksi utama (seperti "Bayar Sekarang" atau saat Kamera AR berhasil mengenali marker) **wajib** memicu getaran pendek pada ponsel (Vibration API) untuk konfirmasi fisik di lingkungan yang mungkin berisik.

## **6\. Rich Text Editor (Markdown / TipTap)**

Untuk mempermudah penulisan deskripsi naratif objek budaya yang dinamis dan terstruktur, aplikasi mengintegrasikan editor **TipTap** (WYSIWYG/Markdown editor) yang elegan.

- **Integrasi ESM Tanpa Bundler:** TipTap diimpor menggunakan ES Modules langsung dari `esm.sh` dalam `<script type="module">` guna performa pemuatan yang cepat dan menghindari penambahan depedensi NPM lokal yang membengkak.
- **Visual Toolbar:** Menggunakan desain minimalis dengan garis batas halus (`border-gray-200`) dan latar belakang netral (`bg-gray-50`). Tombol aksi memiliki feedback visual dengan transisi lembut; tombol aktif disorot dengan warna brand (*Hijau Bambu* super halus: `color-mix(in srgb, var(--color-primary) 10%, transparent)`).
- **Prose Mirror Styling:** Konten editor dibatasi secara visual menggunakan elemen scroll dengan tinggi maksimal (`max-h-[300px]`) dan didesain ramah keterbacaan dengan spasi antar paragraf yang lega serta gaya list bullet/decimal yang rapi.
