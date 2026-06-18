<?php

namespace Database\Seeders;

use App\Models\TourPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TourPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Paket Jelajah Budaya & Sejarah',
                'description' => 'Pelajari sejarah berdirinya Desa Adat Penglipuran, filosofi tata ruang Tri Hita Karana, arsitektur Angkul-angkul (gapura khas), serta kehidupan sehari-hari masyarakat lokal langsung dari pemandu bersertifikasi.',
                'inclusions' => [
                    'Pemandu wisata bersertifikat',
                    'Tiket masuk desa adat',
                    'Welcome drink (Loloh Cemcem)',
                    'Air mineral',
                    'Kit edukasi (leaflet/peta)',
                ],
                'exclusions' => [
                    'Makan siang',
                    'Pengeluaran pribadi',
                    'Transportasi / penjemputan',
                ],
                'price' => 150000.00,
                'duration_hours' => 3.0,
                'max_capacity' => 20,
                'min_capacity' => 1,
                'images' => [],
                'is_active' => true,
            ],
            [
                'name' => 'Paket Workshop Tenun & Kerajinan Bambu',
                'description' => 'Belajar langsung dari pengrajin lokal cara menganyam bambu menjadi anyaman bernilai seni tinggi atau menenun kain tradisional dengan alat tenun bukan mesin (ATBM). Rasakan pengalaman otentik menjadi bagian dari komunitas kreatif Penglipuran.',
                'inclusions' => [
                    'Pemandu lokal',
                    'Instruktur kerajinan profesional',
                    'Bahan dasar (bambu/benang tenun)',
                    'Hasil kerajinan buatan sendiri dapat dibawa pulang',
                    'Makan siang tradisional (Nasi Sela)',
                ],
                'exclusions' => [
                    'Pengeluaran pribadi',
                    'Transportasi ke lokasi',
                ],
                'price' => 250000.00,
                'duration_hours' => 4.5,
                'max_capacity' => 10,
                'min_capacity' => 2,
                'images' => [],
                'is_active' => true,
            ],
            [
                'name' => 'Paket Edukasi & Kuliner Tradisional Loloh Cemcem',
                'description' => 'Jelajahi hutan bambu lindung Desa Penglipuran, pelajari tanaman obat keluarga (TOGA) yang tumbuh subur di pekarangan warga, lalu ikuti kelas interaktif membuat Loloh Cemcem segar yang menyehatkan tubuh secara tradisional.',
                'inclusions' => [
                    'Pemandu wisata',
                    'Demo & kelas pembuatan Loloh Cemcem',
                    'Bahan baku herbal segar',
                    '1 botol Loloh buatan sendiri untuk dibawa pulang',
                    'Makan siang menu tradisional khas Penglipuran',
                ],
                'exclusions' => [
                    'Pengeluaran pribadi',
                    'Tips pemandu',
                ],
                'price' => 175000.00,
                'duration_hours' => 3.5,
                'max_capacity' => 15,
                'min_capacity' => 1,
                'images' => [],
                'is_active' => true,
            ],
            [
                'name' => 'Paket Sunrise Trekking Hutan Bambu',
                'description' => 'Nikmati keheningan pagi hari yang menyegarkan di Desa Penglipuran sebelum dipadati wisatawan. Melakukan trekking menembus keasrian 45 hektar hutan bambu konservasi saat matahari terbit, diakhiri dengan sarapan pagi tradisional yang hangat.',
                'inclusions' => [
                    'Pemandu trekking lokal',
                    'Tiket masuk hutan bambu',
                    'Sarapan pagi ringan di tengah hutan (kopi Bali & jajan pasar)',
                    'Peminjaman senter',
                ],
                'exclusions' => [
                    'Pengeluaran pribadi',
                    'Transportasi ke titik kumpul',
                ],
                'price' => 200000.00,
                'duration_hours' => 2.5,
                'max_capacity' => 12,
                'min_capacity' => 2,
                'images' => [],
                'is_active' => true,
            ],
            [
                'name' => 'Paket Full-Day Premium Smart Edutourism',
                'description' => 'Paket terlengkap untuk menikmati seluruh potensi edutourism Desa Penglipuran secara komprehensif. Mengintegrasikan penjelajahan interaktif Augmented Reality (AR) di peta 3D, kuis berhadiah, pembelajaran kerajinan tradisional, hingga eksplorasi alam bebas.',
                'inclusions' => [
                    'Akses penuh fitur Smart Edutourism AR & Quiz',
                    'Pemandu khusus pendamping seharian penuh',
                    'Tiket masuk terusan desa & hutan',
                    'Welcome drink Loloh Cemcem',
                    'Kelas anyaman bambu & pembuatan Loloh Cemcem',
                    'Makan siang premium di restoran lokal',
                    'Suvenir eksklusif khas Penglipuran',
                ],
                'exclusions' => [
                    'Pengeluaran pribadi di luar program utama',
                ],
                'price' => 450000.00,
                'duration_hours' => 8.0,
                'max_capacity' => 8,
                'min_capacity' => 2,
                'images' => [],
                'is_active' => true,
            ],
        ];

        foreach ($packages as $pkg) {
            $pkg['slug'] = Str::slug($pkg['name']);
            TourPackage::updateOrCreate(
                ['slug' => $pkg['slug']],
                $pkg
            );
        }
    }
}
