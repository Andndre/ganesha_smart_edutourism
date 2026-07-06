<?php

namespace Database\Seeders;

use App\Models\CulturalObject;
use App\Models\RouteMission;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Rute 3 "Penglipuran Eco Quest: The Secret of the Bamboo Village" — 5 titik bermain +
 * layar rekap. Titik 1 (Gerbang Desa), 3 (Rumah Tradisional → reuse "Traditional House
 * Merajan") dan 4 (Hutan Bambu) reuse CulturalObject existing (lokasi fisik sama, hanya
 * konten belajar berbeda). Titik 1's quiz mission adalah RouteMission independen milik
 * rute ini.
 *
 * Konten Bahasa Indonesia mengikuti PDF "Rute Eduwisata" (ClickUp Doc `2kzkxyn8-758`);
 * detail yang tidak dirinci PDF (10 kartu Green Detective, skenario Eco Rescue/Design
 * Challenge) diimprovisasi mengikuti tema — perlu review manusia. Terjemahan EN juga
 * perlu review.
 *
 * Idempotent — pola sama dengan Route1HeritageQuestSeeder & Route2CulturalAdventureSeeder.
 */
class Route3EcoQuestSeeder extends Seeder
{
    public function run(): void
    {
        $objects = [
            'gerbang' => $this->ensureObject(['gerbang desa', 'gapura desa', 'village gate', 'main gate'], [
                'name' => ['id' => 'Gerbang Desa Penglipuran', 'en' => 'Penglipuran Village Gate'],
                'short_description' => [
                    'id' => 'Pintu masuk utama Desa Penglipuran, titik awal setiap rute eduwisata.',
                    'en' => 'The main entrance of Penglipuran Village, starting point of every edutourism route.',
                ],
                'description' => [
                    'id' => 'Gerbang Desa Penglipuran menyambut wisatawan menuju kawasan desa adat dengan tata ruang Tri Mandala yang tertata rapi.',
                    'en' => 'The Penglipuran Village Gate welcomes visitors into the traditional village with its neatly arranged Tri Mandala layout.',
                ],
                'category' => 'tradition',
            ], -8.42340, 115.35920),

            'koridor' => $this->ensureObject(['koridor desa', 'village corridor'], [
                'name' => ['id' => 'Koridor Desa Penglipuran', 'en' => 'Penglipuran Village Corridor'],
                'short_description' => [
                    'id' => 'Jalan utama desa dengan deretan angkul-angkul dan tata ruang tradisional.',
                    'en' => 'The village main street lined with angkul-angkul gates and traditional spatial layout.',
                ],
                'description' => [
                    'id' => 'Koridor utama Desa Penglipuran menerapkan konsep kebersihan dan tata ruang lingkungan yang menjadikannya salah satu desa terbersih di dunia.',
                    'en' => 'The main corridor of Penglipuran applies cleanliness and spatial planning concepts that make it one of the cleanest villages in the world.',
                ],
                'category' => 'tradition',
            ], -8.42280, 115.35915),

            'merajan' => $this->ensureObject(['merajan', 'sanggah', 'traditional house'], [
                'name' => ['id' => 'Merajan Rumah Tradisional', 'en' => 'Traditional House Merajan'],
                'short_description' => [
                    'id' => 'Rumah tradisional Bali yang memanfaatkan bambu secara ekstensif.',
                    'en' => 'A traditional Balinese house that makes extensive use of bamboo.',
                ],
                'description' => [
                    'id' => 'Rumah tradisional Penglipuran memanfaatkan bambu dalam kehidupan sehari-hari, mulai dari bangunan, peralatan rumah tangga, hingga sarana upacara.',
                    'en' => 'Penglipuran\'s traditional houses use bamboo in daily life, from buildings to household tools and ceremonial items.',
                ],
                'category' => 'temple',
            ], -8.42230, 115.35900),

            'bambu' => $this->ensureObject(['hutan bambu', 'bamboo forest', 'bamboo'], [
                'name' => ['id' => 'Hutan Bambu Penglipuran', 'en' => 'Penglipuran Bamboo Forest'],
                'short_description' => [
                    'id' => 'Kawasan hutan bambu seluas ±45 hektar yang dijaga secara adat.',
                    'en' => 'A ±45-hectare bamboo forest protected by customary law.',
                ],
                'description' => [
                    'id' => 'Hutan bambu Penglipuran menjaga sumber air dan mencegah erosi. Pemanfaatannya diatur secara adat agar tetap lestari.',
                    'en' => 'The Penglipuran bamboo forest protects water sources and prevents erosion. Its use is regulated by customary law to keep it sustainable.',
                ],
                'category' => 'tradition',
            ], -8.42050, 115.35980),

            'kerajinan' => $this->ensureObject(['sentra kerajinan bambu', 'bamboo craft center'], [
                'name' => ['id' => 'Sentra Kerajinan Bambu', 'en' => 'Bamboo Craft Center'],
                'short_description' => [
                    'id' => 'Pusat produksi kerajinan bambu warga Desa Penglipuran.',
                    'en' => 'The production center for Penglipuran residents\' bamboo handicrafts.',
                ],
                'description' => [
                    'id' => 'Sentra Kerajinan Bambu menampilkan berbagai produk bambu buatan warga, mulai dari perabot rumah tangga, dekorasi pariwisata, sarana upacara, hingga suvenir.',
                    'en' => 'The Bamboo Craft Center showcases various bamboo products made by residents, from household items and tourism decor to ceremonial tools and souvenirs.',
                ],
                'category' => 'tradition',
            ], -8.42010, 115.35950),
        ];

        $route = $this->ensureRoute();

        $points = $this->ensurePoints($route, $objects);

        $this->rebuildMissions($points);

        $this->command?->info('Rute 3 "Penglipuran Eco Quest" seeded: '.count($points).' points.');
    }

