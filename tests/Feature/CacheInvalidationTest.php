<?php

namespace Tests\Feature;

use App\Models\ArModel;
use App\Models\CapacityZone;
use App\Models\CulturalObject;
use App\Models\Event;
use App\Models\Facility;
use App\Models\MapLocation;
use App\Models\TourPackage;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_capacity_zone_saved_invalidates_cache(): void
    {
        Cache::put('capacity_zones_active_array', ['data']);

        CapacityZone::create([
            'name' => 'Zone A',
            'zone_identifier' => 'zone-a',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        $this->assertNull(Cache::get('capacity_zones_active_array'));
    }

    public function test_cultural_object_saved_invalidates_cache(): void
    {
        Cache::put('cultural_objects_all_array', ['all']);
        Cache::put('explore_map_locations_array', ['locations']);
        Cache::put('cultural_object_array_tari-kecak', ['kecak']);

        $culturalObject = CulturalObject::create([
            'name' => 'Tari Kecak',
            'slug' => 'tari-kecak',
            'description' => 'Tradisi tari Bali',
            'category' => 'tradition',
        ]);

        $this->assertNull(Cache::get('cultural_objects_all_array'));
        $this->assertNull(Cache::get('explore_map_locations_array'));
        $this->assertNull(Cache::get('cultural_object_array_tari-kecak'));

        // Test dirty slug updates also clear old slug
        Cache::put('cultural_object_array_tari-kecak', ['old']);
        Cache::put('cultural_object_array_tari-kecak-baru', ['new']);

        $culturalObject->slug = 'tari-kecak-baru';
        $culturalObject->save();

        $this->assertNull(Cache::get('cultural_object_array_tari-kecak'));
        $this->assertNull(Cache::get('cultural_object_array_tari-kecak-baru'));
    }

    public function test_event_saved_invalidates_cache(): void
    {
        foreach (['all', 'ceremony', 'cultural', 'workshop', 'culinary'] as $cat) {
            Cache::put('public_events_upcoming_'.$cat, ['upcoming']);
            Cache::put('public_events_calendar_'.$cat, ['calendar']);
        }

        Event::create([
            'name' => 'Event Test',
            'slug' => 'event-test',
            'category' => 'cultural',
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHours(2),
            'location_name' => 'Desa Penglipuran',
            'is_free' => true,
        ]);

        foreach (['all', 'ceremony', 'cultural', 'workshop', 'culinary'] as $cat) {
            $this->assertNull(Cache::get('public_events_upcoming_'.$cat));
            $this->assertNull(Cache::get('public_events_calendar_'.$cat));
        }
    }

    public function test_tour_package_saved_invalidates_cache(): void
    {
        Cache::put('tour_packages_active_array', ['packages']);

        TourPackage::create([
            'name' => 'Paket Hemat',
            'slug' => 'paket-hemat',
            'description' => 'Paket murah meriah',
            'price' => 50000,
            'duration_hours' => 4.0,
            'max_capacity' => 10,
            'min_capacity' => 2,
            'is_active' => true,
        ]);

        $this->assertNull(Cache::get('tour_packages_active_array'));
    }

    public function test_tour_route_and_points_saved_invalidates_cache(): void
    {
        Cache::put('explore_map_routes_array', ['map_routes']);
        Cache::put('edutourism_routes_array', ['routes']);

        $route = TourRoute::factory()->create();

        $this->assertNull(Cache::get('explore_map_routes_array'));
        $this->assertNull(Cache::get('edutourism_routes_array'));

        // Reset cache
        Cache::put('explore_map_routes_array', ['map_routes']);
        Cache::put('edutourism_routes_array', ['routes']);

        // Create a route point
        TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::class,
            'locationable_id' => 1,
            'order' => 1,
        ]);

        $this->assertNull(Cache::get('explore_map_routes_array'));
        $this->assertNull(Cache::get('edutourism_routes_array'));
    }

    public function test_umkm_product_category_saved_invalidates_cache(): void
    {
        Cache::put('umkm_categories_array', ['categories']);

        UmkmProductCategory::create([
            'name' => 'Makanan',
            'slug' => 'makanan',
        ]);

        $this->assertNull(Cache::get('umkm_categories_array'));
    }

    public function test_map_related_models_saved_invalidates_cache(): void
    {
        Cache::put('explore_map_locations_array', ['locations']);

        $user = User::factory()->create();

        // 1. Facility
        Facility::create([
            'name' => 'Toilet Barat',
            'type' => 'toilet',
            'description' => 'Toilet bersih',
        ]);
        $this->assertNull(Cache::get('explore_map_locations_array'));

        // Reset cache
        Cache::put('explore_map_locations_array', ['locations']);

        // 2. UmkmProfile
        $umkm = UmkmProfile::create([
            'business_name' => 'Warung Test',
            'slug' => 'warung-test',
            'description' => 'Warung makan enak',
            'address' => 'Desa Penglipuran',
            'contact_number' => '08123456789',
            'owner_name' => 'Pak Test',
            'category' => 'culinary',
            'user_id' => $user->id,
        ]);
        $this->assertNull(Cache::get('explore_map_locations_array'));

        // Reset cache
        Cache::put('explore_map_locations_array', ['locations']);

        // 3. MapLocation
        $loc = MapLocation::create([
            'name' => 'Lokasi Test',
            'latitude' => -8.419,
            'longitude' => 115.321,
            'category' => 'umkm',
            'locationable_type' => UmkmProfile::class,
            'locationable_id' => $umkm->id,
        ]);
        $this->assertNull(Cache::get('explore_map_locations_array'));

        // Reset cache
        Cache::put('explore_map_locations_array', ['locations']);

        // 4. ArModel
        ArModel::create([
            'name' => 'Model AR',
            'model_3d_path' => 'models/ar.glb',
        ]);
        $this->assertNull(Cache::get('explore_map_locations_array'));
    }
}
