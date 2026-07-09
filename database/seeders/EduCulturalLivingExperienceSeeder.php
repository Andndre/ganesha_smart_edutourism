<?php

namespace Database\Seeders;

use App\Models\TourPackage;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Edu Cultural Living Experience in Penglipuran Village" tour package —
 * a real 7-hour guided itinerary currently offered in the village (source: "Paket
 * Wisata" reference document supplied by the village management).
 *
 * ponytail: price/max_capacity are placeholders (not specified in the source
 * document) — update via admin once the village confirms pricing/group size.
 *
 * Idempotent: keyed by slug, safe to re-run.
 * NOTE: translatable fields MUST be locale-keyed arrays, never bare strings.
 */
class EduCulturalLivingExperienceSeeder extends Seeder
{
    public function run(): void
    {
        TourPackage::updateOrCreate(
            ['slug' => 'edu-cultural-living-experience-penglipuran'],
            [
                'name' => [
                    'id' => 'Edu Cultural Living Experience Penglipuran',
                    'en' => 'Edu Cultural Living Experience in Penglipuran Village',
                ],
                'type' => 'package',
                'description' => [
                    'id' => "Penglipuran bukan hanya desa yang indah untuk dikunjungi, tetapi ruang belajar budaya Bali yang hidup. Melalui interaksi dengan masyarakat, wisatawan diajak memahami filosofi kehidupan, tradisi, seni, arsitektur, hingga praktik keberlanjutan yang diwariskan secara turun-temurun.\n\nCocok untuk: anak sekolah, mahasiswa, wisatawan minat khusus, komunitas, maupun delegasi internasional.\n\nNilai Eduwisata — Learning by EDI (Experience, Doing, Interacting): mengalami kehidupan desa secara autentik, ikut mempraktikkan budaya secara langsung, dan belajar bersama masyarakat lokal.",
                    'en' => "Penglipuran is not just a beautiful village to visit, but a living space for learning Balinese culture. Through interaction with the local community, visitors come to understand the philosophy of life, traditions, arts, architecture, and sustainability practices passed down through generations.\n\nSuited for: school children, university students, special-interest travelers, communities, and international delegations.\n\nEduwisata values — Learning by EDI (Experience, Doing, Interacting): authentically experiencing village life, practicing the culture hands-on, and learning directly alongside the local community.",
                ],
                'price' => 350000,
                'duration_hours' => 7.0,
                'min_capacity' => 2,
                'max_capacity' => 15,
                'inclusions' => [
                    'id' => ['Pemandu lokal', 'Makan siang bersama keluarga tuan rumah', 'Aktivitas budaya (tari Bali, membuat canang, kostum adat)', 'Coffee break di hutan bambu (khusus Sabtu & Minggu)'],
                    'en' => ['Local guide', 'Lunch with a host family', 'Cultural activities (Balinese dance, canang making, traditional costume)', 'Bamboo forest coffee break (Saturday & Sunday only)'],
                ],
                'exclusions' => [
                    'id' => ['Transportasi menuju/dari desa', 'Belanja pribadi di Pasar Penglipur Lara'],
                    'en' => ['Transport to/from the village', 'Personal shopping at Pasar Penglipur Lara'],
                ],
                'itinerary' => [
                    'id' => [
                        [
                            'time' => '08.30 – 10.00',
                            'title' => 'Penyambutan oleh Local Guide di Tugu Pahlawan Penglipuran',
                            'description' => 'Interpretasi budaya mengenai sejarah Desa Penglipuran, filosofi tata ruang desa, nilai Tri Hita Karana, kisah perjuangan masyarakat Penglipuran, makna Tugu Pahlawan, dan peran masyarakat dalam menjaga tradisi.',
                            'activities' => ['Belajar Tari Bali', 'Belajar membuat Canang'],
                        ],
                        [
                            'time' => '10.00 – 12.30',
                            'title' => 'Living House Experience',
                            'description' => 'Wisatawan memasuki rumah-rumah tradisional masyarakat. Interpretasi meliputi Rumah Adat dan Bale Saka Enam (fungsi ruang keluarga dalam masyarakat Bali), serta Dapur Tradisional (pusat aktivitas domestik dan simbol kehangatan keluarga).',
                            'activities' => ['Memasak masakan khas keluarga di rumah penduduk', 'Makan siang bersama'],
                        ],
                        [
                            'time' => '12.30 – 13.00',
                            'title' => 'Bali Costume Experience',
                            'description' => 'Memakai pakaian tradisional masyarakat Hindu Bali bersama keluarga tuan rumah — cara pemakaian baju adat Bali untuk pria dan wanita. Aktivitas dilakukan bersama masyarakat lokal dan foto bersama keluarga.',
                            'activities' => [],
                        ],
                        [
                            'time' => '13.00 – 13.30',
                            'title' => 'Karang Memadu — Penglipuran Social System',
                            'description' => 'Belajar sistem sosial dan nilai adat dalam kehidupan masyarakat Penglipuran.',
                            'activities' => [],
                        ],
                        [
                            'time' => '13.30 – 14.30',
                            'title' => 'Pura Dukuh Cultural Stop',
                            'description' => 'Berjalan menuju kawasan pura, foto bersama dengan latar gerbang tradisional Penglipuran. Interpretasi: tradisi keagamaan di Pura Dukuh, konsep keseimbangan spiritual, filosofi pura desa, dan hubungan masyarakat dengan ruang suci.',
                            'activities' => [],
                        ],
                        [
                            'time' => '14.30 – 15.30',
                            'title' => 'Bamboo Forest Heritage Walk',
                            'description' => 'Berjalan menuju kawasan hutan bambu melewati Bamboo Bridge dan Relief Sejarah Penglipuran yang menceritakan perjalanan masyarakat desa dari masa lalu hingga kini. Pembelajaran mengenai fungsi ekologis bambu, konservasi lingkungan, ekonomi kreatif berbasis bambu, dan bambu sebagai identitas Penglipuran.',
                            'activities' => [],
                        ],
                        [
                            'time' => '15.30 – 16.30',
                            'title' => 'Monumen Kalpataru',
                            'description' => 'Interpretasi akhir perjalanan dengan tema Penglipuran sebagai desa yang menjaga hubungan harmonis antara manusia, budaya, dan alam. Materi: penghargaan Kalpataru, konservasi bambu, keberlanjutan desa, keterlibatan masyarakat, dan desa wisata berbasis komunitas.',
                            'activities' => [],
                        ],
                        [
                            'time' => 'Opsional • khusus Sabtu & Minggu',
                            'title' => 'Pasar Penglipur Lara & Bamboo Café Experience',
                            'description' => 'Interaksi dengan pedagang lokal, kuliner tradisional, produk UMKM, dan jajanan khas desa, dilanjutkan coffee break di tengah hutan bambu.',
                            'activities' => ['Pasar Penglipur Lara: pedagang lokal, kuliner tradisional, produk UMKM, jajanan khas desa', 'Bamboo Café: kopi lokal, teh herbal, loloh cem-cem, jajanan tradisional'],
                        ],
                    ],
                    'en' => [
                        [
                            'time' => '08.30 – 10.00',
                            'title' => 'Welcome by the local guide at the Penglipuran Heroes Monument',
                            'description' => 'Cultural interpretation of the history of Penglipuran Village, the philosophy of its spatial layout, the value of Tri Hita Karana, the community\'s struggle, the meaning of the Heroes Monument, and the community\'s role in preserving tradition.',
                            'activities' => ['Learn Balinese dance', 'Learn to make canang (offerings)'],
                        ],
                        [
                            'time' => '10.00 – 12.30',
                            'title' => 'Living House Experience',
                            'description' => 'Visitors enter the community\'s traditional houses. Interpretation covers the Rumah Adat and Bale Saka Enam (the family space\'s role in Balinese society), and the Traditional Kitchen (the center of domestic life and family warmth).',
                            'activities' => ['Cook a family recipe at a resident\'s home', 'Share lunch together'],
                        ],
                        [
                            'time' => '12.30 – 13.00',
                            'title' => 'Bali Costume Experience',
                            'description' => 'Wear traditional Balinese Hindu clothing with a host family — how to wear Balinese traditional attire for men and women. The activity is done together with the local community, followed by a family photo.',
                            'activities' => [],
                        ],
                        [
                            'time' => '13.00 – 13.30',
                            'title' => 'Karang Memadu — Penglipuran Social System',
                            'description' => 'Learn about the social system and traditional values in Penglipuran community life.',
                            'activities' => [],
                        ],
                        [
                            'time' => '13.30 – 14.30',
                            'title' => 'Pura Dukuh Cultural Stop',
                            'description' => 'Walk toward the temple area, take a group photo with Penglipuran\'s traditional gate as the backdrop. Interpretation: religious traditions at Pura Dukuh, the concept of spiritual balance, temple philosophy, and the community\'s relationship with sacred space.',
                            'activities' => [],
                        ],
                        [
                            'time' => '14.30 – 15.30',
                            'title' => 'Bamboo Forest Heritage Walk',
                            'description' => 'Walk toward the bamboo forest area, passing the Bamboo Bridge and the Penglipuran History Relief, which tells the story of the village community from the past to the present. Learn about bamboo\'s ecological function, environmental conservation, bamboo-based creative economy, and bamboo as Penglipuran\'s identity.',
                            'activities' => [],
                        ],
                        [
                            'time' => '15.30 – 16.30',
                            'title' => 'Kalpataru Monument',
                            'description' => 'Closing interpretation with the theme of Penglipuran as a village that maintains harmony between people, culture, and nature. Topics: the Kalpataru award, bamboo conservation, village sustainability, community involvement, and community-based tourism.',
                            'activities' => [],
                        ],
                        [
                            'time' => 'Optional • Saturday & Sunday only',
                            'title' => 'Pasar Penglipur Lara & Bamboo Café Experience',
                            'description' => 'Interact with local vendors, traditional food, UMKM products, and village snacks, followed by a coffee break in the bamboo forest.',
                            'activities' => ['Pasar Penglipur Lara: local vendors, traditional food, UMKM products, village snacks', 'Bamboo Café: local coffee, herbal tea, loloh cem-cem, traditional snacks'],
                        ],
                    ],
                ],
                'is_active' => true,
            ]
        );
    }
}
