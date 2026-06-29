<?php

namespace Database\Seeders;

use App\Models\ArModel;
use App\Models\CapacityZone;
use App\Models\CulturalObject;
use App\Models\CulturalObjectQuiz;
use App\Models\Event;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Seeder khusus untuk simulasi testing di lokasi pengguna.
 *
 * Menempatkan semua data (lokasi budaya, event, zona kapasitas, tour route)
 * di sekitar koordinat test user agar notifikasi, geofence, dan peta
 * dapat diuji secara real-time.
 *
 * Jalankan: php artisan db:seed --class=TestSimulationSeeder
 */
class TestSimulationSeeder extends Seeder
{
    /** Center point (lokasi test user) */
    private const CENTER_LAT = -8.48858951350677;

    private const CENTER_LNG = 115.38392483153403;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCulturalObjects();
        $this->seedCapacityZones();
        $this->seedUpcomingEvent();
        $this->seedTourRoute();
        $this->seedUmkmData();
        $this->seedQuizzes();

        $this->command->info('✅ Test simulation data seeded successfully!');
        $this->command->info('📍 Center: '.self::CENTER_LAT.', '.self::CENTER_LNG);
        $this->command->newLine();
        $this->command->warn('⚠️  Pastikan .env sudah diupdate:');
        $this->command->line('   PENGLIPURAN_LAT='.self::CENTER_LAT);
        $this->command->line('   PENGLIPURAN_LON='.self::CENTER_LNG);
    }

    /**
     * Seed cultural objects near the test location.
     */
    private function seedCulturalObjects(): void
    {
        $objects = [
            [
                'name' => 'Pura Pande (Test)',
                'slug' => 'pura-pande-test',
                'short_description' => 'Pura kuno peninggalan kerajaan Bali, tempat upacara adat dilangsungkan.',
                'description' => '<p>Pura Pande adalah salah satu pura tertua di kawasan ini. Didirikan pada abad ke-14 sebagai tempat pemujaan para empu dan pandai besi kerajaan. Arsitekturnya merupakan perpaduan gaya Majapahit dan Bali Aga yang khas.</p><p>Pura ini masih aktif digunakan untuk upacara piodalan setiap enam bulan sekali menurut kalender Bali.</p>',
                'category' => 'temple',
                'ar_marker_id' => 'marker_pura_pande_test',
                'latitude' => -8.488940665135736,
                'longitude' => 115.38355006544914,
            ],
            [
                'name' => 'Balai Banjar Adat (Test)',
                'slug' => 'balai-banjar-adat-test',
                'short_description' => 'Tempat berkumpul warga untuk musyawarah dan kegiatan adat.',
                'description' => '<p>Balai Banjar Adat berfungsi sebagai pusat kegiatan sosial dan budaya masyarakat setempat. Di sini diselenggarakan berbagai musyawarah desa, latihan tari tradisional, dan pertunjukan gamelan.</p>',
                'category' => 'house',
                'ar_marker_id' => 'marker_balai_banjar_test',
                'latitude' => -8.48806495333438,
                'longitude' => 115.38410892684315,
            ],
            [
                'name' => 'Gerbang Utama Desa (Test)',
                'slug' => 'gerbang-utama-desa-test',
                'short_description' => 'Gerbang masuk utama kawasan wisata.',
                'description' => '<p>Gerbang utama yang menjadi pintu masuk resmi kawasan wisata. Dibangun dengan arsitektur tradisional Bali menggunakan batu padas dan bata merah khas.</p>',
                'category' => 'house',
                'ar_marker_id' => 'marker_gerbang_utama_test',
                'latitude' => self::CENTER_LAT + 0.001,
                'longitude' => self::CENTER_LNG - 0.0005,
            ],
        ];

        foreach ($objects as $obj) {
            if (CulturalObject::where('slug', $obj['slug'])->exists()) {
                $this->command->line("   ⏭️  Skipped (exists): {$obj['name']}");

                continue;
            }

            // ponytail: seed content is Indonesian — file it under the `id` locale
            // explicitly. Passing a bare string lets Spatie store it under the
            // current app locale (en), which is what corrupted the EN/ID tabs.
            $cultural = CulturalObject::create([
                'name' => ['id' => $obj['name']],
                'slug' => $obj['slug'],
                'short_description' => ['id' => $obj['short_description']],
                'description' => ['id' => $obj['description']],
                'category' => $obj['category'],
            ]);

            $mapLocation = $cultural->mapLocation()->create([
                'name' => $obj['name'],
                'category' => 'cultural',
                'latitude' => $obj['latitude'],
                'longitude' => $obj['longitude'],
                'is_accessible' => true,
            ]);

            $arModel = ArModel::firstOrCreate(
                ['name' => 'Model Simulasi Test'],
                [
                    'description' => 'Model simulasi untuk pengujian marker AR.',
                    'model_3d_path' => 'models/default.glb',
                ]
            );

            $arModel->update([
                'ar_marker_id' => $obj['ar_marker_id'],
                'map_location_id' => $mapLocation->id,
            ]);

            $this->command->line("   🏛️  Created: {$obj['name']}");
        }
    }

    /**
     * Seed capacity zones near the test location.
     */
    private function seedCapacityZones(): void
    {
        $zones = [
            [
                'name' => 'Zona Pura Pande (Test)',
                'zone_identifier' => 'pura_pande_test',
                'max_capacity' => 5,
                'warning_threshold' => 40,
                'critical_threshold' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Zona Balai Banjar (Test)',
                'zone_identifier' => 'balai_banjar_test',
                'max_capacity' => 8,
                'warning_threshold' => 50,
                'critical_threshold' => 75,
                'is_active' => true,
            ],
        ];

        foreach ($zones as $zone) {
            CapacityZone::updateOrCreate(
                ['zone_identifier' => $zone['zone_identifier']],
                $zone
            );
            $this->command->line("   📊 Zone: {$zone['name']} (max: {$zone['max_capacity']}, warn: {$zone['warning_threshold']}%, crit: {$zone['critical_threshold']}%)");
        }
    }

    /**
     * Seed an upcoming event that will trigger a reminder in ~15 minutes.
     */
    private function seedUpcomingEvent(): void
    {
        $eventName = 'Pertunjukan Tari Barong (Test Reminder)';

        if (Event::where('name', $eventName)->exists()) {
            // Update to start 15 minutes from now
            $event = Event::where('name', $eventName)->first();
            $event->update([
                'start_datetime' => now()->addMinutes(15),
                'end_datetime' => now()->addMinutes(75),
            ]);
            $this->command->line("   🎭 Updated event time: {$eventName} → starts at ".now()->addMinutes(15)->format('H:i'));

            return;
        }

        $event = new Event;
        $event->name = $eventName;
        $event->slug = Str::slug($eventName).'-'.Str::random(5);
        $event->description = 'Pertunjukan tari Barong klasik dengan iringan gamelan gong kebyar. Cerita epik Ramayana diwujudkan dalam tarian penuh warna dan ekspresi yang memukau.';
        $event->category = 'cultural';
        $event->start_datetime = Carbon::instance(now()->addMinutes(15));
        $event->end_datetime = Carbon::instance(now()->addMinutes(75));
        $event->location_name = 'Balai Banjar Adat (Test)';
        $event->is_free = true;
        $event->price = 0;
        $event->max_participants = 50;
        $event->current_participants = 0;
        $event->save();

        $event->mapLocation()->create([
            'name' => $eventName,
            'category' => 'cultural',
            'latitude' => -8.48806495333438,
            'longitude' => 115.38410892684315,
            'is_accessible' => true,
        ]);

        $this->command->line("   🎭 Created event: {$eventName} → starts at ".now()->addMinutes(15)->format('H:i'));
    }

    /**
     * Seed a tour route passing through the test locations.
     */
    private function seedTourRoute(): void
    {
        $routeName = 'Rute Wisata Budaya (Test)';

        if (TourRoute::where('name', $routeName)->exists()) {
            $this->command->line("   ⏭️  Skipped (exists): {$routeName}");

            return;
        }

        $route = TourRoute::create([
            'name' => $routeName,
            'description' => 'Rute wisata budaya singkat untuk pengujian fitur Smart Edutourism. Menghubungkan Gerbang Utama → Pura Pande → Balai Banjar Adat.',
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 30,
            'distance_meters' => 350,

            'is_active' => true,
        ]);

        // Retrieve the cultural objects we just created
        $puraPande = CulturalObject::where('slug', 'pura-pande-test')->first();
        $balaiBanjar = CulturalObject::where('slug', 'balai-banjar-adat-test')->first();
        $gerbang = CulturalObject::where('slug', 'gerbang-utama-desa-test')->first();

        $points = [
            [
                'locationable' => $gerbang,
                'order' => 1,
                'minutes' => 5,
                'story' => 'Selamat datang di Desa Wisata! Gerbang ini dibangun dengan teknik bangunan tradisional tanpa paku.',
            ],
            [
                'locationable' => $puraPande,
                'order' => 2,
                'minutes' => 15,
                'story' => 'Pura Pande merupakan salah satu pura tertua. Perhatikan relief cerita pewayangan di dinding sebelah kanan.',
            ],
            [
                'locationable' => $balaiBanjar,
                'order' => 3,
                'minutes' => 10,
                'story' => 'Balai Banjar Adat menjadi pusat kehidupan sosial masyarakat. Di sini sering digelar pertunjukan seni dan musyawarah desa.',
            ],
        ];

        foreach ($points as $point) {
            if ($point['locationable']) {
                TourRoutePoint::create([
                    'tour_route_id' => $route->id,
                    'locationable_type' => CulturalObject::class,
                    'locationable_id' => $point['locationable']->id,
                    'order' => $point['order'],
                    'estimated_visit_minutes' => $point['minutes'],
                    'storytelling_content' => $point['story'],
                ]);
            }
        }

        $this->command->line("   🗺️  Created tour route: {$routeName} ({$route->routePoints()->count()} stops)");
    }

    /**
     * Seed simulation UMKM data.
     */
    private function seedUmkmData(): void
    {
        // 1. Create or get categories
        $categoriesData = [
            [
                'name' => ['en' => 'Culinary', 'id' => 'Kuliner'],
                'slug' => 'culinary',
                'description' => ['en' => 'Local food and beverages', 'id' => 'Makanan dan minuman khas lokal'],
            ],
            [
                'name' => ['en' => 'Craft', 'id' => 'Kerajinan'],
                'slug' => 'craft',
                'description' => ['en' => 'Handmade traditional crafts', 'id' => 'Kerajinan tangan tradisional'],
            ],
            [
                'name' => ['en' => 'Souvenir', 'id' => 'Oleh-oleh'],
                'slug' => 'souvenir',
                'description' => ['en' => 'Gifts and souvenirs', 'id' => 'Cendera mata khas Penglipuran'],
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = UmkmProductCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                ]
            );
        }

        // 2. Create UMKM Owners and Profiles
        $umkms = [
            [
                'business_name' => ['en' => 'Warung Pak Wayan', 'id' => 'Warung Pak Wayan'],
                'slug' => 'warung-pak-wayan',
                'owner_name' => 'I Wayan Sudarta',
                'description' => ['en' => 'Authentic Balinese culinary and traditional drinks.', 'id' => 'Kuliner otentik Bali dan minuman tradisional.'],
                'latitude' => -8.48820000,
                'longitude' => 115.38450000,
                'rating' => 4.9,
                'products' => [
                    [
                        'name' => ['en' => 'Authentic Kopi Luwak', 'id' => 'Kopi Luwak Penglipuran Asli'],
                        'slug' => 'kopi-luwak-asli',
                        'description' => ['en' => 'Traditional sangrai Kopi Luwak from local plantation.', 'id' => 'Kopi Luwak khas Desa Wisata Penglipuran, diproses secara tradisional menggunakan metode sangrai manual.'],
                        'price' => 50000,
                        'stock' => 15,
                        'unit' => 'pack',
                        'category_slug' => 'culinary',
                    ],
                ],
            ],
            [
                'business_name' => ['en' => 'Kadek Souvenirs', 'id' => 'Kadek Souvenirs'],
                'slug' => 'kadek-souvenirs',
                'owner_name' => 'Ni Kadek Sri',
                'description' => ['en' => 'Traditional Balinese handicrafts and souvenirs.', 'id' => 'Kerajinan tangan tradisional dan cendera mata khas Bali.'],
                'latitude' => -8.48900000,
                'longitude' => 115.38320000,
                'rating' => 4.8,
                'products' => [
                    [
                        'name' => ['en' => 'Balinese Bamboo Fan', 'id' => 'Kipas Bambu Bali'],
                        'slug' => 'kipas-bambu-bali',
                        'description' => ['en' => 'Handmade woven bamboo fan with traditional motifs.', 'id' => 'Kipas bambu anyaman tangan dengan motif tradisional khas Bali.'],
                        'price' => 15000,
                        'stock' => 30,
                        'unit' => 'pcs',
                        'category_slug' => 'craft',
                    ],
                ],
            ],
        ];

        foreach ($umkms as $item) {
            if (UmkmProfile::where('slug', $item['slug'])->exists()) {
                continue;
            }

            // Create Owner User
            $owner = User::create([
                'name' => $item['owner_name'],
                'email' => $item['slug'].'@example.com',
                'password' => bcrypt('password'),
                'role' => 'umkm_owner',
                'phone' => '628123456789'.rand(0, 9),
            ]);

            $profile = UmkmProfile::create([
                'user_id' => $owner->id,
                'owner_name' => $item['owner_name'],
                'business_name' => $item['business_name'],
                'slug' => $item['slug'],
                'description' => $item['description'],
                'rating' => $item['rating'],
                'is_active' => true,
            ]);

            // Create Map Location for Leaflet
            $profile->mapLocation()->create([
                'name' => $item['business_name']['id'],
                'category' => 'umkm',
                'latitude' => $item['latitude'],
                'longitude' => $item['longitude'],
                'is_accessible' => true,
            ]);

            // Create Products
            foreach ($item['products'] as $prod) {
                $category = $categories[$prod['category_slug']];
                UmkmProduct::create([
                    'umkm_profile_id' => $profile->id,
                    'umkm_product_category_id' => $category->id,
                    'name' => $prod['name'],
                    'slug' => $prod['slug'],
                    'description' => $prod['description'],
                    'price' => $prod['price'],
                    'stock' => $prod['stock'],
                    'unit' => $prod['unit'],
                    'is_active' => true,
                ]);
            }
        }

        $this->command->line('   🛒  Created simulation UMKMs and Products');
    }

    /**
     * Seed quizzes for cultural objects on the tour route.
     */
    private function seedQuizzes(): void
    {
        $quizzes = [
            ['slug' => 'gerbang-utama-desa-test', 'question' => ['en' => 'What traditional material is the main gate made of?', 'id' => 'Dari bahan tradisional apa gerbang utama dibuat?'], 'options' => ['Batu padas dan bata merah', 'Kayu jati dan bambu', 'Batu granit dan semen', 'Anyaman bambu saja'], 'correct' => 'A'],
            ['slug' => 'gerbang-utama-desa-test', 'question' => ['en' => 'What is the architectural style of the main gate?', 'id' => 'Apa gaya arsitektur dari gerbang utama?'], 'options' => ['Modern Minimalis', 'Tradisional Bali', 'Kolonial Belanda', 'Gotik Eropa'], 'correct' => 'B'],
            ['slug' => 'pura-pande-test', 'question' => ['en' => 'When was Pura Pande estimated to be established?', 'id' => 'Kapan Pura Pande diperkirakan didirikan?'], 'options' => ['Abad ke-10', 'Abad ke-14', 'Abad ke-16', 'Abad ke-20'], 'correct' => 'B'],
            ['slug' => 'pura-pande-test', 'question' => ['en' => 'Who were the main worshipers at Pura Pande?', 'id' => 'Siapa yang menjadi pemuja utama di Pura Pande?'], 'options' => ['Para petani', 'Para empu dan pandai besi', 'Para pedagang', 'Para nelayan'], 'correct' => 'B'],
            ['slug' => 'balai-banjar-adat-test', 'question' => ['en' => 'What is the main function of Balai Banjar Adat?', 'id' => 'Apa fungsi utama dari Balai Banjar Adat?'], 'options' => ['Tempat ibadah', 'Pusat kegiatan sosial dan budaya', 'Pasar tradisional', 'Sekolah adat'], 'correct' => 'B'],
            ['slug' => 'balai-banjar-adat-test', 'question' => ['en' => 'What traditional performances are held at Balai Banjar Adat?', 'id' => 'Pertunjukan tradisional apa yang diadakan di Balai Banjar Adat?'], 'options' => ['Wayang kulit', 'Tari tradisional dan gamelan', 'Tari modern', 'Sirkus'], 'correct' => 'B'],
        ];

        foreach ($quizzes as $item) {
            $object = CulturalObject::where('slug', $item['slug'])->first();
            if (! $object) {
                continue;
            }

            $exists = CulturalObjectQuiz::where('cultural_object_id', $object->id)
                ->where('correct_option', $item['correct'])
                ->exists();

            if ($exists) {
                continue;
            }

            CulturalObjectQuiz::create([
                'cultural_object_id' => $object->id,
                'question' => $item['question'],
                'option_a' => $item['options'][0],
                'option_b' => $item['options'][1],
                'option_c' => $item['options'][2],
                'option_d' => $item['options'][3],
                'correct_option' => $item['correct'],
            ]);
        }

        $count = CulturalObjectQuiz::count();
        $this->command->line("   📝  Seeded quizzes for cultural objects (total: {$count})");
    }
}
