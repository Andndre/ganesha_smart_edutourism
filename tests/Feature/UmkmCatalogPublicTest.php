<?php

namespace Tests\Feature;

use App\Models\MapLocation;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmCatalogPublicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that user can access the catalog index.
     */
    public function test_user_can_access_umkm_catalog_index(): void
    {
        $response = $this->get('/umkm');
        $response->assertStatus(200);
        $response->assertSee('Jelajah UMKM');
    }

    /**
     * Test that the session keeps multi-stop recommendations when loading index.
     */
    public function test_multi_stop_recommendations_kept_in_session(): void
    {
        // 1. Create 2 UMKM Owners
        $owner1 = User::factory()->create(['role' => 'umkm_owner']);
        $owner2 = User::factory()->create(['role' => 'umkm_owner']);

        // 2. Create 2 UMKM Profiles
        $umkm1 = UmkmProfile::create([
            'user_id' => $owner1->id,
            'owner_name' => $owner1->name,
            'business_name' => 'Wayan Coffee',
            'category' => 'culinary',
            'slug' => 'wayan-coffee',
            'ar_marker_id' => 'UMKM_TEST01',
            'is_active' => true,
        ]);
        $umkm2 = UmkmProfile::create([
            'user_id' => $owner2->id,
            'owner_name' => $owner2->name,
            'business_name' => 'Kadek Souvenirs',
            'category' => 'craft',
            'slug' => 'kadek-souvenirs',
            'ar_marker_id' => 'UMKM_TEST02',
            'is_active' => true,
        ]);

        // Create MapLocations for both UMKM to prevent division by zero in Haversine distance
        MapLocation::create([
            'locationable_id' => $umkm1->id,
            'locationable_type' => UmkmProfile::class,
            'latitude' => -8.4223,
            'longitude' => 115.3594,
            'name' => 'Wayan Coffee',
            'category' => 'umkm',
            'is_accessible' => true,
        ]);
        MapLocation::create([
            'locationable_id' => $umkm2->id,
            'locationable_type' => UmkmProfile::class,
            'latitude' => -8.4225,
            'longitude' => 115.3596,
            'name' => 'Kadek Souvenirs',
            'category' => 'umkm',
            'is_accessible' => true,
        ]);

        // 3. Create 2 categories
        $catFood = UmkmProductCategory::create(['name' => 'Kuliner', 'slug' => 'kuliner']);
        $catCraft = UmkmProductCategory::create(['name' => 'Kerajinan', 'slug' => 'kerajinan']);

        // 4. Assign products: UMKM 1 only has Culinary, UMKM 2 only has Craft
        UmkmProduct::create([
            'umkm_profile_id' => $umkm1->id,
            'umkm_product_category_id' => $catFood->id,
            'name' => 'Kopi Luwak',
            'slug' => 'kopi-luwak',
            'price' => 25000,
            'stock' => 10,
            'is_active' => true,
        ]);
        UmkmProduct::create([
            'umkm_profile_id' => $umkm2->id,
            'umkm_product_category_id' => $catCraft->id,
            'name' => 'Kipas Bali',
            'slug' => 'kipas-bali',
            'price' => 15000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // 5. Query both categories (which forces a multi-stop fallback route since no single UMKM has both!)
        $response = $this->post('/umkm/recommend', [
            'category_ids' => [$catFood->id, $catCraft->id],
        ]);

        // 6. Should redirect back to /umkm with multi_stop_recommendations in session
        $response->assertRedirect();
        $response->assertSessionHas('multi_stop_recommendations');

        // 7. Follow the redirect to /umkm
        $indexResponse = $this->from('/umkm')->get('/umkm');
        $indexResponse->assertStatus(200);

        // Due to session()->keep(), it must STILL have it in the session!
        $indexResponse->assertSessionHas('multi_stop_recommendations');

        // 8. Go to multi-route page, which should read the session successfully and render the route
        $multiRouteResponse = $this->get('/umkm/multi-route');
        $multiRouteResponse->assertStatus(200);
        $multiRouteResponse->assertSee('Rute Belanja');
    }
}