    private function ensureObject(array $keywords, array $attributes, float $lat, float $lng): CulturalObject
    {
        $existing = CulturalObject::all()->first(function ($object) use ($keywords) {
            $names = array_map('mb_strtolower', array_filter($object->getTranslations('name')));

            foreach ($keywords as $keyword) {
                foreach ($names as $name) {
                    if (str_contains($name, $keyword)) {
                        return true;
                    }
                }
            }

            return false;
        });

        if ($existing) {
            if (! $existing->mapLocation) {
                $existing->syncMapLocation([
                    'category' => 'cultural',
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'is_accessible' => true,
                ]);
            }

            return $existing;
        }

        $object = CulturalObject::create($attributes + [
            'slug' => Str::slug($attributes['name']['en']).'-'.Str::lower(Str::random(4)),
        ]);

        $object->syncMapLocation([
            'category' => 'cultural',
            'latitude' => $lat,
            'longitude' => $lng,
            'is_accessible' => true,
        ]);

        return $object;
    }

    private function ensureRoute(): TourRoute
    {
        $route = TourRoute::all()->first(
            fn ($r) => str_contains(mb_strtolower(translateValue($r->name, 'en')), 'eco quest')
        );

        $attributes = [
            'name' => ['id' => 'Penglipuran Eco Quest: The Secret of the Bamboo Village', 'en' => 'Penglipuran Eco Quest: The Secret of the Bamboo Village'],
            'description' => [
                'id' => 'Menjelajahi Kearifan Lokal dan Konservasi Lingkungan Desa Penglipuran dalam 90 menit melalui 6 pemberhentian.',
                'en' => 'Exploring Local Wisdom and Environmental Conservation in Penglipuran Village in 90 minutes across 6 stops.',
            ],
            'difficulty' => 'moderate',
            'gamification_key' => 'eco_quest',
            'estimated_duration_minutes' => 90,
            'distance_meters' => 1300,
            'is_active' => true,
        ];

        if ($route) {
            $route->update($attributes);

            return $route;
        }

        return TourRoute::create($attributes);
    }

