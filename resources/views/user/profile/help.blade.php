@extends('layouts.app')
@section('title', 'Bantuan')
@section('header_title', 'Bantuan')

@section('content')
    <div class="px-4 pb-24 pt-[calc(env(safe-area-inset-top)+6rem)]">
        <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="mb-1 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-green-50">
                    <svg class="h-7 w-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <h2 class="text-charcoal text-lg font-bold">Pusat Bantuan</h2>
                <p class="mt-1 text-xs text-gray-500">Pertanyaan yang sering diajukan</p>
            </div>
        </div>

        <div class="mt-4 space-y-2">
            @php
                $faqs = [
                    [
                        'q' => 'Apa itu Edutourism?',
                        'a' => 'Edutourism adalah fitur wisata edukasi yang mengajak Anda menjelajahi objek budaya dan sejarah di Desa Wisata Penglipuran melalui rute perjalanan interaktif. Setiap titik memiliki cerita dan kuis yang menambah wawasan Anda tentang budaya Bali.',
                    ],
                    [
                        'q' => 'Bagaimana cara memulai Edutourism?',
                        'a' => 'Buka halaman utama dan pilih menu "Edutourism". Pilih rute yang tersedia, lalu klik "Mulai". Ikuti petunjuk navigasi untuk mengunjungi setiap titik. Anda juga bisa memindai QR code yang tersedia di lokasi untuk memulai rute.',
                    ],
                    [
                        'q' => 'Bagaimana cara menggunakan fitur AR Scan?',
                        'a' => 'Ketuk ikon kamera AR di navigasi bawah. Arahkan kamera ke marker AR yang tersedia di objek wisata. Model 3D akan muncul di layar Anda. Untuk pengguna iOS, model akan terbuka otomatis dengan AR Quick Look.',
                    ],
                    [
                        'q' => 'Bagaimana cara melihat objek 3D di Android?',
                        'a' => 'Pastikan Anda telah menginstal browser terbaru (Chrome). Arahkan kamera ke marker AR, model 3D akan tampil langsung di layar. Geser layar untuk melihat objek dari berbagai sudut.',
                    ],
                    [
                        'q' => 'Bagaimana cara memesan paket wisata?',
                        'a' => 'Buka menu "Paket Wisata" di halaman utama. Pilih paket yang Anda inginkan, tentukan jadwal dan jumlah peserta, lalu lakukan pembayaran melalui Midtrans. Tiket elektronik akan dikirim ke email Anda setelah pembayaran berhasil.',
                    ],
                    [
                        'q' => 'Bagaimana cara melihat tiket saya?',
                        'a' => 'Semua tiket aktif Anda bisa dilihat di halaman "Tiket Saya" dari menu profil. Tiket juga dikirim ke email Anda dalam bentuk e-ticket.',
                    ],
                    [
                        'q' => 'Apa saja metode pembayaran yang tersedia?',
                        'a' => 'Kami menggunakan Midtrans yang mendukung berbagai metode pembayaran: transfer bank (BCA, Mandiri, BRI, BNI), kartu kredit, GoPay, OVO, Dana, LinkAja, dan Indomaret/Alfamart.',
                    ],
                    [
                        'q' => 'Bagaimana cara melihat riwayat kunjungan saya?',
                        'a' => 'Buka halaman profil, pilih menu "Riwayat Kunjungan". Semua tempat yang sudah Anda kunjungi melalui fitur Edutourism akan tercatat di sana. Anda juga bisa menandai tempat favorit dengan ikon bintang.',
                    ],
                    [
                        'q' => 'Apa itu fitur Favorit?',
                        'a' => 'Fitur Favorit memungkinkan Anda menyimpan tempat-tempat favorit dari riwayat kunjungan. Cukup ketuk ikon bintang di kartu tempat yang Anda sukai. Semua favorit bisa dilihat di halaman "Favorit Saya".',
                    ],
                    [
                        'q' => 'Bagaimana cara menggunakan peta interaktif?',
                        'a' => 'Pilih menu "Peta" dari halaman utama. Anda bisa melihat lokasi objek budaya, fasilitas umum, dan UMKM. Ketuk marker untuk melihat detail. Gunakan tombol rute untuk mendapatkan petunjuk arah.',
                    ],
                    [
                        'q' => 'Bagaimana cara berbelanja di UMKM?',
                        'a' => 'Buka menu "UMKM" untuk melihat produk-produk lokal dari masyarakat Penglipuran. Anda bisa menjelajahi berbagai kategori produk dan melihat profil UMKM. Untuk pembelian, hubungi langsung pemilik UMKM melalui kontak yang tersedia.',
                    ],
                    [
                        'q' => 'Bagaimana cara memberi ulasan?',
                        'a' => 'Setelah mengunjungi objek wisata, Anda akan mendapat notifikasi untuk memberi penilaian. Anda juga bisa memberi ulasan kapan saja melalui menu "Riwayat Penilaian & Ulasan" di halaman profil.',
                    ],
                    [
                        'q' => 'Apakah data saya aman?',
                        'a' => 'Kami menjaga keamanan data Anda dengan enkripsi dan tidak membagikan data pribadi Anda ke pihak ketiga tanpa izin. Pelajari lebih lanjut di halaman Kebijakan Privasi.',
                    ],
                    [
                        'q' => 'Bagaimana cara menghubungi pengelola?',
                        'a' => 'Anda bisa menghubungi pengelola Desa Wisata Penglipuran melalui informasi kontak yang tersedia di halaman utama. Untuk pertanyaan seputar aplikasi, silakan kirim masukan melalui menu "Riwayat Penilaian & Ulasan".',
                    ],
                ];
            @endphp

            @foreach ($faqs as $index => $faq)
                <div x-data="{ open: false }"
                    class="overflow-hidden rounded-2xl border border-gray-100 bg-white transition-shadow duration-200"
                    :class="open && 'shadow-sm'">
                    <button @click="open = !open"
                        class="flex w-full items-center justify-between p-4 text-left active:bg-gray-50"
                        :class="open && 'border-b border-gray-50'">
                        <span class="text-charcoal pr-4 text-sm font-medium leading-snug">{{ $faq['q'] }}</span>
                        <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200"
                            :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse.duration.200ms>
                        <div class="px-4 pb-4">
                            <p class="text-sm leading-relaxed text-gray-600">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('profile.edit') }}"
                class="text-primary inline-flex items-center gap-1 text-sm font-semibold">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Profil
            </a>
        </div>
    </div>
@endsection
