<?php

namespace Database\Seeders;

use App\Models\CulturalObject;
use App\Models\RouteMission;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Rute 1 "Penglipuran Heritage Quest" — 6 titik bermain + layar rekap.
 * Konten Bahasa Indonesia mengikuti PDF "Rute Eduwisata" (verbatim di storytelling);
 * terjemahan EN dibuat otomatis dan ditandai untuk direview manusia.
 *
 * Idempotent: aman dijalankan berulang (objek dicocokkan fuzzy by name agar
 * reuse CulturalObject hasil digitalisasi yang sudah ada, titik di-updateOrCreate
 * per order, misi di-rebuild deterministik).
 */
class Route1HeritageQuestSeeder extends Seeder
{
    public function run(): void
    {
        $objects = [
            'gerbang' => $this->ensureObject(['gerbang desa', 'gapura desa', 'village gate', 'main gate'], [
                'name' => ['id' => 'Gerbang Desa Penglipuran', 'en' => 'Penglipuran Village Gate'],
                'short_description' => [
                    'id' => 'Pintu masuk utama Desa Penglipuran, titik awal perjalanan Heritage Quest.',
                    'en' => 'The main entrance of Penglipuran Village, starting point of the Heritage Quest.',
                ],
                'description' => [
                    'id' => 'Gerbang Desa Penglipuran menyambut wisatawan menuju kawasan desa adat dengan tata ruang Tri Mandala yang tertata rapi. Dari sini pengunjung memulai check-in eduwisata, menonton video pengenalan desa, dan mengakses peta digital.',
                    'en' => 'The Penglipuran Village Gate welcomes visitors into the traditional village with its neatly arranged Tri Mandala layout. From here visitors check in for the edutourism route, watch the village introduction video, and access the digital map.',
                ],
                'category' => 'pawongan',
            ], -8.42340, 115.35920),

            'koridor' => $this->ensureObject(['koridor desa', 'village corridor'], [
                'name' => ['id' => 'Koridor Desa Penglipuran', 'en' => 'Penglipuran Village Corridor'],
                'short_description' => [
                    'id' => 'Jalan utama desa dengan deretan angkul-angkul dan tata ruang tradisional.',
                    'en' => 'The village main street lined with angkul-angkul gates and traditional spatial layout.',
                ],
                'description' => [
                    'id' => 'Koridor utama Desa Penglipuran memperlihatkan pola tata ruang desa yang khas: deretan rumah dengan angkul-angkul seragam, tanaman Loloh Cemcem, kerajinan anyaman bambu, dan pelinggih di tiap pekarangan.',
                    'en' => 'The main corridor of Penglipuran shows the village\'s distinctive spatial pattern: rows of houses with uniform angkul-angkul gates, Loloh Cemcem plants, woven bamboo crafts, and family shrines in every yard.',
                ],
                'category' => 'pawongan',
            ], -8.42280, 115.35915),

            'merajan' => $this->ensureObject(['merajan', 'sanggah'], [
                'name' => ['id' => 'Merajan Rumah Tradisional', 'en' => 'Traditional House Merajan'],
                'short_description' => [
                    'id' => 'Tempat pemujaan leluhur yang terdapat di setiap rumah tradisional Bali.',
                    'en' => 'The ancestral shrine found in every traditional Balinese home.',
                ],
                'description' => [
                    'id' => 'Merajan (disebut juga Sanggah) adalah area suci keluarga di setiap rumah tradisional Bali, tempat pemujaan leluhur. Di dalamnya terdapat beberapa bangunan suci seperti Sanggah Kemulan, Padmasana, dan Piyasan yang masing-masing memiliki fungsi tersendiri.',
                    'en' => 'The Merajan (also called Sanggah) is the family\'s sacred compound in every traditional Balinese house, dedicated to ancestor worship. It contains several shrines such as the Sanggah Kemulan, Padmasana, and Piyasan, each with its own function.',
                ],
                'category' => 'parahyangan',
            ], -8.42230, 115.35900),

            'kulkul' => $this->ensureObject(['kulkul'], [
                'name' => ['id' => 'Bale Kulkul', 'en' => 'Bale Kulkul'],
                'short_description' => [
                    'id' => 'Menara kentongan tradisional, alat komunikasi masyarakat adat.',
                    'en' => 'The traditional slit-drum tower, a communication device of the customary community.',
                ],
                'description' => [
                    'id' => 'Bale Kulkul adalah bangunan menara tempat kulkul (kentongan bambu/kayu) digantung. Pola bunyi kulkul yang berbeda memiliki makna berbeda dalam kehidupan masyarakat adat: tanda bahaya, panggilan gotong royong, hingga penanda upacara.',
                    'en' => 'The Bale Kulkul is a tower where the kulkul (bamboo/wooden slit drum) hangs. Different beating patterns carry different meanings in customary life: danger alerts, calls for communal work, and ceremony signals.',
                ],
                'category' => 'pawongan',
            ], -8.42180, 115.35910),

            'relief' => $this->ensureObject(['relief'], [
                'name' => ['id' => 'Relief Sejarah Desa Penglipuran', 'en' => 'Penglipuran History Relief'],
                'short_description' => [
                    'id' => 'Relief yang menggambarkan perjalanan sejarah Desa Penglipuran.',
                    'en' => 'A relief depicting the historical journey of Penglipuran Village.',
                ],
                'description' => [
                    'id' => 'Relief sejarah menggambarkan kronologi Desa Penglipuran: migrasi leluhur dari Desa Bayung Gede, lahirnya nama Penglipuran ("pengeling pura" — mengenang tempat leluhur), hingga penetapan sebagai desa wisata.',
                    'en' => 'The history relief depicts Penglipuran\'s chronology: the ancestors\' migration from Bayung Gede, the birth of the name Penglipuran ("pengeling pura" — remembering the ancestral place), up to its designation as a tourism village.',
                ],
                'category' => 'pawongan',
            ], -8.42120, 115.35920),

            'bambu' => $this->ensureObject(['hutan bambu', 'bamboo forest', 'bamboo'], [
                'name' => ['id' => 'Hutan Bambu Penglipuran', 'en' => 'Penglipuran Bamboo Forest'],
                'short_description' => [
                    'id' => 'Kawasan hutan bambu seluas ±45 hektar yang dijaga secara adat.',
                    'en' => 'A ±45-hectare bamboo forest protected by customary law.',
                ],
                'description' => [
                    'id' => 'Hutan bambu Penglipuran menjadi penyangga ekologis desa: menjaga sumber air, mencegah erosi, dan menyediakan bahan bangunan serta kerajinan. Pemanfaatannya diatur secara adat agar tetap lestari.',
                    'en' => 'The Penglipuran bamboo forest is the village\'s ecological buffer: it protects water sources, prevents erosion, and provides building and craft materials. Its use is regulated by customary law to keep it sustainable.',
                ],
                'category' => 'pawongan',
            ], -8.42050, 115.35980),
        ];

        $route = $this->ensureRoute();

        $points = $this->ensurePoints($route, $objects);

        $this->rebuildMissions($points);

        $this->command?->info('Rute 1 "Penglipuran Heritage Quest" seeded: '.count($points).' points.');
    }