    /**
     * @param  array<string, CulturalObject>  $objects
     * @return array<int, TourRoutePoint>
     */
    private function ensurePoints(TourRoute $route, array $objects): array
    {
        $definitions = [
            1 => ['object' => $objects['gerbang'], 'minutes' => 10, 'storytelling' => [
                'id' => 'Selamat datang di misi "Save the Bamboo Village"! Keseimbangan Desa Penglipuran bergantung pada keberhasilanmu menyelamatkan 5 Eco Crystal. Jawab kuis orientasi untuk mendapatkan Eco Crystal pertama.',
                'en' => 'Welcome to the "Save the Bamboo Village" mission! Penglipuran\'s balance depends on your success in saving 5 Eco Crystals. Answer the orientation quiz to earn your first Eco Crystal.',
            ]],
            2 => ['object' => $objects['koridor'], 'minutes' => 15, 'storytelling' => [
                'id' => 'Akses peta digital dan informasi mengenai kebersihan serta pengelolaan lingkungan desa, lalu selesaikan Green Detective untuk Eco Crystal kedua.',
                'en' => 'Access the digital map and information about the village\'s cleanliness and environmental management, then complete Green Detective for your second Eco Crystal.',
            ]],
            3 => ['object' => $objects['merajan'], 'minutes' => 15, 'storytelling' => [
                'id' => 'Pindai QR Code untuk mempelajari pemanfaatan bambu dalam kehidupan sehari-hari, lalu jadi Bamboo Builder — susun komponen rumah dari bahan yang tepat.',
                'en' => 'Scan the QR code to learn how bamboo is used in daily life, then become the Bamboo Builder — arrange house components using the right materials.',
            ]],
            4 => ['object' => $objects['bambu'], 'minutes' => 25, 'storytelling' => [
                'id' => 'Jelajahi hutan bambu sambil mempelajari fungsi ekologisnya, lalu jadi Eco Rescue — pilih tindakan terbaik untuk menyelamatkan hutan dari berbagai ancaman.',
                'en' => 'Explore the bamboo forest while learning about its ecological function, then become Eco Rescue — choose the best action to save the forest from various threats.',
            ]],
            5 => ['object' => $objects['kerajinan'], 'minutes' => 15, 'storytelling' => [
                'id' => 'Pindai QR Code untuk mengenal produk kerajinan bambu, lalu ikuti Design Challenge — pilih produk bambu paling sesuai untuk setiap kebutuhan.',
                'en' => 'Scan the QR code to learn about bamboo craft products, then take on the Design Challenge — choose the most suitable bamboo product for each need.',
            ]],
        ];

        $points = [];

        foreach ($definitions as $order => $def) {
            $hasArMarker = (bool) $def['object']->mapLocation?->arModel?->ar_marker_id;

            $points[$order] = TourRoutePoint::updateOrCreate(
                ['tour_route_id' => $route->id, 'order' => $order],
                [
                    'locationable_type' => $def['object']->getMorphClass(),
                    'locationable_id' => $def['object']->id,
                    'estimated_visit_minutes' => $def['minutes'],
                    'storytelling_content' => $def['storytelling'],
                    'qr_code_token' => $hasArMarker ? null : 'EDU-R3-P'.$order,
                ]
            );
        }

        TourRoutePoint::where('tour_route_id', $route->id)->where('order', '>', 5)->delete();

        return $points;
    }

