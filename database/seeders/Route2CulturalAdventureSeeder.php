<?php

namespace Database\Seeders;

use App\Models\CulturalObject;
use App\Models\RouteMission;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Rute 2 "Penglipuran Cultural Adventure: Mystery of the Living Tradition" — 5 titik
 * bermain + layar rekap. Titik 1 & 2 (Gerbang Desa, Koridor Desa) reuse CulturalObject
 * yang sama dengan Rute 1 (lokasi fisik sama), tapi Titik 1's quiz mission adalah
 * RouteMission independen milik rute ini (satu misi = satu titik = satu rute).
 *
 * Konten Bahasa Indonesia mengikuti PDF "Rute Eduwisata" (ClickUp Doc `2kzkxyn8-738`);
 * detail yang tidak dirinci PDF (resep Loloh Cemcem, skenario Karang Memadu, riddle akhir,
 * daftar kartu scavenger hunt) diimprovisasi mengikuti tema & lokasi — perlu review manusia.
 * Terjemahan EN dibuat otomatis, juga perlu review.
 *
 * Idempotent: sama pola dengan Route1HeritageQuestSeeder (ensureObject fuzzy-match,
 * updateOrCreate per order/point, mission() updateOrCreate per (point, order) — ID stabil
 * antar reseed, dibutuhkan karena RouteMission direferensikan oleh missions_completed).
 */