    /**
     * Reuse an existing CulturalObject when its name (any locale) matches one of
     * the keywords (digitized objects may already exist), otherwise create it.
     */
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
            $existing->update($attributes);

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
        $route = TourRoute::where('gamification_key', 'heritage_quest')->first()
            ?? TourRoute::all()->first(
                fn ($r) => str_contains(mb_strtolower(translateValue($r->name, 'en')), 'penglipuran heritage quest')
            );

        $attributes = [
            'name' => ['id' => 'Penglipuran Heritage Quest', 'en' => 'Penglipuran Heritage Quest'],
            'description' => [
                'id' => 'Menjelajahi Warisan Budaya Desa Penglipuran — Digital Guided Tour, QR Code Exploration, AR Learning, dan Gamification dalam 90 menit melalui 7 pemberhentian.',
                'en' => 'Exploring the Cultural Heritage of Penglipuran Village — a 90-minute Digital Guided Tour with QR Code Exploration, AR Learning, and Gamification across 7 stops.',
            ],
            'difficulty' => 'easy',
            'gamification_key' => 'heritage_quest',
            'estimated_duration_minutes' => 90,
            'distance_meters' => 1200,
            'is_active' => true,
        ];

        if ($route) {
            $route->update($attributes);

            return $route;
        }

        return TourRoute::create($attributes);
    }

    /**
     * 6 playable points (PDF point 7 "Penutup" is the recap screen, not a physical point).
     * qr_code_token is only set for points whose locationable has no AR marker —
     * points with a marker reuse that marker's existing QR sticker (one QR per location).
     *
     * @param  array<string, CulturalObject>  $objects
     * @return array<int, TourRoutePoint>
     */
    private function ensurePoints(TourRoute $route, array $objects): array
    {
        $definitions = [
            1 => ['object' => $objects['gerbang'], 'minutes' => 10, 'storytelling' => [
                'id' => 'Selamat datang di Penglipuran Heritage Quest! Lakukan check-in eduwisata, pindai QR Code pada peta desa, tonton video pengenalan Desa Penglipuran, lalu gunakan peta digital sebagai panduan perjalananmu.',
                'en' => 'Welcome to the Penglipuran Heritage Quest! Check in for the edutourism route, scan the QR code on the village map, watch the Penglipuran introduction video, then use the digital map as your travel guide.',
            ]],
            2 => ['object' => $objects['koridor'], 'minutes' => 15, 'storytelling' => [
                'id' => 'Gunakan peta digital untuk mengidentifikasi pola tata ruang desa, lalu amati objek-objek budaya di sepanjang koridor: angkul-angkul, tanaman Loloh Cemcem, anyaman bambu, dan pelinggih.',
                'en' => 'Use the digital map to identify the village\'s spatial layout, then observe the cultural objects along the corridor: angkul-angkul gates, Loloh Cemcem plants, woven bamboo, and family shrines.',
            ]],
            3 => ['object' => $objects['merajan'], 'minutes' => 15, 'storytelling' => [
                'id' => 'Pindai QR Code untuk menampilkan model 3D/AR Merajan, kemudian pelajari fungsi setiap bangunan suci keluarga melalui fitur interaktif.',
                'en' => 'Scan the QR code to display the 3D/AR model of the Merajan, then learn the function of each family shrine through the interactive feature.',
            ]],
            4 => ['object' => $objects['kulkul'], 'minutes' => 15, 'storytelling' => [
                'id' => 'Dengarkan simulasi berbagai bunyi kulkul dan pelajari fungsi masing-masing bunyi dalam kehidupan masyarakat adat.',
                'en' => 'Listen to the simulation of various kulkul beats and learn the function of each pattern in customary community life.',
            ]],
            5 => ['object' => $objects['relief'], 'minutes' => 15, 'storytelling' => [
                'id' => 'Amati relief secara langsung sambil menyimak narasi sejarah desa. Temukan fakta-fakta sejarah yang tersembunyi di sekitar relief.',
                'en' => 'Observe the relief up close while following the village history narration. Find the historical facts hidden around the relief.',
            ]],
            6 => ['object' => $objects['bambu'], 'minutes' => 15, 'storytelling' => [
                'id' => 'Jelajahi kawasan hutan bambu sambil mengakses informasi mengenai jenis bambu, fungsi ekologis, dan pemanfaatannya oleh masyarakat.',
                'en' => 'Explore the bamboo forest while accessing information about bamboo species, their ecological functions, and how the community uses them.',
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
                    'qr_code_token' => $hasArMarker ? null : 'EDU-R1-P'.$order,
                ]
            );
        }

        // Drop leftover points beyond order 6 from previous seeds.
        TourRoutePoint::where('tour_route_id', $route->id)->where('order', '>', 6)->delete();

        return $points;
    }

    /**
     * Mission 1 "Unlock the Village": 5 MCQs, as a RouteMission type 'quiz'.
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
     * Missions per point. Rebuilt deterministically on every run.
     *
     * @param  array<int, TourRoutePoint>  $points
     */
    private function rebuildMissions(array $points): void
    {
        // Titik 1 — Mission 1 "Unlock the Village": 5 MCQs.
        $this->mission($points[1], 1, 'quiz', ['id' => 'Buka Gerbang Desa', 'en' => 'Unlock the Village'], $this->pointOneQuizConfig(), 500);

        // Titik 2 — Mission 2 "Heritage Explorer": scavenger hunt + matching.
        // MVP: "memotret objek" diganti memilih objek asli dari grid (foto lapangan belum tersedia,
        // kartu memakai label — unggah foto asli melalui admin builder begitu tersedia dari tim lapangan).
        $this->mission($points[2], 1, 'matching', ['id' => 'Heritage Explorer: Temukan Objeknya', 'en' => 'Heritage Explorer: Find the Objects'], [
            'mode' => 'pick',
            'prompt' => ['id' => 'Digital Treasure Hunt! Temukan 4 objek budaya asli Penglipuran di antara pilihan berikut. Hati-hati, ada pengecoh!', 'en' => 'Digital Treasure Hunt! Find the 4 authentic Penglipuran cultural objects below. Watch out for decoys!'],
            'pick_count' => 4,
            'items' => [
                ['label' => ['id' => 'Angkul-angkul', 'en' => 'Angkul-angkul'], 'correct' => true, 'explanation' => ['id' => 'Angkul-angkul adalah gerbang khas di depan setiap rumah Penglipuran.', 'en' => 'Angkul-angkul are the distinctive gates in front of every Penglipuran house.']],
                ['label' => ['id' => 'Tanaman Loloh Cemcem', 'en' => 'Loloh Cemcem plant'], 'correct' => true, 'explanation' => ['id' => 'Daun cemcem digunakan untuk minuman herbal penyegar khas desa.', 'en' => 'Cemcem leaves are used for the village’s signature refreshing herbal drink.']],
                ['label' => ['id' => 'Anyaman bambu', 'en' => 'Woven bamboo'], 'correct' => true, 'explanation' => ['id' => 'Anyaman bambu adalah kerajinan tangan warga dari hutan adat.', 'en' => 'Woven bamboo is a handicraft made by residents from the customary forest.']],
                ['label' => ['id' => 'Pelinggih', 'en' => 'Family shrine (pelinggih)'], 'correct' => true, 'explanation' => ['id' => 'Pelinggih adalah bangunan suci kecil untuk persembahyangan keluarga.', 'en' => 'Pelinggih is a small shrine for family worship.']],
                ['label' => ['id' => 'Pagar beton modern', 'en' => 'Modern concrete fence'], 'correct' => false, 'explanation' => ['id' => 'Pagar beton modern bukan bagian dari arsitektur tradisional Penglipuran.', 'en' => 'Modern concrete fences are not part of traditional Penglipuran architecture.']],
                ['label' => ['id' => 'Antena parabola', 'en' => 'Satellite dish'], 'correct' => false, 'explanation' => ['id' => 'Antena parabola adalah teknologi modern yang tidak menjadi ciri khas desa adat.', 'en' => 'Satellite dishes are modern technology, not a feature of the traditional village.']],
                ['label' => ['id' => 'Lampu neon', 'en' => 'Neon light'], 'correct' => false, 'explanation' => ['id' => 'Lampu neon tidak termasuk elemen tradisional tata ruang desa.', 'en' => 'Neon lights are not a traditional element of the village layout.']],
                ['label' => ['id' => 'Patung beton modern', 'en' => 'Modern concrete statue'], 'correct' => false, 'explanation' => ['id' => 'Patung beton modern bukan bagian dari penataan tradisional Penglipuran.', 'en' => 'Modern concrete statues are not part of Penglipuran’s traditional arrangement.']],
            ],
        ]);

        $this->mission($points[2], 2, 'matching', ['id' => 'Heritage Explorer: Cocokkan Objeknya', 'en' => 'Heritage Explorer: Match the Objects'], [
            'mode' => 'match',
            'prompt' => ['id' => 'Semua objek ditemukan! Sekarang cocokkan setiap objek dengan fungsinya.', 'en' => 'All objects found! Now match each object with its function.'],
            'pairs' => [
                ['left' => ['id' => 'Angkul-angkul', 'en' => 'Angkul-angkul'], 'right' => ['id' => 'Gerbang masuk khas di depan setiap rumah', 'en' => 'The distinctive entrance gate of every house'], 'explanation' => ['id' => 'Angkul-angkul menandai batas antara ruang publik dan privat rumah tradisional.', 'en' => 'Angkul-angkul marks the boundary between public and private space in a traditional house.']],
                ['left' => ['id' => 'Loloh Cemcem', 'en' => 'Loloh Cemcem'], 'right' => ['id' => 'Minuman herbal tradisional dari daun cemcem', 'en' => 'Traditional herbal drink from cemcem leaves'], 'explanation' => ['id' => 'Loloh Cemcem adalah minuman khas yang menyegarkan dan menjadi warisan kuliner desa.', 'en' => 'Loloh Cemcem is a signature refreshing drink and part of the village’s culinary heritage.']],
                ['left' => ['id' => 'Anyaman bambu', 'en' => 'Woven bamboo'], 'right' => ['id' => 'Kerajinan tangan warga dari bambu hutan adat', 'en' => 'Handicraft made from customary forest bamboo'], 'explanation' => ['id' => 'Anyaman bambu menunjukkan pemanfaatan hutan adat secara lestari.', 'en' => 'Woven bamboo shows the sustainable use of the customary forest.']],
                ['left' => ['id' => 'Pelinggih', 'en' => 'Pelinggih'], 'right' => ['id' => 'Bangunan suci kecil untuk persembahyangan keluarga', 'en' => 'Small shrine for family worship'], 'explanation' => ['id' => 'Pelinggih mengingatkan pentingnya spiritualitas dan hubungan dengan leluhur.', 'en' => 'Pelinggih reminds us of spirituality and the connection with ancestors.']],
            ],
        ]);

        // Titik 3 — Mission 3 "Sacred Puzzle": susun tata letak Merajan + teka-teki (verbatim PDF).
        $this->mission($points[3], 1, 'matching', ['id' => 'Sacred Puzzle: Tata Letak Merajan', 'en' => 'Sacred Puzzle: The Merajan Layout'], [
            'mode' => 'match',
            'prompt' => ['id' => 'Susun puzzle tata letak Merajan: cocokkan setiap bangunan suci dengan fungsinya.', 'en' => 'Solve the Merajan layout puzzle: match each shrine with its function.'],
            'pairs' => [
                ['left' => ['id' => 'Sanggah Kemulan', 'en' => 'Sanggah Kemulan'], 'right' => ['id' => 'Pemujaan roh leluhur keluarga (rong tiga)', 'en' => 'Worship of family ancestors (three chambers)'], 'explanation' => ['id' => 'Sanggah Kemulan menjadi pusat pemujaan leluhur dalam kompleks rumah Bali.', 'en' => 'Sanggah Kemulan is the center of ancestor worship within a Balinese house compound.']],
                ['left' => ['id' => 'Padmasana', 'en' => 'Padmasana'], 'right' => ['id' => 'Pemujaan Ida Sang Hyang Widhi Wasa', 'en' => 'Worship of Ida Sang Hyang Widhi Wasa'], 'explanation' => ['id' => 'Padmasana melambangkan kesucian tertinggi dan hubungan manusia dengan Sang Pencipta.', 'en' => 'Padmasana symbolizes the highest sanctity and the human connection to the Creator.']],
                ['left' => ['id' => 'Piyasan', 'en' => 'Piyasan'], 'right' => ['id' => 'Tempat menata sesajen dan sarana upacara', 'en' => 'Place to arrange offerings and ceremonial items'], 'explanation' => ['id' => 'Piyasan berfungsi menyiapkan persembahan sebelum upacara keluarga atau desa.', 'en' => 'Piyasan is used to prepare offerings before family or village ceremonies.']],
            ],
        ]);

        $this->mission($points[3], 2, 'riddle', ['id' => 'Sacred Puzzle: Teka-Teki Suci', 'en' => 'Sacred Puzzle: The Sacred Riddle'], [
            'riddle' => ['id' => 'Aku merupakan tempat pemujaan leluhur yang terdapat di setiap rumah tradisional Bali. Siapakah aku?', 'en' => 'I am the place of ancestor worship found in every traditional Balinese home. Who am I?'],
            'answers' => ['merajan', 'sanggah', 'sanggah merajan'],
            'hint' => ['id' => 'Kamu sedang berdiri di dekatnya sekarang.', 'en' => 'You are standing near it right now.'],
            'explanation' => ['id' => 'Merajan atau Sanggah adalah kompleks suci terpenting dalam rumah tradisional Bali, tempat keluarga memuja leluhur dan menjaga hubungan spiritual.', 'en' => 'The Merajan or Sanggah is the most sacred compound in a traditional Balinese house, where the family worships ancestors and maintains spiritual connection.'],
        ]);

        // Titik 4 — Mission 4 "Sound Detective".
        // PLACEHOLDER AUDIO (keputusan tim ✅): rekaman kulkul asli belum ada — field 'audio' null,
        // deskripsi teks dipakai sementara. Isi 'audio' dengan path rekaman begitu tersedia dari tim lapangan.
        $this->mission($points[4], 1, 'matching', ['id' => 'Sound Detective', 'en' => 'Sound Detective'], [
            'mode' => 'match',
            'prompt' => ['id' => 'Tiga pola bunyi kulkul memiliki makna berbeda. Cocokkan setiap pola bunyi dengan fungsinya! (Audio asli menyusul — sementara gunakan deskripsi bunyi.)', 'en' => 'Three kulkul beating patterns carry different meanings. Match each pattern with its function! (Real audio coming soon — text descriptions for now.)'],
            'pairs' => [
                ['left' => ['id' => '🔊 Bunyi cepat bertalu-talu', 'en' => '🔊 Fast, insistent beats'], 'right' => ['id' => 'Tanda bahaya / keadaan darurat', 'en' => 'Danger / emergency alert'], 'audio' => null, 'explanation' => ['id' => 'Bunyi cepat menjadi peringatan agar warga segera waspada dan berkumpul.', 'en' => 'Fast beats serve as a warning for residents to be alert and gather.']],
                ['left' => ['id' => '🔉 Bunyi lambat berirama', 'en' => '🔉 Slow, rhythmic beats'], 'right' => ['id' => 'Panggilan rapat dan gotong royong warga', 'en' => 'Call for village meetings and communal work'], 'audio' => null, 'explanation' => ['id' => 'Bunyi lambat mengundang warga untuk musyawarah atau kerja bersama.', 'en' => 'Slow beats invite residents to discussion or communal work.']],
                ['left' => ['id' => '🔈 Bunyi sedang teratur', 'en' => '🔈 Steady, moderate beats'], 'right' => ['id' => 'Penanda upacara adat dimulai', 'en' => 'Signal that a ceremony is starting'], 'audio' => null, 'explanation' => ['id' => 'Bunyi teratur memberi tahu warga bahwa upacara adat akan segera dimulai.', 'en' => 'Steady beats inform residents that a customary ceremony is about to begin.']],
            ],
        ]);

        // Titik 5 — Mission 5 "History Hunter": temukan 5 fakta (reveal) lalu susun kronologi.
        $this->mission($points[5], 1, 'sequence', ['id' => 'History Hunter', 'en' => 'History Hunter'], [
            'reveal_first' => true,
            'prompt' => ['id' => 'Temukan 5 fakta sejarah yang tersembunyi, lalu susun kronologi perkembangan Desa Penglipuran!', 'en' => 'Find the 5 hidden historical facts, then arrange the chronology of Penglipuran\'s development!'],
            'explanation' => ['id' => 'Kronologi ini menggambarkan perjalanan Desa Penglipuran dari migrasi leluhur, lahirnya nama desa, tata ruang tradisional, penetapan desa wisata, hingga pengakuan dunia.', 'en' => 'This chronology depicts Penglipuran Village\'s journey from ancestral migration, the origin of its name, traditional spatial layout, tourism village designation, to world recognition.'],
            'items' => [
                ['text' => ['id' => 'Leluhur warga bermigrasi dari Desa Bayung Gede pada masa Kerajaan Bangli.', 'en' => 'The ancestors migrated from Bayung Gede Village during the Bangli Kingdom era.']],
                ['text' => ['id' => 'Nama "Penglipuran" lahir dari kata "pengeling pura" — mengenang tempat leluhur.', 'en' => 'The name "Penglipuran" was born from "pengeling pura" — remembering the ancestral place.']],
                ['text' => ['id' => 'Warga menata desa dengan konsep Tri Mandala dan angkul-angkul yang seragam.', 'en' => 'The residents arranged the village with the Tri Mandala concept and uniform angkul-angkul.']],
                ['text' => ['id' => 'Penglipuran ditetapkan sebagai desa wisata pada tahun 1993.', 'en' => 'Penglipuran was designated a tourism village in 1993.']],
                ['text' => ['id' => 'Penglipuran meraih pengakuan dunia sebagai salah satu desa terbersih di dunia.', 'en' => 'Penglipuran gained world recognition as one of the cleanest villages on earth.']],
            ],
        ]);

        // Titik 6 — Mission 6 "Eco Ranger Challenge": word search → riddle → decision (3 komponen berantai).
        $this->mission($points[6], 1, 'word_search', ['id' => 'Eco Ranger: Cari Kata Konservasi', 'en' => 'Eco Ranger: Conservation Word Search'], [
            'prompt' => ['id' => 'Temukan kata-kata bertema konservasi yang tersembunyi di dalam grid!', 'en' => 'Find the hidden conservation-themed words in the grid!'],
            'grid_size' => 8,
            'words' => ['BAMBU', 'HUTAN', 'LESTARI', 'ALAM', 'AKAR', 'AIR'],
            'explanation' => ['id' => 'Hutan bambu Penglipuran bukan sekadar lahan, melainkan penjaga keseimbangan air, tanah, dan iklim mikro desa. Konservasi ini dilakukan bersama oleh warga melalui aturan adat.', 'en' => 'The Penglipuran bamboo forest is more than just land — it safeguards the village\'s water balance, soil, and microclimate. This conservation is carried out collectively by residents through customary law.'],
        ]);

        $this->mission($points[6], 2, 'riddle', ['id' => 'Eco Ranger: Teka-Teki Lingkungan', 'en' => 'Eco Ranger: Environment Riddle'], [
            'riddle' => ['id' => 'Aku berdiri tinggi berumpun, batangku berongga, dan akarku menjaga air serta tanah desa ini. Siapakah aku?', 'en' => 'I stand tall in clumps, my stem is hollow, and my roots guard this village\'s water and soil. Who am I?'],
            'answers' => ['bambu', 'pohon bambu', 'hutan bambu', 'bamboo'],
            'explanation' => ['id' => 'Akar bambu yang lebat menahan tanah dan menjaga cadangan air, sehingga hutan bambu menjadi paru-paru dan penyaring alami bagi Desa Penglipuran.', 'en' => 'Bamboo\'s dense roots hold the soil and maintain water reserves, making the bamboo forest a natural lung and filter for Penglipuran Village.'],
        ]);

        $this->mission($points[6], 3, 'decision', ['id' => 'Eco Decision Game', 'en' => 'Eco Decision Game'], [
            'scenarios' => [
                [
                    'text' => ['id' => 'Kamu melihat sampah plastik tergeletak di jalur hutan bambu. Apa tindakan terbaikmu?', 'en' => 'You spot plastic litter on the bamboo forest trail. What is your best action?'],
                    'options' => [
                        ['text' => ['id' => 'Membiarkannya, itu bukan sampahku', 'en' => 'Leave it — it\'s not my litter'], 'correct' => false, 'explanation' => ['id' => 'Sampah plastik butuh ratusan tahun terurai dan merusak ekosistem hutan.', 'en' => 'Plastic takes centuries to decompose and damages the forest ecosystem.']],
                        ['text' => ['id' => 'Memungutnya dan membuang ke tempat sampah', 'en' => 'Pick it up and put it in a bin'], 'correct' => true, 'explanation' => ['id' => 'Benar! Wisatawan ikut bertanggung jawab menjaga kebersihan desa terbersih di dunia.', 'en' => 'Correct! Visitors share responsibility for keeping the world\'s cleanest village clean.']],
                        ['text' => ['id' => 'Menyembunyikannya di balik semak', 'en' => 'Hide it behind a bush'], 'correct' => false, 'explanation' => ['id' => 'Menyembunyikan sampah tidak menghilangkan dampaknya bagi lingkungan.', 'en' => 'Hiding litter does not remove its impact on the environment.']],
                    ],
                ],
                [
                    'text' => ['id' => 'Temanmu ingin mengukir nama di batang bambu sebagai kenang-kenangan. Apa yang kamu lakukan?', 'en' => 'Your friend wants to carve their name on a bamboo stem as a memento. What do you do?'],
                    'options' => [
                        ['text' => ['id' => 'Ikut mengukir nama juga', 'en' => 'Carve my name too'], 'correct' => false, 'explanation' => ['id' => 'Luka pada batang membuka jalan penyakit dan merusak pertumbuhan bambu.', 'en' => 'Wounds on the stem invite disease and harm the bamboo\'s growth.']],
                        ['text' => ['id' => 'Mengingatkan bahwa itu merusak dan dilarang adat', 'en' => 'Remind them it damages the bamboo and is forbidden by adat'], 'correct' => true, 'explanation' => ['id' => 'Benar! Hutan bambu Penglipuran dijaga aturan adat — mengukir batang merusak tanaman.', 'en' => 'Correct! The bamboo forest is protected by customary rules — carving damages the plants.']],
                        ['text' => ['id' => 'Diam saja dan memotretnya', 'en' => 'Stay quiet and take a photo'], 'correct' => false, 'explanation' => ['id' => 'Diam berarti membiarkan kerusakan terjadi di depan mata.', 'en' => 'Staying silent lets the damage happen before your eyes.']],
                    ],
                ],
                [
                    'text' => ['id' => 'Kamu menemukan rebung (tunas bambu muda) di pinggir jalur wisata. Apa tindakan terbaikmu?', 'en' => 'You find a bamboo shoot at the edge of the trail. What is your best action?'],
                    'options' => [
                        ['text' => ['id' => 'Mencabutnya untuk oleh-oleh', 'en' => 'Pull it out as a souvenir'], 'correct' => false, 'explanation' => ['id' => 'Rebung adalah calon bambu baru — mencabutnya menghentikan regenerasi hutan.', 'en' => 'Shoots are future bamboo — pulling them stops the forest\'s regeneration.']],
                        ['text' => ['id' => 'Membiarkannya tumbuh menjadi bambu baru', 'en' => 'Let it grow into new bamboo'], 'correct' => true, 'explanation' => ['id' => 'Benar! Regenerasi alami menjaga hutan bambu tetap lestari untuk generasi berikutnya.', 'en' => 'Correct! Natural regeneration keeps the bamboo forest sustainable for the next generation.']],
                        ['text' => ['id' => 'Menandainya dengan pita agar mudah ditemukan lagi', 'en' => 'Mark it with a ribbon to find it again'], 'correct' => false, 'explanation' => ['id' => 'Menandai tanaman liar tetap mengganggu; biarkan alam bekerja tanpa campur tangan.', 'en' => 'Marking wild plants is still interference; let nature work undisturbed.']],
                    ],
                ],
            ],
        ]);
    }

    private function mission(TourRoutePoint $point, int $order, string $type, array $title, array $config, int $points = 100): void
    {
        // updateOrCreate on (point, order) keeps mission IDs stable across reseeds —
        // completeMission() and RouteSession.missions_completed reference these IDs.
        RouteMission::updateOrCreate(
            ['tour_route_point_id' => $point->id, 'order' => $order],
            ['type' => $type, 'title' => $title, 'config' => $config, 'points' => $points],
        );
    }
}