    /**
     * Mission 1 quiz config — independent copy of Route 1's 5 MCQs (same starting
     * content per team decision; each route's questions can be customized later).
     *
     * @return array<string, mixed>
     */
    private function pointOneQuizConfig(): array
    {
        return ['questions' => [
            [
                'prompt' => ['id' => 'Desa Penglipuran terletak di kabupaten apa?', 'en' => 'In which regency is Penglipuran Village located?'],
                'option_a' => ['id' => 'Bangli', 'en' => 'Bangli'],
                'option_b' => ['id' => 'Badung', 'en' => 'Badung'],
                'option_c' => ['id' => 'Gianyar', 'en' => 'Gianyar'],
                'option_d' => ['id' => 'Tabanan', 'en' => 'Tabanan'],
                'correct_option' => 'A',
                'explanation' => ['id' => 'Desa Penglipuran berada di Kelurahan Kubu, Kabupaten Bangli, Bali.', 'en' => 'Penglipuran Village is located in Kubu, Bangli Regency, Bali.'],
            ],
            [
                'prompt' => ['id' => 'Konsep tata ruang tradisional yang membagi Desa Penglipuran menjadi tiga zona disebut?', 'en' => 'What is the traditional spatial concept dividing Penglipuran into three zones called?'],
                'option_a' => ['id' => 'Tri Datu', 'en' => 'Tri Datu'],
                'option_b' => ['id' => 'Tri Mandala', 'en' => 'Tri Mandala'],
                'option_c' => ['id' => 'Tri Kaya Parisudha', 'en' => 'Tri Kaya Parisudha'],
                'option_d' => ['id' => 'Tri Sakti', 'en' => 'Tri Sakti'],
                'correct_option' => 'B',
                'explanation' => ['id' => 'Tri Mandala membagi desa menjadi Utama, Madya, dan Nista Mandala dari hulu ke hilir.', 'en' => 'Tri Mandala divides the village into Utama, Madya, and Nista Mandala from upstream to downstream.'],
            ],
            [
                'prompt' => ['id' => 'Gerbang khas di depan setiap rumah tradisional Penglipuran disebut?', 'en' => 'What is the distinctive gate in front of every traditional Penglipuran house called?'],
                'option_a' => ['id' => 'Candi Bentar', 'en' => 'Candi Bentar'],
                'option_b' => ['id' => 'Kori Agung', 'en' => 'Kori Agung'],
                'option_c' => ['id' => 'Angkul-angkul', 'en' => 'Angkul-angkul'],
                'option_d' => ['id' => 'Aling-aling', 'en' => 'Aling-aling'],
                'correct_option' => 'C',
                'explanation' => ['id' => 'Angkul-angkul yang seragam di sepanjang koridor adalah ikon arsitektur Penglipuran.', 'en' => 'The uniform angkul-angkul along the corridor are Penglipuran\'s architectural icon.'],
            ],
            [
                'prompt' => ['id' => 'Desa Penglipuran dikenal dunia sebagai salah satu desa paling ... di dunia.', 'en' => 'Penglipuran is known worldwide as one of the most ... villages in the world.'],
                'option_a' => ['id' => 'Ramai', 'en' => 'Crowded'],
                'option_b' => ['id' => 'Bersih', 'en' => 'Clean'],
                'option_c' => ['id' => 'Luas', 'en' => 'Vast'],
                'option_d' => ['id' => 'Tua', 'en' => 'Old'],
                'correct_option' => 'B',
                'explanation' => ['id' => 'Penglipuran berulang kali masuk daftar desa terbersih di dunia berkat kedisiplinan adat menjaga lingkungan.', 'en' => 'Penglipuran repeatedly ranks among the world\'s cleanest villages thanks to customary discipline in caring for the environment.'],
            ],
            [
                'prompt' => ['id' => 'Bahan bangunan tradisional yang paling banyak dimanfaatkan warga Penglipuran adalah?', 'en' => 'Which traditional building material is most used by Penglipuran residents?'],
                'option_a' => ['id' => 'Batu bata merah', 'en' => 'Red brick'],
                'option_b' => ['id' => 'Kayu jati', 'en' => 'Teak wood'],
                'option_c' => ['id' => 'Bambu', 'en' => 'Bamboo'],
                'option_d' => ['id' => 'Batu paras', 'en' => 'Paras stone'],
                'correct_option' => 'C',
                'explanation' => ['id' => 'Bambu dari hutan adat dipakai untuk atap, dinding, hingga kerajinan — pemanfaatannya diatur secara adat.', 'en' => 'Bamboo from the customary forest is used for roofs, walls, and crafts — its use is regulated by adat.'],
            ],
        ]];
    }

