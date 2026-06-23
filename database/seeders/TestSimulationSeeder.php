<?php

namespace Database\Seeders;

use App\Models\ArModel;
use App\Models\CapacityZone;
use App\Models\CulturalObject;
use App\Models\Event;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
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

            $cultural = CulturalObject::create([
                'name' => $obj['name'],
                'slug' => $obj['slug'],
                'short_description' => $obj['short_description'],
                'description' => $obj['description'],
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
}