class Route2CulturalAdventureSeeder extends Seeder
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
                    'id' => 'Koridor utama Desa Penglipuran memperlihatkan pola tata ruang desa yang khas: deretan rumah dengan angkul-angkul seragam, bale banjar, wantilan, dan pura penataran.',
                    'en' => 'The main corridor of Penglipuran shows the village\'s distinctive spatial pattern: rows of houses with uniform angkul-angkul gates, the bale banjar, wantilan hall, and penataran temple.',
                ],
                'category' => 'tradition',
            ], -8.42280, 115.35915),

            'paon' => $this->ensureObject(['paon', 'dapur rumah tradisional', 'traditional kitchen'], [
                'name' => ['id' => 'Dapur Rumah Tradisional (Paon)', 'en' => 'Traditional House Kitchen (Paon)'],
                'short_description' => [
                    'id' => 'Dapur tradisional Bali tempat memasak makanan khas Desa Penglipuran.',
                    'en' => 'The traditional Balinese kitchen where Penglipuran\'s signature dishes are cooked.',
                ],
                'description' => [
                    'id' => 'Paon adalah dapur tradisional rumah Bali dengan tungku kayu bakar. Di sinilah warga memasak makanan khas seperti Loloh Cemcem, minuman herbal penyegar dari daun cemcem.',
                    'en' => 'The Paon is the traditional kitchen of a Balinese house, with a wood-fired stove. Here residents cook signature dishes such as Loloh Cemcem, a refreshing herbal drink made from cemcem leaves.',
                ],
                'category' => 'tradition',
            ], -8.42250, 115.35905),

            'karang_memadu' => $this->ensureObject(['karang memadu'], [
                'name' => ['id' => 'Karang Memadu', 'en' => 'Karang Memadu'],
                'short_description' => [
                    'id' => 'Area khusus yang mencerminkan sistem hukum adat Desa Penglipuran.',
                    'en' => 'A special area reflecting Penglipuran\'s customary legal system.',
                ],
                'description' => [
                    'id' => 'Karang Memadu adalah sebidang tanah yang mencerminkan berlakunya hukum adat (awig-awig) di Desa Penglipuran. Keberadaannya menjadi pengingat bahwa aturan adat dijunjung tinggi dan berlaku bagi siapa saja.',
                    'en' => 'Karang Memadu is a plot of land reflecting the enforcement of customary law (awig-awig) in Penglipuran. Its existence is a reminder that customary rules are upheld and apply to everyone.',
                ],
                'category' => 'tradition',
            ], -8.42150, 115.35895),

            'makam_pahlawan' => $this->ensureObject(['taman makam pahlawan', 'heroes cemetery'], [
                'name' => ['id' => 'Taman Makam Pahlawan', 'en' => 'Heroes Cemetery Park'],
                'short_description' => [
                    'id' => 'Taman yang mengenang jasa para pejuang wilayah Bangli.',
                    'en' => 'A park commemorating the struggle of Bangli\'s freedom fighters.',
                ],
                'description' => [
                    'id' => 'Taman Makam Pahlawan mengenang perjuangan warga sekitar Bangli dalam mempertahankan kemerdekaan. Setiap tahun, upacara penghormatan diadakan di taman ini.',
                    'en' => 'The Heroes Cemetery Park commemorates the struggle of the people around Bangli in defending independence. Every year, a commemoration ceremony is held here.',
                ],
                'category' => 'tradition',
            ], -8.42090, 115.35860),
        ];

        $route = $this->ensureRoute();

        $points = $this->ensurePoints($route, $objects);

        $this->rebuildMissions($points);

        $this->command?->info('Rute 2 "Penglipuran Cultural Adventure" seeded: '.count($points).' points.');
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
            fn ($r) => str_contains(mb_strtolower(translateValue($r->name, 'en')), 'cultural adventure')
        );

        $attributes = [
            'name' => ['id' => 'Penglipuran Cultural Adventure: Mystery of the Living Tradition', 'en' => 'Penglipuran Cultural Adventure: Mystery of the Living Tradition'],
            'description' => [
                'id' => 'Menjelajahi Kehidupan Sosial, Tradisi, dan Nilai-Nilai Budaya Desa Penglipuran dalam 90 menit melalui 6 pemberhentian.',
                'en' => 'Exploring the Social Life, Traditions, and Cultural Values of Penglipuran Village in 90 minutes across 6 stops.',
            ],
            'difficulty' => 'moderate',
            'gamification_key' => 'cultural_adventure',
            'estimated_duration_minutes' => 90,
            'distance_meters' => 1400,
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
                'id' => 'Selamat datang di Misi Penjaga Warisan Penglipuran! Kumpulkan 5 Heritage Key di sepanjang perjalanan untuk membuka Kotak Warisan Penglipuran di titik terakhir. Jawab 5 kuis cepat untuk mendapatkan Heritage Key pertama.',
                'en' => 'Welcome to the Guardian of Penglipuran Heritage mission! Collect 5 Heritage Keys along the way to unlock the Heritage Chest at the final stop. Answer 5 quick quizzes to earn your first Heritage Key.',
            ]],
            2 => ['object' => $objects['koridor'], 'minutes' => 15, 'storytelling' => [
                'id' => 'Gunakan peta digital dan QR Code untuk mengenali ciri khas permukiman Desa Penglipuran, lalu selesaikan Cultural Scavenger Hunt untuk Heritage Key kedua.',
                'en' => 'Use the digital map and QR code to recognize the distinctive features of Penglipuran\'s settlement, then complete the Cultural Scavenger Hunt for your second Heritage Key.',
            ]],
            3 => ['object' => $objects['paon'], 'minutes' => 15, 'storytelling' => [
                'id' => 'Pindai QR Code untuk menonton video aktivitas memasak tradisional, lalu bantu Master Chef Penglipuran menyusun langkah membuat Loloh Cemcem dan pecahkan Mystery Box-nya.',
                'en' => 'Scan the QR code to watch a video of traditional cooking, then help Master Chef Penglipuran arrange the steps to make Loloh Cemcem and solve its Mystery Box.',
            ]],
            4 => ['object' => $objects['karang_memadu'], 'minutes' => 20, 'storytelling' => [
                'id' => 'Pelajari sejarah, fungsi, dan nilai hukum adat Karang Memadu, lalu jadi Judge of Tradition — pilih keputusan yang paling sesuai dengan nilai adat Penglipuran.',
                'en' => 'Learn the history, function, and customary legal value of Karang Memadu, then become the Judge of Tradition — choose the decision that best fits Penglipuran\'s customary values.',
            ]],
            5 => ['object' => $objects['makam_pahlawan'], 'minutes' => 20, 'storytelling' => [
                'id' => 'Dengarkan audio sejarah perjuangan para pahlawan, lalu susun timeline digitalnya sebelum waktu habis untuk memperoleh Heritage Key terakhir.',
                'en' => 'Listen to the audio history of the heroes\' struggle, then arrange the digital timeline before time runs out to earn your final Heritage Key.',
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
                    'qr_code_token' => $hasArMarker ? null : 'EDU-R2-P'.$order,
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
        // Titik 1 — Mission 1: 5 MCQs to earn the first Heritage Key.
        $this->mission($points[1], 1, 'quiz', ['id' => 'Buka Gerbang Desa', 'en' => 'Unlock the Village'], $this->pointOneQuizConfig(), 500);

        // Titik 2 — Mission 2 "Cultural Scavenger Hunt": 8 kartu, 5 benar.
        $this->mission($points[2], 1, 'matching', ['id' => 'Cultural Scavenger Hunt', 'en' => 'Cultural Scavenger Hunt'], [
            'mode' => 'pick',
            'prompt' => ['id' => 'Temukan 5 objek budaya asli di sekitar koridor desa. Hati-hati, ada pengecoh!', 'en' => 'Find the 5 authentic cultural objects around the village corridor. Watch out for decoys!'],
            'pick_count' => 5,
            'items' => [
                ['label' => ['id' => 'Angkul-angkul', 'en' => 'Angkul-angkul'], 'correct' => true, 'explanation' => ['id' => 'Angkul-angkul adalah gerbang khas di depan setiap rumah adat Penglipuran.', 'en' => 'Angkul-angkul is the distinctive gate in front of every traditional Penglipuran house.']],
                ['label' => ['id' => 'Bale Banjar', 'en' => 'Bale Banjar'], 'correct' => true, 'explanation' => ['id' => 'Bale Banjar adalah tempat berkumpulnya warga untuk musyawarah dan gotong royong.', 'en' => 'Bale Banjar is where residents gather for discussion and communal work.']],
                ['label' => ['id' => 'Wantilan', 'en' => 'Wantilan Hall'], 'correct' => true, 'explanation' => ['id' => 'Wantilan adalah balai terbuka untuk pertemuan besar dan kegiatan sosial desa.', 'en' => 'Wantilan is an open hall for large gatherings and village social activities.']],
                ['label' => ['id' => 'Pura Penataran', 'en' => 'Penataran Temple'], 'correct' => true, 'explanation' => ['id' => 'Pura Penataran adalah pura desa tempat warga melaksanakan upacara keagamaan bersama.', 'en' => 'Pura Penataran is the village temple where residents carry out communal religious ceremonies.']],
                ['label' => ['id' => 'Rumah Adat Bata Merah', 'en' => 'Traditional Red-Brick House'], 'correct' => true, 'explanation' => ['id' => 'Rumah tradisional Penglipuran mempertahankan bentuk dan material lokal seperti bata merah serta bambu.', 'en' => 'Traditional Penglipuran houses maintain local forms and materials such as red brick and bamboo.']],
                ['label' => ['id' => 'Warung Modern', 'en' => 'Modern Convenience Store'], 'correct' => false, 'explanation' => ['id' => 'Warung modern bukan ciri khas arsitektur atau sosial tradisional desa.', 'en' => 'A modern convenience store is not a feature of traditional village architecture or social life.']],
                ['label' => ['id' => 'Menara Sinyal', 'en' => 'Signal Tower'], 'correct' => false, 'explanation' => ['id' => 'Menara sinyal adalah infrastruktur modern yang tidak menjadi bagian tata ruang adat.', 'en' => 'A signal tower is modern infrastructure, not part of the customary spatial layout.']],
                ['label' => ['id' => 'Pagar Beton Tinggi', 'en' => 'Tall Concrete Fence'], 'correct' => false, 'explanation' => ['id' => 'Pagar beton tinggi bertentangan dengan keterbukaan dan keseragaman koridor tradisional.', 'en' => 'A tall concrete fence contradicts the openness and uniformity of the traditional corridor.']],
            ],
        ]);

        // Titik 3 — Mission 3 "Master Chef Penglipuran": susun resep + riddle.
        $this->mission($points[3], 1, 'sequence', ['id' => 'Master Chef Penglipuran: Susun Resep', 'en' => 'Master Chef Penglipuran: Arrange the Recipe'], [
            'prompt' => ['id' => 'Susun langkah membuat Loloh Cemcem sesuai urutan yang benar!', 'en' => 'Arrange the steps to make Loloh Cemcem in the correct order!'],
            'explanation' => ['id' => 'Loloh Cemcem adalah minuman herbal khas Penglipuran yang dibuat dari daun cemcem segar. Prosesnya mencerminkan kearifan lokal dalam memanfaatkan tanaman sekitar untuk kesehatan dan kesegaran.', 'en' => 'Loloh Cemcem is a signature Penglipuran herbal drink made from fresh cemcem leaves. The process reflects local wisdom in using surrounding plants for health and refreshment.'],
            'items' => [
                ['text' => ['id' => 'Petik daun cemcem muda yang segar.', 'en' => 'Pick fresh young cemcem leaves.']],
                ['text' => ['id' => 'Cuci bersih daun cemcem.', 'en' => 'Wash the cemcem leaves thoroughly.']],
                ['text' => ['id' => 'Tumbuk halus daun cemcem bersama sedikit air.', 'en' => 'Finely crush the leaves with a little water.']],
                ['text' => ['id' => 'Peras dan saring air tumbukan daun.', 'en' => 'Squeeze and strain the crushed leaf water.']],
                ['text' => ['id' => 'Campurkan dengan air asam, gula aren, dan sedikit garam.', 'en' => 'Mix with tamarind water, palm sugar, and a pinch of salt.']],
                ['text' => ['id' => 'Sajikan dingin sebagai minuman penyegar.', 'en' => 'Serve cold as a refreshing drink.']],
            ],
        ]);

        $this->mission($points[3], 2, 'riddle', ['id' => 'Mystery Box: Teka-Teki Dapur', 'en' => 'Mystery Box: Kitchen Riddle'], [
            'riddle' => ['id' => 'Aku menghangatkan keluarga setiap hari, tetapi aku bukan matahari. Siapakah aku?', 'en' => 'I warm the family every day, but I am not the sun. Who am I?'],
            'answers' => ['tungku', 'dapur', 'perapian', 'paon'],
            'hint' => ['id' => 'Aku ada di ruangan tempat kamu belajar memasak Loloh Cemcem tadi.', 'en' => 'I am in the room where you just learned to cook Loloh Cemcem.'],
            'success_text' => ['id' => 'Benar! Tungku adalah jantung dapur tradisional Bali.', 'en' => 'Correct! The stove is the heart of the traditional Balinese kitchen.'],
            'explanation' => ['id' => 'Tungku kayu bakar di Paon tidak hanya untuk memasak, tetapi juga menjadi pusat kehangatan keluarga dan pelestarian resep turun-temurun.', 'en' => 'The wood-fired stove in the Paon is not only for cooking, but also a center of family warmth and the preservation of inherited recipes.'],
        ]);

        // Titik 4 — Mission 4 "Judge of Tradition": decision game.
        // Konten skenario diimprovisasi (PDF hanya menyebut "beberapa kasus kehidupan masyarakat")
        // — dijaga netral/menghormati adat, perlu review sebelum tampil ke publik.
        $this->mission($points[4], 1, 'decision', ['id' => 'Judge of Tradition', 'en' => 'Judge of Tradition'], [
            'scenarios' => [
                [
                    'text' => ['id' => 'Kamu mendengar bahwa Karang Memadu terkait pelanggaran aturan pernikahan adat. Bagaimana sikap terbaik wisatawan terhadap aturan ini?', 'en' => 'You hear that Karang Memadu relates to violations of customary marriage rules. What is the best attitude for a visitor toward this rule?'],
                    'options' => [
                        ['text' => ['id' => 'Menghormatinya sebagai bagian dari sistem hukum adat setempat', 'en' => 'Respect it as part of the local customary legal system'], 'correct' => true, 'explanation' => ['id' => 'Benar! Wisatawan sebaiknya menghormati awig-awig meski berbeda dari daerah asalnya.', 'en' => 'Correct! Visitors should respect awig-awig even if it differs from their own region.']],
                        ['text' => ['id' => 'Menganggapnya kuno dan perlu dihapus', 'en' => 'Consider it outdated and in need of abolition'], 'correct' => false, 'explanation' => ['id' => 'Menilai sepihak aturan adat tanpa memahami konteksnya kurang tepat bagi tamu desa.', 'en' => 'Judging customary rules one-sidedly without understanding the context is not appropriate for a village guest.']],
                        ['text' => ['id' => 'Mengabaikan dan tidak peduli', 'en' => 'Ignore it and not care'], 'correct' => false, 'explanation' => ['id' => 'Memahami konteks budaya justru memperkaya pengalaman eduwisata.', 'en' => 'Understanding cultural context actually enriches the edutourism experience.']],
                    ],
                ],
                [
                    'text' => ['id' => 'Kamu ingin memotret area Karang Memadu dari dekat. Apa tindakan terbaikmu?', 'en' => 'You want to photograph the Karang Memadu area up close. What is your best action?'],
                    'options' => [
                        ['text' => ['id' => 'Meminta izin pemandu atau warga terlebih dahulu', 'en' => 'Ask the guide or residents for permission first'], 'correct' => true, 'explanation' => ['id' => 'Benar! Beberapa area adat memiliki batasan yang perlu dihormati.', 'en' => 'Correct! Some customary areas have boundaries that must be respected.']],
                        ['text' => ['id' => 'Langsung memotret tanpa bertanya', 'en' => 'Photograph immediately without asking'], 'correct' => false, 'explanation' => ['id' => 'Tidak semua area terbuka bebas untuk difoto tanpa izin.', 'en' => 'Not every area is freely open to be photographed without permission.']],
                        ['text' => ['id' => 'Masuk ke area tanpa izin demi foto terbaik', 'en' => 'Enter the area without permission for the best photo'], 'correct' => false, 'explanation' => ['id' => 'Masuk tanpa izin bisa melanggar norma yang berlaku di desa adat.', 'en' => 'Entering without permission may violate the norms upheld in the customary village.']],
                    ],
                ],
                [
                    'text' => ['id' => 'Kamu penasaran alasan adanya Karang Memadu. Bagaimana cara terbaik mencari tahu?', 'en' => 'You are curious about the reason Karang Memadu exists. What is the best way to find out?'],
                    'options' => [
                        ['text' => ['id' => 'Bertanya langsung pada pemandu atau warga desa', 'en' => 'Ask the guide or village residents directly'], 'correct' => true, 'explanation' => ['id' => 'Benar! Sumber informasi langsung dari warga adalah yang paling akurat dan sopan.', 'en' => 'Correct! Information straight from residents is the most accurate and respectful source.']],
                        ['text' => ['id' => 'Menyimpulkan sendiri dari internet tanpa verifikasi', 'en' => 'Draw your own conclusions from the internet without verifying'], 'correct' => false, 'explanation' => ['id' => 'Informasi daring tentang adat lokal bisa tidak akurat.', 'en' => 'Online information about local customs can be inaccurate.']],
                        ['text' => ['id' => 'Tidak perlu tahu, cukup lewat saja', 'en' => 'No need to know, just pass by'], 'correct' => false, 'explanation' => ['id' => 'Memahami konteksnya membuat kunjungan lebih bermakna.', 'en' => 'Understanding the context makes the visit more meaningful.']],
                    ],
                ],
            ],
        ]);

        // Titik 5 — Mission 5 "Escape the Timeline": susun timeline berbatas waktu + riddle akhir.
        // Riddle akhir diimprovisasi (PDF tidak memberi teks spesifik) — perlu review.
        $this->mission($points[5], 1, 'sequence', ['id' => 'Escape the Timeline', 'en' => 'Escape the Timeline'], [
            'time_limit_seconds' => 180,
            'prompt' => ['id' => 'Susun kronologi perjuangan para pahlawan sebelum waktu habis!', 'en' => 'Arrange the chronology of the heroes\' struggle before time runs out!'],
            'explanation' => ['id' => 'Perjuangan warga Bangli dan Penglipuran dalam mempertahankan kemerdekaan menjadi bagian penting dari sejarah lokal yang diabadikan di Taman Makam Pahlawan.', 'en' => 'The struggle of the people of Bangli and Penglipuran in defending independence is an important part of local history commemorated at the Heroes Cemetery Park.'],
            'items' => [
                ['text' => ['id' => 'Pejuang lokal ikut mempertahankan wilayah Bangli pada masa perjuangan kemerdekaan.', 'en' => 'Local fighters helped defend the Bangli region during the independence struggle.']],
                ['text' => ['id' => 'Warga Penglipuran turut mendukung logistik dan perlindungan para pejuang.', 'en' => 'Penglipuran residents helped support the fighters\' logistics and protection.']],
                ['text' => ['id' => 'Beberapa pertempuran kecil terjadi di sekitar wilayah Bangli.', 'en' => 'Several small battles took place around the Bangli region.']],
                ['text' => ['id' => 'Setelah kemerdekaan, taman makam pahlawan dibangun untuk mengenang jasa mereka.', 'en' => 'After independence, the heroes cemetery park was built to honor their service.']],
                ['text' => ['id' => 'Setiap tahun, upacara penghormatan diadakan di taman ini.', 'en' => 'Every year, a commemoration ceremony is held at this park.']],
            ],
        ]);

        $this->mission($points[5], 2, 'riddle', ['id' => 'Heritage Key Terakhir', 'en' => 'The Final Heritage Key'], [
            'riddle' => ['id' => 'Aku dibangun untuk mengenang mereka yang gugur membela tanah air. Tempat apakah aku?', 'en' => 'I was built to honor those who gave their lives defending the homeland. What place am I?'],
            'answers' => ['taman makam pahlawan', 'makam pahlawan'],
            'success_text' => ['id' => 'Benar! Selamat, kamu berhasil mengumpulkan Heritage Key terakhir.', 'en' => 'Correct! Congratulations, you have collected the final Heritage Key.'],
            'explanation' => ['id' => 'Taman Makam Pahlawan mengingatkan kita untuk menghargai jasa para pejuang dan menjaga nilai-nilai kebersamaan serta patriotisme.', 'en' => 'The Heroes Cemetery Park reminds us to appreciate the sacrifices of fighters and uphold the values of togetherness and patriotism.'],
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