    /**
     * Missions per point.
     *
     * @param  array<int, TourRoutePoint>  $points
     */
    private function rebuildMissions(array $points): void
    {
        // Titik 1 — Mission 1: 5 MCQs to earn the first Eco Crystal.
        $this->mission($points[1], 1, 'quiz', ['id' => 'Buka Gerbang Desa', 'en' => 'Unlock the Village'], $this->pointOneQuizConfig(), 500);

        // Titik 2 — Mission 2 "Green Detective": 10 kartu, 6 perilaku ramah lingkungan benar.
        $this->mission($points[2], 1, 'matching', ['id' => 'Green Detective', 'en' => 'Green Detective'], [
            'mode' => 'pick',
            'prompt' => ['id' => 'Pilih 6 perilaku ramah lingkungan sebelum waktu habis!', 'en' => 'Pick the 6 eco-friendly behaviors before time runs out!'],
            'pick_count' => 6,
            'items' => [
                ['label' => ['id' => 'Membawa tumbler sendiri', 'en' => 'Bringing your own tumbler'], 'icon' => '🧴', 'correct' => true],
                ['label' => ['id' => 'Membuang sampah pada tempatnya', 'en' => 'Disposing of trash properly'], 'icon' => '🗑️', 'correct' => true],
                ['label' => ['id' => 'Menggunakan tas kain', 'en' => 'Using a cloth bag'], 'icon' => '👜', 'correct' => true],
                ['label' => ['id' => 'Menghemat penggunaan air', 'en' => 'Conserving water use'], 'icon' => '💧', 'correct' => true],
                ['label' => ['id' => 'Ikut menanam pohon', 'en' => 'Joining a tree-planting activity'], 'icon' => '🌱', 'correct' => true],
                ['label' => ['id' => 'Tidak memetik tanaman liar', 'en' => 'Not picking wild plants'], 'icon' => '🌿', 'correct' => true],
                ['label' => ['id' => 'Membuang sampah sembarangan', 'en' => 'Littering'], 'icon' => '🚯', 'correct' => false],
                ['label' => ['id' => 'Memetik bunga liar untuk suvenir', 'en' => 'Picking wildflowers as souvenirs'], 'icon' => '🌸', 'correct' => false],
                ['label' => ['id' => 'Memakai plastik sekali pakai berlebihan', 'en' => 'Overusing single-use plastic'], 'icon' => '🥤', 'correct' => false],
                ['label' => ['id' => 'Menyalakan api unggun sembarangan', 'en' => 'Lighting campfires carelessly'], 'icon' => '🔥', 'correct' => false],
            ],
        ]);

        // Titik 3 — Mission 3 "Bamboo Builder": cocokkan komponen rumah dengan bahan.
        $this->mission($points[3], 1, 'matching', ['id' => 'Bamboo Builder', 'en' => 'Bamboo Builder'], [
            'mode' => 'match',
            'prompt' => ['id' => 'Susun rumah tradisional dengan mencocokkan komponen dan bahan yang tepat!', 'en' => 'Build the traditional house by matching each component with the right material!'],
            'pairs' => [
                ['left' => ['id' => 'Atap', 'en' => 'Roof'], 'right' => ['id' => 'Bambu dianyam / ijuk', 'en' => 'Woven bamboo / palm fiber'], 'icon' => '🏠'],
                ['left' => ['id' => 'Dinding', 'en' => 'Wall'], 'right' => ['id' => 'Anyaman bambu dan kayu', 'en' => 'Woven bamboo and wood'], 'icon' => '🧱'],
                ['left' => ['id' => 'Pondasi', 'en' => 'Foundation'], 'right' => ['id' => 'Batu', 'en' => 'Stone'], 'icon' => '🪨'],
                ['left' => ['id' => 'Lantai', 'en' => 'Floor'], 'right' => ['id' => 'Tanah dipadatkan', 'en' => 'Compacted earth'], 'icon' => '🟫'],
            ],
        ]);

        // Titik 4 — Mission 4 "Eco Rescue": decision game, 4 skenario ancaman hutan.
        $this->mission($points[4], 1, 'decision', ['id' => 'Eco Rescue', 'en' => 'Eco Rescue'], [
            'scenarios' => [
                [
                    'text' => ['id' => 'Kamu melihat penebangan liar bambu tanpa izin adat. Apa tindakan terbaikmu?', 'en' => 'You see illegal bamboo logging without customary permission. What is your best action?'],
                    'options' => [
                        ['text' => ['id' => 'Melaporkannya ke pengelola atau pecalang desa', 'en' => 'Report it to the manager or village security'], 'correct' => true, 'explanation' => ['id' => 'Benar! Melapor membantu menjaga hutan tetap terkendali secara adat.', 'en' => 'Correct! Reporting helps keep the forest under customary control.']],
                        ['text' => ['id' => 'Ikut menebang karena sudah banyak yang menebang', 'en' => 'Join the logging since others already are'], 'correct' => false, 'explanation' => ['id' => 'Menebang tanpa izin merusak keseimbangan ekologis hutan.', 'en' => 'Logging without permission damages the forest\'s ecological balance.']],
                        ['text' => ['id' => 'Diam saja karena bukan urusanmu', 'en' => 'Stay silent since it is not your business'], 'correct' => false, 'explanation' => ['id' => 'Diam berarti membiarkan kerusakan terus terjadi.', 'en' => 'Staying silent lets the damage continue.']],
                    ],
                ],
                [
                    'text' => ['id' => 'Terjadi kebakaran kecil di tepi hutan bambu akibat puntung rokok. Apa yang kamu lakukan?', 'en' => 'A small fire breaks out at the edge of the bamboo forest from a cigarette butt. What do you do?'],
                    'options' => [
                        ['text' => ['id' => 'Segera padamkan atau laporkan ke petugas terdekat', 'en' => 'Put it out immediately or report it to the nearest staff'], 'correct' => true, 'explanation' => ['id' => 'Benar! Respons cepat mencegah kebakaran meluas ke seluruh hutan.', 'en' => 'Correct! A quick response prevents the fire from spreading through the forest.']],
                        ['text' => ['id' => 'Mendekat untuk memotret kejadian', 'en' => 'Get closer to take photos of the incident'], 'correct' => false, 'explanation' => ['id' => 'Mendekat tanpa penanganan justru membahayakan diri sendiri.', 'en' => 'Getting closer without handling it properly endangers yourself.']],
                        ['text' => ['id' => 'Berlari pergi tanpa melapor', 'en' => 'Run away without reporting it'], 'correct' => false, 'explanation' => ['id' => 'Tidak melapor membuat api bisa membesar tanpa penanganan.', 'en' => 'Not reporting it lets the fire grow unchecked.']],
                    ],
                ],
                [
                    'text' => ['id' => 'Musim kemarau panjang membuat sumber air di desa menipis. Sikap terbaik pengunjung?', 'en' => 'A long dry season is depleting the village\'s water sources. What is the best attitude for a visitor?'],
                    'options' => [
                        ['text' => ['id' => 'Menghemat penggunaan air selama berkunjung', 'en' => 'Conserve water use during the visit'], 'correct' => true, 'explanation' => ['id' => 'Benar! Menghemat air membantu desa melewati musim kemarau.', 'en' => 'Correct! Conserving water helps the village get through the dry season.']],
                        ['text' => ['id' => 'Menggunakan air sebebas-bebasnya seperti biasa', 'en' => 'Use water as freely as usual'], 'correct' => false, 'explanation' => ['id' => 'Penggunaan air berlebih memperparah kelangkaan saat kemarau.', 'en' => 'Excessive water use worsens the shortage during the dry season.']],
                        ['text' => ['id' => 'Mengabaikan imbauan hemat air dari pengelola', 'en' => 'Ignore the water-saving appeal from management'], 'correct' => false, 'explanation' => ['id' => 'Mengabaikan imbauan memperberat beban warga desa.', 'en' => 'Ignoring the appeal adds to the burden on villagers.']],
                    ],
                ],
                [
                    'text' => ['id' => 'Kamu melihat sampah plastik menumpuk di aliran air dalam hutan bambu. Apa tindakanmu?', 'en' => 'You see plastic trash piling up in a stream inside the bamboo forest. What do you do?'],
                    'options' => [
                        ['text' => ['id' => 'Mengangkut dan membuangnya pada tempatnya', 'en' => 'Collect it and dispose of it properly'], 'correct' => true, 'explanation' => ['id' => 'Benar! Sampah di aliran air bisa mencemari sumber air desa.', 'en' => 'Correct! Trash in the stream can pollute the village\'s water source.']],
                        ['text' => ['id' => 'Membiarkannya hanyut terbawa air', 'en' => 'Let it drift away with the water'], 'correct' => false, 'explanation' => ['id' => 'Membiarkannya hanyut hanya memindahkan masalah ke hilir.', 'en' => 'Letting it drift only moves the problem downstream.']],
                        ['text' => ['id' => 'Menyembunyikannya di balik semak', 'en' => 'Hide it behind a bush'], 'correct' => false, 'explanation' => ['id' => 'Menyembunyikan sampah tidak menghilangkan dampaknya bagi lingkungan.', 'en' => 'Hiding litter does not remove its impact on the environment.']],
                    ],
                ],
            ],
        ]);

        // Titik 5 — Mission 5 "Design Challenge": pilih produk bambu paling sesuai kebutuhan.
        $this->mission($points[5], 1, 'decision', ['id' => 'Bamboo Creator: Design Challenge', 'en' => 'Bamboo Creator: Design Challenge'], [
            'scenarios' => [
                [
                    'text' => ['id' => 'Sebuah keluarga butuh wadah dapur ramah lingkungan untuk kebutuhan sehari-hari. Produk bambu apa yang paling sesuai?', 'en' => 'A family needs an eco-friendly kitchen container for daily use. Which bamboo product fits best?'],
                    'options' => [
                        ['text' => ['id' => 'Anyaman bakul/wadah dapur bambu', 'en' => 'Woven bamboo basket/kitchen container'], 'correct' => true, 'explanation' => ['id' => 'Benar! Produk rumah tangga cocok untuk kebutuhan dapur sehari-hari.', 'en' => 'Correct! Household products suit everyday kitchen needs.']],
                        ['text' => ['id' => 'Gazebo bambu besar', 'en' => 'Large bamboo gazebo'], 'correct' => false, 'explanation' => ['id' => 'Gazebo lebih cocok untuk kebutuhan pariwisata, bukan dapur harian.', 'en' => 'A gazebo suits tourism needs better, not daily kitchen use.']],
                        ['text' => ['id' => 'Payung hias bambu', 'en' => 'Decorative bamboo umbrella'], 'correct' => false, 'explanation' => ['id' => 'Payung hias tidak fungsional untuk kebutuhan dapur.', 'en' => 'A decorative umbrella is not functional for kitchen needs.']],
                    ],
                ],
                [
                    'text' => ['id' => 'Sebuah hotel ingin menciptakan suasana alami di area lobi wisata. Produk apa yang paling sesuai?', 'en' => 'A hotel wants a natural atmosphere in its tourism lobby area. Which product fits best?'],
                    'options' => [
                        ['text' => ['id' => 'Gazebo/dekorasi bambu', 'en' => 'Bamboo gazebo/decor'], 'correct' => true, 'explanation' => ['id' => 'Benar! Dekorasi bambu berskala besar cocok untuk suasana pariwisata.', 'en' => 'Correct! Large-scale bamboo decor suits a tourism atmosphere.']],
                        ['text' => ['id' => 'Sendok bambu sekali pakai', 'en' => 'Disposable bamboo spoon'], 'correct' => false, 'explanation' => ['id' => 'Terlalu kecil untuk membentuk suasana ruangan.', 'en' => 'Too small to shape a room\'s atmosphere.']],
                        ['text' => ['id' => 'Anyaman kecil untuk dapur', 'en' => 'Small woven kitchen item'], 'correct' => false, 'explanation' => ['id' => 'Lebih cocok untuk kebutuhan rumah tangga, bukan lobi hotel.', 'en' => 'Better suited for household use, not a hotel lobby.']],
                    ],
                ],
                [
                    'text' => ['id' => 'Sebuah keluarga akan mengadakan upacara adat dan butuh sarana dari bambu. Produk apa yang paling sesuai?', 'en' => 'A family is holding a customary ceremony and needs a bamboo item. Which product fits best?'],
                    'options' => [
                        ['text' => ['id' => 'Sarana upacara (wadah sesajen dari bambu)', 'en' => 'Ceremonial item (bamboo offering container)'], 'correct' => true, 'explanation' => ['id' => 'Benar! Sarana upacara memang dirancang untuk kebutuhan ritual adat.', 'en' => 'Correct! Ceremonial items are specifically made for customary rituals.']],
                        ['text' => ['id' => 'Suvenir gantungan kunci', 'en' => 'Souvenir keychain'], 'correct' => false, 'explanation' => ['id' => 'Suvenir kecil tidak memenuhi kebutuhan sarana upacara.', 'en' => 'A small souvenir does not meet ceremonial needs.']],
                        ['text' => ['id' => 'Perabot dapur modern', 'en' => 'Modern kitchen furniture'], 'correct' => false, 'explanation' => ['id' => 'Bukan barang bambu tradisional yang sesuai untuk upacara.', 'en' => 'Not a traditional bamboo item suited for a ceremony.']],
                    ],
                ],
                [
                    'text' => ['id' => 'Seorang wisatawan ingin membawa pulang kenang-kenangan kecil dari Penglipuran. Produk apa yang paling sesuai?', 'en' => 'A tourist wants to bring home a small memento from Penglipuran. Which product fits best?'],
                    'options' => [
                        ['text' => ['id' => 'Suvenir kerajinan bambu kecil', 'en' => 'Small bamboo craft souvenir'], 'correct' => true, 'explanation' => ['id' => 'Benar! Suvenir kecil praktis dibawa pulang wisatawan.', 'en' => 'Correct! A small souvenir is practical for tourists to bring home.']],
                        ['text' => ['id' => 'Gazebo bambu besar', 'en' => 'Large bamboo gazebo'], 'correct' => false, 'explanation' => ['id' => 'Terlalu besar untuk dibawa pulang sebagai kenang-kenangan.', 'en' => 'Too large to bring home as a memento.']],
                        ['text' => ['id' => 'Perabot dapur berukuran besar', 'en' => 'Large kitchen furniture'], 'correct' => false, 'explanation' => ['id' => 'Tidak praktis sebagai suvenir perjalanan.', 'en' => 'Not practical as a travel souvenir.']],
                    ],
                ],
            ],
        ]);
    }

    private function mission(TourRoutePoint $point, int $order, string $type, array $title, array $config, int $points = 100): void
    {
        RouteMission::updateOrCreate(
            ['tour_route_point_id' => $point->id, 'order' => $order],
            ['type' => $type, 'title' => $title, 'config' => $config, 'points' => $points],
        );
    }
}
