<?php

namespace Database\Seeders;

use App\Models\CapacityZone;
use App\Models\CulturalObject;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\LearningContent;
use App\Models\LearningModule;
use App\Models\LearningQuiz;
use App\Models\MapLocation;
use App\Models\Reservation;
use App\Models\TourPackage;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\UmkmProfile;
use App\Models\User;
use App\Models\VisitorLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalDevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baseLat = (float) env('PENGLIPURAN_LAT', -8.422303596762355);
        $baseLon = (float) env('PENGLIPURAN_LON', 115.35948833933173);

        // 1. Seed Users (Admin, Tourist, and UMKM Owners linked to profiles)
        $admin = User::where('email', 'admin@ganesha.com')->first();
        if (! $admin) {
            $admin = User::create([
                'name' => 'Admin Ganesha',
                'email' => 'admin@ganesha.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'phone' => '081122334455',
                'nationality' => 'Indonesia',
                'preferred_language' => 'id',
            ]);
        }

        $tourist = User::where('email', 'tourist@ganesha.com')->first();
        if (! $tourist) {
            $tourist = User::create([
                'name' => 'Tourist Jane',
                'email' => 'tourist@ganesha.com',
                'password' => Hash::make('password'),
                'role' => 'tourist',
                'email_verified_at' => now(),
                'phone' => '081234567890',
                'nationality' => 'Australia',
                'preferred_language' => 'en',
            ]);
        }

        // Additional random tourists
        for ($i = 1; $i <= 5; $i++) {
            $touristEmail = 'tourist'.$i.'@example.com';
            if (! User::where('email', $touristEmail)->exists()) {
                User::create([
                    'name' => 'Tourist '.$i,
                    'email' => $touristEmail,
                    'password' => Hash::make('password'),
                    'role' => 'tourist',
                    'email_verified_at' => now(),
                    'phone' => '08223456789'.$i,
                    'nationality' => fake()->country(),
                    'preferred_language' => fake()->randomElement(['id', 'en']),
                ]);
            }
        }

        // Link existing UMKM Profiles with corresponding owner users
        $umkmSlugs = [
            'warung-dedari' => 'owner.dedari@ganesha.com',
            'penglipuran-craft' => 'owner.craft@ganesha.com',
            'souvenir-collection' => 'owner.souvenir@ganesha.com',
            'traditional-massage' => 'owner.massage@ganesha.com',
            'balinese-cooking-class' => 'owner.cooking@ganesha.com',
        ];

        foreach ($umkmSlugs as $slug => $email) {
            $profile = UmkmProfile::where('slug', $slug)->first();
            if ($profile) {
                $user = User::where('email', $email)->first();
                if (! $user) {
                    $user = User::create([
                        'name' => 'Owner '.$profile->business_name,
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'role' => 'umkm_owner',
                        'email_verified_at' => now(),
                        'phone' => '0878654321'.rand(0, 9),
                        'nationality' => 'Indonesia',
                        'preferred_language' => 'id',
                    ]);
                }
                $profile->update(['user_id' => $user->id]);
            }
        }

        // 2. Seed Capacity Zones
        $zones = [
            [
                'name' => 'Hutan Bambu Penglipuran',
                'zone_identifier' => 'forest_bamboo',
                'max_capacity' => 150,
                'warning_threshold' => 105,
                'critical_threshold' => 135,
                'current_count' => 35,
                'is_active' => true,
            ],
            [
                'name' => 'Pura Penataran Penglipuran',
                'zone_identifier' => 'pura_penataran',
                'max_capacity' => 80,
                'warning_threshold' => 56,
                'critical_threshold' => 72,
                'current_count' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Pura Desa & Pura Puseh',
                'zone_identifier' => 'pura_desa',
                'max_capacity' => 100,
                'warning_threshold' => 70,
                'critical_threshold' => 90,
                'current_count' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Desa Adat Penglipuran (Main Street)',
                'zone_identifier' => 'village_main',
                'max_capacity' => 600,
                'warning_threshold' => 420,
                'critical_threshold' => 540,
                'current_count' => 220,
                'is_active' => true,
            ],
            [
                'name' => 'Pusat UMKM & Kuliner',
                'zone_identifier' => 'umkm_culinary',
                'max_capacity' => 100,
                'warning_threshold' => 70,
                'critical_threshold' => 90,
                'current_count' => 45,
                'is_active' => true,
            ],
        ];

        foreach ($zones as $zone) {
            CapacityZone::updateOrCreate(
                ['zone_identifier' => $zone['zone_identifier']],
                $zone
            );
        }

        // 3. Seed Map Locations polymorphically for existing Cultural Objects & UMKM Profiles
        $culturalObjects = CulturalObject::all();
        foreach ($culturalObjects as $obj) {
            MapLocation::updateOrCreate(
                [
                    'locationable_type' => CulturalObject::class,
                    'locationable_id' => $obj->id,
                ],
                [
                    'name' => $obj->name,
                    'category' => 'cultural',
                    'latitude' => $obj->latitude,
                    'longitude' => $obj->longitude,
                    'is_accessible' => true,
                    'accessibility_notes' => 'Akses jalan datar ramah kursi roda dan stroller bayi.',
                ]
            );
        }

        $umkms = UmkmProfile::all();
        foreach ($umkms as $umkm) {
            MapLocation::updateOrCreate(
                [
                    'locationable_type' => UmkmProfile::class,
                    'locationable_id' => $umkm->id,
                ],
                [
                    'name' => $umkm->business_name,
                    'category' => 'umkm',
                    'latitude' => $umkm->latitude,
                    'longitude' => $umkm->longitude,
                    'is_accessible' => true,
                    'accessibility_notes' => 'Pintu masuk landai, staf siap membantu akses disabilitas.',
                ]
            );
        }

        // Standard Utility & Emergency Facilities Map Locations
        $defaultZone = CapacityZone::where('zone_identifier', 'village_main')->first();
        $defaultZoneId = $defaultZone ? $defaultZone->id : 1;

        $facilities = [
            [
                'name' => 'Tourist Information Center',
                'category' => 'facility',
                'locationable_type' => CapacityZone::class,
                'locationable_id' => $defaultZoneId,
                'latitude' => $baseLat + 0.0003,
                'longitude' => $baseLon - 0.0005,
                'is_accessible' => true,
                'accessibility_notes' => 'Pusat informasi utama dekat area parkir depan.',
            ],
            [
                'name' => 'Toilet Umum Aksesibel',
                'category' => 'accessibility',
                'locationable_type' => CapacityZone::class,
                'locationable_id' => $defaultZoneId,
                'latitude' => $baseLat - 0.0002,
                'longitude' => $baseLon + 0.0001,
                'is_accessible' => true,
                'accessibility_notes' => 'Toilet ramah disabilitas dengan handrail dan ruang putar luas.',
            ],
            [
                'name' => 'Pos Kesehatan & Pertolongan Pertama',
                'category' => 'emergency',
                'locationable_type' => CapacityZone::class,
                'locationable_id' => $defaultZoneId,
                'latitude' => $baseLat - 0.0006,
                'longitude' => $baseLon - 0.0002,
                'is_accessible' => true,
                'accessibility_notes' => 'Peralatan pertolongan pertama dasar, tabung oksigen, dan kursi roda darurat.',
            ],
        ];

        foreach ($facilities as $fac) {
            MapLocation::updateOrCreate(
                [
                    'name' => $fac['name'],
                    'category' => $fac['category'],
                ],
                $fac
            );
        }

        // 4. Seed Tour Routes & Points
        $route1 = TourRoute::updateOrCreate(
            ['name' => 'Jalur Sejarah & Spiritual'],
            [
                'description' => 'Menyusuri pura-pura bersejarah di Desa Adat Penglipuran dan mempelajari tradisi keagamaan setempat.',
                'difficulty' => 'moderate',
                'estimated_duration_minutes' => 90,
                'distance_meters' => 1500,
                'is_smart_route' => true,
                'is_active' => true,
            ]
        );

        $route2 = TourRoute::updateOrCreate(
            ['name' => 'Jalur Kuliner & Kerajinan'],
            [
                'description' => 'Menyusuri area perajin anyaman bambu dan menjelajahi keindahan kuliner lokal Desa Penglipuran.',
                'difficulty' => 'easy',
                'estimated_duration_minutes' => 60,
                'distance_meters' => 900,
                'is_smart_route' => false,
                'is_active' => true,
            ]
        );

        $puraTamanAyun = CulturalObject::where('slug', 'pura-taman-ayun')->first();
        $puraPenataran = CulturalObject::where('slug', 'pura-penataran')->first();
        $rumahLombok = CulturalObject::where('slug', 'rumah-lombok-penglipuran')->first();

        if ($puraPenataran) {
            TourRoutePoint::updateOrCreate(
                [
                    'tour_route_id' => $route1->id,
                    'locationable_type' => CulturalObject::class,
                    'locationable_id' => $puraPenataran->id,
                ],
                [
                    'order' => 1,
                    'estimated_visit_minutes' => 30,
                    'storytelling_content' => 'Pura Penataran adalah pusat spiritual utama warga Desa Adat Penglipuran.',
                ]
            );
        }

        if ($rumahLombok) {
            TourRoutePoint::updateOrCreate(
                [
                    'tour_route_id' => $route1->id,
                    'locationable_type' => CulturalObject::class,
                    'locationable_id' => $rumahLombok->id,
                ],
                [
                    'order' => 2,
                    'estimated_visit_minutes' => 20,
                    'storytelling_content' => 'Rumah Tradisional Lombok Penglipuran menyajikan arsitektur Bali kuno yang terawat dengan asri.',
                ]
            );
        }

        if ($puraTamanAyun) {
            TourRoutePoint::updateOrCreate(
                [
                    'tour_route_id' => $route1->id,
                    'locationable_type' => CulturalObject::class,
                    'locationable_id' => $puraTamanAyun->id,
                ],
                [
                    'order' => 3,
                    'estimated_visit_minutes' => 40,
                    'storytelling_content' => 'Pura bersejarah dengan keindahan taman air warisan kerajaan Mengwi.',
                ]
            );
        }

        $warungDedari = UmkmProfile::where('slug', 'warung-dedari')->first();
        $penglipuranCraft = UmkmProfile::where('slug', 'penglipuran-craft')->first();
        $workshopBambu = CulturalObject::where('slug', 'workshop-anyaman-bambu')->first();

        if ($workshopBambu) {
            TourRoutePoint::updateOrCreate(
                [
                    'tour_route_id' => $route2->id,
                    'locationable_type' => CulturalObject::class,
                    'locationable_id' => $workshopBambu->id,
                ],
                [
                    'order' => 1,
                    'estimated_visit_minutes' => 25,
                    'storytelling_content' => 'Saksikan langsung demonstrasi menganyam bambu oleh perajin lokal desa.',
                ]
            );
        }

        if ($penglipuranCraft) {
            TourRoutePoint::updateOrCreate(
                [
                    'tour_route_id' => $route2->id,
                    'locationable_type' => UmkmProfile::class,
                    'locationable_id' => $penglipuranCraft->id,
                ],
                [
                    'order' => 2,
                    'estimated_visit_minutes' => 15,
                    'storytelling_content' => 'Penglipuran Craft menjual cinderamata anyaman bambu orisinal buatan tangan warga.',
                ]
            );
        }

        if ($warungDedari) {
            TourRoutePoint::updateOrCreate(
                [
                    'tour_route_id' => $route2->id,
                    'locationable_type' => UmkmProfile::class,
                    'locationable_id' => $warungDedari->id,
                ],
                [
                    'order' => 3,
                    'estimated_visit_minutes' => 20,
                    'storytelling_content' => 'Nikmati santap siang khas kuliner lokal Bali di Warung Dedari.',
                ]
            );
        }

        // 5. Seed Events
        $events = [
            [
                'name' => 'Festival Kuliner Tradisional Penglipuran',
                'slug' => 'festival-kuliner-tradisional-penglipuran',
                'description' => 'Nikmati kelezatan berbagai kuliner tradisional Bali buatan warga lokal Penglipuran.',
                'category' => 'culinary',
                'start_datetime' => Carbon::now()->addDays(5)->setTime(10, 0),
                'end_datetime' => Carbon::now()->addDays(5)->setTime(18, 0),
                'location_name' => 'Balai Banjar Penglipuran',
                'latitude' => $baseLat - 0.0004,
                'longitude' => $baseLon + 0.0005,
                'is_free' => true,
                'max_participants' => 200,
                'current_participants' => 45,
            ],
            [
                'name' => 'Upacara Piodalan Agung',
                'slug' => 'upacara-piodalan-agung',
                'description' => 'Menyaksikan langsung ritual peribadatan piodalan agung di Pura Penataran.',
                'category' => 'ceremony',
                'start_datetime' => Carbon::now()->addDays(12)->setTime(8, 0),
                'end_datetime' => Carbon::now()->addDays(12)->setTime(15, 0),
                'location_name' => 'Pura Penataran Penglipuran',
                'latitude' => $baseLat - 0.0009,
                'longitude' => $baseLon + 0.0002,
                'is_free' => true,
                'max_participants' => 300,
                'current_participants' => 120,
            ],
            [
                'name' => 'Kelas Privat Kerajinan Anyaman Bambu',
                'slug' => 'kelas-privat-kerajinan-anyaman-bambu',
                'description' => 'Belajar langsung dari empu perajin anyaman bambu Bali dan bawa pulang hasil karyamu.',
                'category' => 'workshop',
                'start_datetime' => Carbon::now()->addDays(3)->setTime(14, 0),
                'end_datetime' => Carbon::now()->addDays(3)->setTime(16, 30),
                'location_name' => 'Workshop Craft Center',
                'latitude' => $baseLat + 0.0006,
                'longitude' => $baseLon + 0.0008,
                'is_free' => false,
                'price' => 75000.00,
                'max_participants' => 15,
                'current_participants' => 8,
            ],
        ];

        foreach ($events as $evt) {
            Event::updateOrCreate(
                ['slug' => $evt['slug']],
                $evt
            );
        }

        // 6. Seed Reservations
        $packages = TourPackage::all();
        $package1 = $packages->first();
        $package2 = $packages->skip(1)->first();

        $qr1 = 'QR_PACK_DEV_001';
        $qr2 = 'QR_PACK_DEV_002';
        $qr3 = 'QR_PACK_DEV_003';
        $qr4 = 'QR_PACK_DEV_004';

        if ($package1 && ! Reservation::where('qr_code', $qr1)->exists()) {
            Reservation::create([
                'user_id' => $tourist->id,
                'guest_name' => $tourist->name,
                'guest_email' => $tourist->email,
                'guest_phone' => '081234567890',
                'tour_package_id' => $package1->id,
                'reservation_type' => 'package',
                'scheduled_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'scheduled_time' => '09:00:00',
                'party_size' => 4,
                'total_amount' => $package1->price * 4,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'TX_TRSF_'.rand(1000, 9999),
                'qr_code' => $qr1,
            ]);
        }

        if ($package2 && ! Reservation::where('qr_code', $qr2)->exists()) {
            Reservation::create([
                'user_id' => null,
                'guest_name' => 'John Doe',
                'guest_email' => 'johndoe@example.com',
                'guest_phone' => '081987654321',
                'tour_package_id' => $package2->id,
                'reservation_type' => 'package',
                'scheduled_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
                'scheduled_time' => '10:00:00',
                'party_size' => 2,
                'total_amount' => $package2->price * 2,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => 'e_wallet',
                'payment_reference' => null,
                'qr_code' => $qr2,
            ]);
        }

        if ($package1 && ! Reservation::where('qr_code', $qr3)->exists()) {
            $completedRes = Reservation::create([
                'user_id' => $tourist->id,
                'guest_name' => $tourist->name,
                'guest_email' => $tourist->email,
                'guest_phone' => '081234567890',
                'tour_package_id' => $package1->id,
                'reservation_type' => 'package',
                'scheduled_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'scheduled_time' => '09:00:00',
                'party_size' => 3,
                'total_amount' => $package1->price * 3,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'credit_card',
                'payment_reference' => 'TX_CC_'.rand(1000, 9999),
                'qr_code' => $qr3,
            ]);

            // 7. Seed Feedback on completed reservation
            if (! Feedback::where('reservation_id', $completedRes->id)->exists()) {
                Feedback::create([
                    'user_id' => $tourist->id,
                    'reservation_id' => $completedRes->id,
                    'feedback_type' => 'general',
                    'rating' => 5,
                    'comment' => 'Pengalaman yang sangat luar biasa! Pemandu wisatanya sangat ramah dan berpengetahuan luas tentang adat istiadat desa.',
                    'is_public' => true,
                    'admin_response' => 'Terima kasih Jane! Kami senang Anda menikmati tur bersama kami. Sampai jumpa di kunjungan berikutnya!',
                ]);
            }
        }

        if ($package1 && ! Reservation::where('qr_code', $qr4)->exists()) {
            Reservation::create([
                'user_id' => null,
                'guest_name' => 'Alice Cooper',
                'guest_email' => 'alice@example.com',
                'guest_phone' => '082112233440',
                'tour_package_id' => $package1->id,
                'reservation_type' => 'package',
                'scheduled_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'scheduled_time' => '13:00:00',
                'party_size' => 2,
                'total_amount' => $package1->price * 2,
                'status' => 'cancelled',
                'payment_status' => 'unpaid',
                'payment_method' => null,
                'payment_reference' => null,
                'qr_code' => $qr4,
            ]);
        }

        // Additional public feedback
        $feedbacksData = [
            [
                'comment' => 'Sangat mengedukasi tentang budaya Bali, AR reconstruction-nya keren sekali.',
                'feedback_type' => 'cultural',
                'rating' => 4,
                'user_id' => $tourist->id,
            ],
            [
                'comment' => 'Makanan di Warung Dedari enak sekali! Sate lilitnya juara.',
                'feedback_type' => 'umkm',
                'rating' => 5,
                'user_id' => null,
                'admin_response' => 'Terima kasih atas kunjungannya ke warung mitra kami!',
            ],
            [
                'comment' => 'Tempat parkir agak jauh dari pintu masuk utama, tapi lingkungannya bersih sekali.',
                'feedback_type' => 'facility',
                'rating' => 3,
                'user_id' => null,
            ],
        ];

        foreach ($feedbacksData as $fb) {
            if (! Feedback::where('comment', $fb['comment'])->exists()) {
                Feedback::create([
                    'user_id' => $fb['user_id'],
                    'reservation_id' => null,
                    'feedback_type' => $fb['feedback_type'],
                    'rating' => $fb['rating'],
                    'comment' => $fb['comment'],
                    'is_public' => true,
                    'admin_response' => $fb['admin_response'] ?? null,
                ]);
            }
        }

        // 8. Seed Learning Modules, Contents & Quizzes
        $module1 = LearningModule::updateOrCreate(
            ['slug' => 'sejarah-desa-adat-penglipuran'],
            [
                'name' => 'Sejarah Desa Adat Penglipuran',
                'category' => 'history',
                'description' => 'Mengenal asal-usul, perkembangan, dan warisan budaya adiluhung Desa Penglipuran.',
                'difficulty' => 'beginner',
                'estimated_duration_minutes' => 20,
                'is_active' => true,
                'order' => 1,
            ]
        );

        $content1_1 = LearningContent::updateOrCreate(
            [
                'learning_module_id' => $module1->id,
                'title' => 'Asal-Usul Nama Penglipuran',
            ],
            [
                'content_type' => 'text',
                'content' => 'Penglipuran berasal dari kata "Pengeling" dan "Pura" yang berarti mengingat tempat leluhur. Teori lain menyebutkan berasal dari kata "Penglipur" (penghibur) karena dahulu kala raja-raja Bangli sering berkunjung ke desa ini untuk menghibur diri.',
                'order' => 1,
            ]
        );

        $content1_2 = LearningContent::updateOrCreate(
            [
                'learning_module_id' => $module1->id,
                'title' => 'Kuis Asal-Usul Penglipuran',
            ],
            [
                'content_type' => 'quiz',
                'content' => 'Uji pengetahuan dasar Anda mengenai nama dan asal-usul desa Penglipuran.',
                'order' => 2,
            ]
        );

        LearningQuiz::updateOrCreate(
            [
                'learning_content_id' => $content1_2->id,
                'question' => 'Apa salah satu arti asal kata nama Penglipuran?',
            ],
            [
                'options' => [
                    ['option' => 'Tempat pemandian raja', 'is_correct' => false],
                    ['option' => 'Mengingat tempat suci leluhur', 'is_correct' => true],
                    ['option' => 'Hutan bambu yang rindang', 'is_correct' => false],
                    ['option' => 'Kerajaan kuno di Bali', 'is_correct' => false],
                ],
                'explanation' => 'Penglipuran dipercaya berasal dari gabungan kata Pengeling (mengingat) dan Pura (tempat leluhur).',
                'order' => 1,
            ]
        );

        $module2 = LearningModule::updateOrCreate(
            ['slug' => 'arsitektur-bambu-tradisional'],
            [
                'name' => 'Arsitektur & Bambu Tradisional',
                'category' => 'craft',
                'description' => 'Menyelami keunikan arsitektur gerbang angkul-angkul dan pemanfaatan bambu dalam kehidupan warga.',
                'difficulty' => 'intermediate',
                'estimated_duration_minutes' => 15,
                'is_active' => true,
                'order' => 2,
            ]
        );

        $content2_1 = LearningContent::updateOrCreate(
            [
                'learning_module_id' => $module2->id,
                'title' => 'Gerbang Angkul-Angkul Tradisional',
            ],
            [
                'content_type' => 'text',
                'content' => 'Setiap pekarangan rumah di Desa Penglipuran memiliki gerbang khas yang dinamakan angkul-angkul. Angkul-angkul dibangun menggunakan bahan alami setempat, terutama bambu pilihan yang disusun rapi sebagai sirap penutup atap.',
                'order' => 1,
            ]
        );

        $content2_2 = LearningContent::updateOrCreate(
            [
                'learning_module_id' => $module2->id,
                'title' => 'Kuis Arsitektur Tradisional',
            ],
            [
                'content_type' => 'quiz',
                'content' => 'Uji pengetahuan Anda tentang struktur fisik dan bangunan tradisional Penglipuran.',
                'order' => 2,
            ]
        );

        LearningQuiz::updateOrCreate(
            [
                'learning_content_id' => $content2_2->id,
                'question' => 'Material utama apa yang digunakan untuk membuat atap rumah tradisional (Angkul-angkul) di Desa Penglipuran?',
            ],
            [
                'options' => [
                    ['option' => 'Sirap Kayu Ulin', 'is_correct' => false],
                    ['option' => 'Bambu (Sirap Bambu)', 'is_correct' => true],
                    ['option' => 'Ijuk Hitam', 'is_correct' => false],
                    ['option' => 'Genteng Tanah Liat', 'is_correct' => false],
                ],
                'explanation' => 'Atap angkul-angkul dan sebagian besar rumah adat di Penglipuran memanfaatkan sirap belahan bambu.',
                'order' => 1,
            ]
        );

        // 9. Seed Visitor Logs (for charts / statistics)
        if (VisitorLog::count() < 10) {
            $sessionIds = ['sess_aaa111', 'sess_bbb222', 'sess_ccc333', 'sess_ddd444'];
            $eventTypes = ['page_view', 'feature_use', 'location_visit', 'purchase'];

            for ($k = 0; $k < 50; $k++) {
                $user_id = fake()->boolean(60) ? $tourist->id : null;
                $loggedAt = Carbon::now()->subDays(rand(0, 14))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

                VisitorLog::create([
                    'session_id' => fake()->randomElement($sessionIds),
                    'user_id' => $user_id,
                    'event_type' => fake()->randomElement($eventTypes),
                    'event_data' => json_encode(['path' => fake()->randomElement(['/home', '/cultural', '/events', '/learning', '/tour-packages'])]),
                    'latitude' => $baseLat + (rand(-100, 100) / 100000),
                    'longitude' => $baseLon + (rand(-100, 100) / 100000),
                    'device_type' => fake()->randomElement(['mobile', 'tablet', 'desktop']),
                    'browser' => fake()->randomElement(['Chrome', 'Safari', 'Firefox', 'Edge']),
                    'nationality' => fake()->randomElement(['Indonesia', 'Australia', 'Germany', 'USA', 'Japan']),
                    'logged_at' => $loggedAt,
                ]);
            }
        }
    }
}
