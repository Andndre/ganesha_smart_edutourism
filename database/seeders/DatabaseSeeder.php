<?php

namespace Database\Seeders;

use App\Models\ArModel;
use App\Models\CapacityZone;
use App\Models\CulturalObject;
use App\Models\CulturalObjectQuiz;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\MapLocation;
use App\Models\Reservation;
use App\Models\TourPackage;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Real credentials/master data — runs in all environments
        $this->call([
            AdminSeeder::class,
            StaffSeeder::class,
        ]);

        CapacityZone::firstOrCreate(
            ['zone_identifier' => 'desa_penglipuran'],
            [
                'name' => 'Keseluruhan Desa Penglipuran',
                'max_capacity' => 2000,
                'warning_threshold' => 70,
                'critical_threshold' => 90,
                'polygon_coordinates' => [],
                'is_active' => true,
            ]
        );

        if (app()->environment('local')) {
            $this->seedDummyData();
        }

        // Real curated content — needed in production for the Rector demo
        $this->call(Route1HeritageQuestSeeder::class);
    }

    /**
     * Dummy data for local development, generated via model factories.
     */
    private function seedDummyData(): void
    {
        if (! User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $users = User::factory()->count(5)->create();

        $culturalObjects = CulturalObject::factory()->count(5)->create()->each(function (CulturalObject $object) {
            MapLocation::factory()->for($object, 'locationable')->create([
                'category' => 'cultural',
            ]);
            CulturalObjectQuiz::factory()->count(2)->create(['cultural_object_id' => $object->id]);
        });

        ArModel::factory()->count(3)->create();

        TourRoute::factory()->count(2)->create()->each(function (TourRoute $route) use ($culturalObjects) {
            TourRoutePoint::factory()->count(3)->create([
                'tour_route_id' => $route->id,
                'locationable_type' => CulturalObject::class,
                'locationable_id' => fn () => $culturalObjects->random()->id,
            ]);
        });

        UmkmProductCategory::factory()->count(3)->create();

        UmkmProfile::factory()->count(4)->create()->each(function (UmkmProfile $profile) {
            MapLocation::factory()->for($profile, 'locationable')->create(['category' => 'umkm']);
            UmkmProduct::factory()->count(3)->create([
                'umkm_profile_id' => $profile->id,
                'umkm_product_category_id' => UmkmProductCategory::inRandomOrder()->value('id'),
            ]);
        });

        Event::factory()->count(4)->create();

        $packages = TourPackage::factory()->count(5)->create();

        $reservations = $users->flatMap(fn (User $user) => Reservation::factory()->count(2)->create([
            'user_id' => $user->id,
            'tour_package_id' => $packages->random()->id,
        ]));

        $reservations->each(fn (Reservation $reservation) => Feedback::factory()->create([
            'user_id' => $reservation->user_id,
            'reservation_id' => $reservation->id,
        ]));

        VisitorLog::factory()->count(10)->create();

        CapacityZone::factory()->count(3)->create();
    }
}
