<?php

namespace Tests\Feature;

use App\Models\MapLocation;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExploreLocationIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_explore_locations_include_map_location_id(): void
    {
        $owner = User::factory()->create(['role' => 'umkm_owner']);
        $umkm = UmkmProfile::create([
            'user_id' => $owner->id,
            'owner_name' => $owner->name,
            'business_name' => 'Warung Tes',
            'slug' => 'warung-tes',
            'is_active' => true,
        ]);
        $loc = MapLocation::create([
            'locationable_type' => UmkmProfile::class,
            'locationable_id' => $umkm->id,
            'name' => 'Warung Tes',
            'category' => 'umkm',
            'latitude' => -8.4210,
            'longitude' => 115.3592,
        ]);

        $response = $this->get('/explore');

        $response->assertOk();
        $locations = $response->viewData('locations');
        $this->assertContains($loc->id, array_column($locations, 'id'));
    }

    public function test_explore_renders_multi_route_stop_control(): void
    {
        $response = $this->get('/explore');

        $response->assertOk();
        $response->assertSee('btn-stop-multi-route', false);
        $response->assertSee('multi-route-progress', false);
    }

    /**
     * Leaflet ada saat parse, tapi window.Swal baru dipasang oleh bundel module yang
     * deferred — kalau gate ini cuma menunggu L, initMap() jalan duluan dan
     * initMultiRoute meledak dengan "Swal is not defined".
     */
    public function test_map_init_waits_for_both_leaflet_and_swal(): void
    {
        $response = $this->get('/explore');

        $response->assertOk();
        $response->assertSee("typeof L !== 'undefined' && typeof window.Swal !== 'undefined'", false);
    }
}
