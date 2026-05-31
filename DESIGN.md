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

Berbeda dengan aplikasi konservasi biasa yang penuh dengan gradasi hijau, aplikasi ini menggunakan warna dasar yang bersih dengan "Hijau Penglipuran" murni digunakan sebagai **warna aksi** (_Call-to-Action / CTA_).

| Kategori        | Nama Warna        | Kode Hex | Penggunaan Utama                                                                |
| :-------------- | :---------------- | :------- | :------------------------------------------------------------------------------ |
| **Brand / CTA** | Penglipuran Green | \#1E5128 | Tombol utama, _toggle_ aktif, ikon menu terpilih, _progress bar_.               |
| **Accent**      | Bali Gold         | \#D4AF37 | Label premium, bintang rating UMKM, elemen dekoratif (garis bawah/ikon khusus). |
| **Background**  | Clean Off-White   | \#FAF9F6 | Latar belakang kanvas aplikasi (_body background_).                             |
| **Surface**     | Solid White       | \#FFFFFF | Latar belakang _cards_, _bottom sheet_, dan _navbar_.                           |
| **Typography**  | Charcoal Dark     | \#191A19 | Teks judul (_Headline_) dan teks tubuh (_Body_) untuk kontras maksimal.         |
| **Utility**     | Alert Amber       | \#E65100 | Peringatan keramaian (_Heatmap/Crowd Warning_), rute darurat (SOS).             |

## **3\. Tipografi**

Sistem tipografi dirancang untuk keterbacaan instan (_glanceability_) di layar kecil.

- **Primary Font (UI & Data):** Plus Jakarta Sans atau Inter.
    - _Kenapa?_ Karakter hurufnya tegak, tinggi (_large x-height_), sangat mudah dibaca walau ukurannya kecil (seperti pada label bawah ikon).
    - _Penggunaan:_ Label navigasi, harga UMKM, deskripsi rute, status cuaca.
- **Secondary Font (Editorial/Budaya):** Playfair Display.
    - _Kenapa?_ Memberikan sentuhan klasik Bali tanpa membuat UI terlihat kuno.
    - _Penggunaan:_ **HANYA** digunakan untuk judul _Digital Storytelling_, _headline_ halaman budaya, dan nama-nama objek wisata.

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
- **Visual Toolbar:** Menggunakan desain minimalis dengan garis batas halus (`border-gray-200`) dan latar belakang netral (`bg-gray-50`). Tombol aksi memiliki feedback visual dengan transisi lembut; tombol aktif disorot dengan warna brand (*Penglipuran Green* super halus: `rgba(30, 81, 40, 0.1)`).
- **Prose Mirror Styling:** Konten editor dibatasi secara visual menggunakan elemen scroll dengan tinggi maksimal (`max-h-[300px]`) dan didesain ramah keterbacaan dengan spasi antar paragraf yang lega serta gaya list bullet/decimal yang rapi.
