<?php

namespace App\Services;

use Illuminate\Support\Arr;

class TourPackageCatalog
{
    /**
     * @return array<int, array{
     *     id: string,
     *     title: string,
     *     price: string,
     *     raw_price: int,
     *     unit: string,
     *     duration: string,
     *     badge: string,
     *     badge_color: string,
     *     image: string,
     *     tagline: string,
     *     highlights: list<string>,
     *     inclusions: list<string>,
     *     rundown: list<array{time: string, title: string, desc: string}>
     * }>
     */
    public function all(): array
    {
        return [
            [
                'id' => 'edu-living',
                'title' => 'Edu Cultural Living Experience',
                'price' => 'Rp 350.000',
                'raw_price' => 350000,
                'unit' => 'per orang (Min. 2 Pax)',
                'duration' => '7 Jam',
                'badge' => 'PALING LENGKAP • 7 JAM',
                'badge_color' => 'bg-amber-500 text-white',
                'image' => 'images/packages/living-experience.jpeg',
                'tagline' => 'Tinggal, berinteraksi, dan beraktivitas langsung bersama keluarga warga adat Penglipuran.',
                'highlights' => [
                    'Workshop Tari Bali & Pembuatan Canang Sari',
                    'Praktik Masak Kuliner Otentik di Dapur Paon',
                    'Sesi Foto Lengkap Berbusana Adat Madya Bali',
                    'Konservasi & Edukasi Ekosistem Hutan Bambu',
                ],
                'inclusions' => [
                    'Termasuk Tiket Masuk Gerbang Desa Resmi & Hutan Bambu',
                    'Pemandu Adat Warga Penglipuran (Akreditasi Lokal)',
                    'Makan Siang Tradisional Bersama Keluarga di Dapur Paon',
                    'Perlengkapan Canang Sari & Kostum Adat Lengkap',
                    'Donasi Pelestarian Lingkungan Pura & Hutan Bambu',
                ],
                'rundown' => [
                    ['time' => '08.30 – 10.00 WITA', 'title' => 'Penyambutan Adat & Praktik Canang Sari', 'desc' => 'Penyambutan hangat di Tugu Pahlawan oleh tetua adat dengan welcome drink Loloh Cemcem, dilanjutkan workshop membuat canang sari di Bale Banjar.'],
                    ['time' => '10.00 – 12.30 WITA', 'title' => 'Living House & Memasak di Dapur Paon', 'desc' => 'Tamu tinggal di salah satu pekarangan warga, berinteraksi langsung dengan keluarga tuan rumah, dan belajar memasak kuliner tradisional di Dapur Paon.'],
                    ['time' => '12.30 – 13.30 WITA', 'title' => 'Makan Siang & Bali Costume Experience', 'desc' => 'Menikmati makan siang bersama keluarga tuan rumah dilanjutkan pemakaian busana adat madya Bali lengkap dengan sesi dokumentasi di pelataran natah.'],
                    ['time' => '13.30 – 14.30 WITA', 'title' => 'Pura Dukuh: Pengenalan Filosofi Kesucian', 'desc' => 'Interpretasi zona Utama Mandala desa, pemaparan konsep Tri Hita Karana, dan meditasi hening singkat di kawasan suci Pura Dukuh.'],
                    ['time' => '14.30 – 16.30 WITA', 'title' => 'Bamboo Forest Walk & Tugu Kalpataru', 'desc' => 'Menelusuri 45 hektar hutan bambu konservasi desa Penglipuran, mengenal jenis bambu langka, dan mempelajari sejarah penghargaan lingkungan Kalpataru.'],
                ],
            ],
            [
                'id' => 'edu-arsitektur',
                'title' => 'Edukasi Arsitektur & Bambu Lestari',
                'price' => 'Rp 175.000',
                'raw_price' => 175000,
                'unit' => 'per orang (Min. 2 Pax)',
                'duration' => '3.5 Jam',
                'badge' => 'STUDI ARSITEKTUR • 3.5 JAM',
                'badge_color' => 'bg-blue-600 text-white',
                'image' => 'images/packages/arsitektur-bambu.webp',
                'tagline' => 'Bedah anatomi arsitektur tradisional Bali dan konservasi bambu terencana.',
                'highlights' => [
                    'Kosmologi Tata Ruang Sanga Mandala',
                    'Bedah Struktur 76 Angkul-Angkul Tradisional',
                    'Workshop Kerajinan Anyaman Bambu Desa',
                    'Eksplorasi Bale Meten & Sirap Bambu Otentik',
                ],
                'inclusions' => [
                    'Termasuk Tiket Masuk Gerbang Desa Resmi & Hutan Bambu',
                    'Pemandu Ahli Arsitektur Tradisional Warga Lokal',
                    'Bahan & Alat Praktik Anyaman Bambu (Bawa Pulang)',
                    'Welcome Drink Loloh Cemcem Khas Desa',
                    'Modul Edukasi Tata Ruang Penglipuran',
                ],
                'rundown' => [
                    ['time' => '09.00 – 10.00 WITA', 'title' => 'Prolog Kosmologi Sanga Mandala & Pola Linear', 'desc' => 'Pemaparan komprehensif sumbu kaja-kelod (gunung-laut) dan susunan simetris 76 pekarangan rumah adat.'],
                    ['time' => '10.00 – 11.30 WITA', 'title' => 'Bedah Angkul-Angkul, Bale Meten & Paon', 'desc' => 'Mengunjungi pekarangan terpilih untuk membedah fungsi ruang spiritual, sosial, dan domestik.'],
                    ['time' => '11.30 – 12.30 WITA', 'title' => 'Workshop Kerajinan Bambu & Konservasi', 'desc' => 'Praktik langsung menganyam bambu dipandu oleh pengrajin sepuh desa Penglipuran.'],
                ],
            ],
            [
                'id' => 'edu-kuliner',
                'title' => 'Kuliner Tradisi & Dapur Paon',
                'price' => 'Rp 200.000',
                'raw_price' => 200000,
                'unit' => 'per orang (Min. 2 Pax)',
                'duration' => '4 Jam',
                'badge' => 'LIVING KITCHEN • 4 JAM',
                'badge_color' => 'bg-pink-600 text-white',
                'image' => 'images/packages/kuliner-paon.webp',
                'tagline' => 'Meracik resep warisan leluhur dan bersantap bersama di perapian dapur tradisional.',
                'highlights' => [
                    'Meracik Minuman Herbal Loloh Cemcem Asli',
                    'Memasak Menu Tradisional Bersama Tuan Rumah',
                    'Makan Siang Komunal di Dapur Paon Tradisional',
                    'Filosofi Pawongan (Harmoni Hubungan Antar Manusia)',
                ],
                'inclusions' => [
                    'Termasuk Tiket Masuk Gerbang Desa Resmi & Hutan Bambu',
                    'Bahan Masak Lengkap & Resep Kuliner Warisan',
                    'Makan Siang Lengkap Hasil Masakan di Dapur Paon',
                    'Botol Loloh Cemcem Olahan Sendiri',
                    'Didampingi Ibu Rumah Tangga Warga Penglipuran',
                ],
                'rundown' => [
                    ['time' => '09.30 – 10.30 WITA', 'title' => 'Memetik Daun Cemcem & Racik Loloh Segar', 'desc' => 'Mengenal tanaman cemcem berkhasiat di pekarangan dan menumbuk ramuan herbal tradisional.'],
                    ['time' => '10.30 – 12.30 WITA', 'title' => 'Aktivitas Memasak di Tungku Dapur Paon', 'desc' => 'Praktik memasak menggunakan tungku kayu bakar tradisional bersama ibu-ibu warga adat.'],
                    ['time' => '12.30 – 13.30 WITA', 'title' => 'Megibung: Santap Siang Bersama Tuan Rumah', 'desc' => 'Menikmati makan siang hangat dengan tradisi kekeluargaan khas Bali.'],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     id: string,
     *     title: string,
     *     price: string,
     *     raw_price: int,
     *     unit: string,
     *     duration: string,
     *     badge: string,
     *     badge_color: string,
     *     image: string,
     *     tagline: string,
     *     highlights: list<string>
     * }>
     */
    public function homepageSlides(): array
    {
        $homepageFields = [
            'id', 'title', 'price', 'raw_price', 'unit', 'duration', 'badge',
            'badge_color', 'image', 'tagline', 'highlights',
        ];

        return array_map(
            static fn (array $package): array => Arr::only($package, $homepageFields),
            $this->all(),
        );
    }
}
