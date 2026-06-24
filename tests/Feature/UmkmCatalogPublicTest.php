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
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/umkm/recommend', [
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

    /**
     * Test search returns proper JSON structure with non-empty results.
     */
    public function test_umkm_search_returns_json(): void
    {
        app()->setLocale('en');

        $owner = User::factory()->create(['role' => 'umkm_owner']);

        $category = UmkmProductCategory::create([
            'name' => ['en' => 'Kuliner'],
            'slug' => 'kuliner',
        ]);

        $umkm = UmkmProfile::create([
            'user_id' => $owner->id,
            'owner_name' => $owner->name,
            'business_name' => ['en' => 'Kopi Luwak Shop'],
            'slug' => 'kopi-luwak-shop',
            'is_active' => true,
        ]);

        MapLocation::create([
            'locationable_id' => $umkm->id,
            'locationable_type' => UmkmProfile::class,
            'latitude' => -8.4223,
            'longitude' => 115.3594,
            'name' => 'Kopi Luwak Shop',
            'category' => 'umkm',
            'is_accessible' => true,
        ]);

        UmkmProduct::create([
            'umkm_profile_id' => $umkm->id,
            'umkm_product_category_id' => $category->id,
            'name' => ['en' => 'Kopi Luwak'],
            'slug' => 'kopi-luwak',
            'price' => 25000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->get('/umkm/api-search?q=kopi');
        $response->assertStatus(200);
        $response->assertJsonStructure(['umkms', 'products', 'categories']);

        $data = $response->json();
        $this->assertNotEmpty($data['umkms']);
        $this->assertNotEmpty($data['products']);
    }

    /**
     * Test search with empty query returns empty arrays.
     */
    public function test_umkm_search_empty_query(): void
    {
        $response = $this->get('/umkm/api-search?q=');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEmpty($data['umkms']);
        $this->assertEmpty($data['products']);
        $this->assertEmpty($data['categories']);
    }

    /**
     * Test search with no matching results returns empty arrays.
     */
    public function test_umkm_search_no_results(): void
    {
        $response = $this->get('/umkm/api-search?q=zzzznonexistent');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEmpty($data['umkms']);
        $this->assertEmpty($data['products']);
        $this->assertEmpty($data['categories']);
    }

    /**
     * Test search with special characters does not cause SQL errors.
     */
    public function test_umkm_search_special_chars(): void
    {
        $response = $this->get('/umkm/api-search?q=%25_');
        $response->assertStatus(200);
    }

    /**
     * Test search only returns active UMKM profiles.
     */
    public function test_umkm_search_only_active(): void
    {
        app()->setLocale('en');

        $owner1 = User::factory()->create(['role' => 'umkm_owner']);
        $owner2 = User::factory()->create(['role' => 'umkm_owner']);

        // Active UMKM
        $active = UmkmProfile::create([
            'user_id' => $owner1->id,
            'owner_name' => $owner1->name,
            'business_name' => ['en' => 'Kopi Active Shop'],
            'slug' => 'kopi-active',
            'is_active' => true,
        ]);
        MapLocation::create([
            'locationable_id' => $active->id,
            'locationable_type' => UmkmProfile::class,
            'latitude' => -8.4223,
            'longitude' => 115.3594,
            'name' => 'Kopi Active Shop',
            'category' => 'umkm',
            'is_accessible' => true,
        ]);

        // Inactive UMKM
        $inactive = UmkmProfile::create([
            'user_id' => $owner2->id,
            'owner_name' => $owner2->name,
            'business_name' => ['en' => 'Kopi Inactive Shop'],
            'slug' => 'kopi-inactive',
            'is_active' => false,
        ]);
        MapLocation::create([
            'locationable_id' => $inactive->id,
            'locationable_type' => UmkmProfile::class,
            'latitude' => -8.4225,
            'longitude' => 115.3596,
            'name' => 'Kopi Inactive Shop',
            'category' => 'umkm',
            'is_accessible' => true,
        ]);

        $response = $this->get('/umkm/api-search?q=kopi');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(1, $data['umkms']);
    }

    /**
     * Test search is locale-aware.
     */
    public function test_umkm_search_locale_aware(): void
    {
        $owner = User::factory()->create(['role' => 'umkm_owner']);

        // Start with English locale to create the UMKM
        app()->setLocale('en');

        $umkm = new UmkmProfile;
        $umkm->setTranslation('business_name', 'en', 'Coffee Shop');
        $umkm->setTranslation('business_name', 'id', 'Kedai Kopi');
        $umkm->user_id = $owner->id;
        $umkm->owner_name = $owner->name;
        $umkm->slug = 'kedai-kopi';
        $umkm->is_active = true;
        $umkm->save();

        MapLocation::create([
            'locationable_id' => $umkm->id,
            'locationable_type' => UmkmProfile::class,
            'latitude' => -8.4223,
            'longitude' => 115.3594,
            'name' => 'Kedai Kopi',
            'category' => 'umkm',
            'is_accessible' => true,
        ]);

        // Search in Indonesian locale - should match Kedai Kopi
        app()->setLocale('id');
        $responseId = $this->get('/umkm/api-search?q=Kedai');
        $responseId->assertStatus(200);
        $dataId = $responseId->json();
        $this->assertCount(1, $dataId['umkms']);

        // Search in English locale - should NOT match (English name is "Coffee Shop")
        app()->setLocale('en');
        $responseEn = $this->get('/umkm/api-search?q=Kedai');
        $responseEn->assertStatus(200);
        $dataEn = $responseEn->json();
        $this->assertCount(0, $dataEn['umkms']);
    }

    /**
     * Test search limits per type.
     */
    public function test_umkm_search_limits_per_type(): void
    {
        app()->setLocale('en');

        $category = UmkmProductCategory::create([
            'name' => ['en' => 'General'],
            'slug' => 'general',
        ]);

        $firstUmkmId = null;
        for ($i = 0; $i < 12; $i++) {
            $owner = User::factory()->create(['role' => 'umkm_owner']);

            $umkm = UmkmProfile::create([
                'user_id' => $owner->id,
                'owner_name' => $owner->name,
                'business_name' => ['en' => "Search Shop {$i}"],
                'slug' => "search-shop-{$i}",
                'is_active' => true,
            ]);
            MapLocation::create([
                'locationable_id' => $umkm->id,
                'locationable_type' => UmkmProfile::class,
                'latitude' => -8.4223 + ($i * 0.0001),
                'longitude' => 115.3594 + ($i * 0.0001),
                'name' => "Search Shop {$i}",
                'category' => 'umkm',
                'is_accessible' => true,
            ]);

            if ($firstUmkmId === null) {
                $firstUmkmId = $umkm->id;
            }
        }

        // Create 6 products for the first UMKM
        for ($i = 0; $i < 6; $i++) {
            UmkmProduct::create([
                'umkm_profile_id' => $firstUmkmId,
                'umkm_product_category_id' => $category->id,
                'name' => ['en' => "Search Product {$i}"],
                'slug' => "search-product-{$i}",
                'price' => 10000 + $i,
                'stock' => 10,
                'is_active' => true,
            ]);
        }

        $response = $this->get('/umkm/api-search?q=Search');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(10, $data['umkms']);
        $this->assertCount(5, $data['products']);
    }

    /**
     * Test that the directory tab loads with correct navigation.
     */
    public function test_directory_tab_loads(): void
    {
        $response = $this->get('/umkm');
        $response->assertStatus(200);
        $response->assertSee('Smart Route');
        $response->assertSee('Direktori UMKM');
    }

    /**
     * Test that existing multi-stop recommendation flow is unaffected by directory tab.
     */
    public function test_existing_recommend_flow_unaffected(): void
    {
        // 1. Create 2 UMKM Owners
        $owner1 = User::factory()->create(['role' => 'umkm_owner']);
        $owner2 = User::factory()->create(['role' => 'umkm_owner']);

        // 2. Create 2 UMKM Profiles (without dropped fields: category, ar_marker_id)
        $umkm1 = UmkmProfile::create([
            'user_id' => $owner1->id,
            'owner_name' => $owner1->name,
            'business_name' => 'Wayan Coffee',
            'slug' => 'wayan-coffee',
            'is_active' => true,
        ]);
        $umkm2 = UmkmProfile::create([
            'user_id' => $owner2->id,
            'owner_name' => $owner2->name,
            'business_name' => 'Kadek Souvenirs',
            'slug' => 'kadek-souvenirs',
            'is_active' => true,
        ]);

        // Create MapLocations for both UMKM
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

        // 4. Assign products: each UMKM has only one category (forces multi-stop)
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

        // 5. POST both categories -> forces multi-stop fallback (no single UMKM has both)
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/umkm/recommend', [
            'category_ids' => [$catFood->id, $catCraft->id],
        ]);

        // 6. Should redirect back with multi_stop_recommendations in session
        $response->assertRedirect();
        $response->assertSessionHas('multi_stop_recommendations');

        // 7. Follow redirect to /umkm
        $indexResponse = $this->from('/umkm')->get('/umkm');
        $indexResponse->assertStatus(200);

        // Session must still have it (due to session()->keep())
        $indexResponse->assertSessionHas('multi_stop_recommendations');

        // 8. Go to multi-route page
        $multiRouteResponse = $this->get('/umkm/multi-route');
        $multiRouteResponse->assertStatus(200);
        $multiRouteResponse->assertSee('Rute Belanja');
    }

    /**
     * Test that the directory tab shows active UMKM profiles.
     */
    public function test_directory_shows_umkm(): void
    {
        $owner = User::factory()->create(['role' => 'umkm_owner']);

        $umkm = UmkmProfile::create([
            'user_id' => $owner->id,
            'owner_name' => $owner->name,
            'business_name' => 'Toko Cendera Mata',
            'slug' => 'toko-cendera-mata',
            'is_active' => true,
        ]);

        MapLocation::create([
            'locationable_id' => $umkm->id,
            'locationable_type' => UmkmProfile::class,
            'latitude' => -8.4223,
            'longitude' => 115.3594,
            'name' => 'Toko Cendera Mata',
            'category' => 'umkm',
            'is_accessible' => true,
        ]);

        $response = $this->get('/umkm');
        $response->assertStatus(200);
        $response->assertSee('Toko Cendera Mata');
    }

    /**
     * Test that the directory tab hides inactive UMKM profiles.
     */
    public function test_directory_hides_inactive_umkm(): void
    {
        $owner = User::factory()->create(['role' => 'umkm_owner']);

        $umkm = UmkmProfile::create([
            'user_id' => $owner->id,
            'owner_name' => $owner->name,
            'business_name' => 'Toko Tutup',
            'slug' => 'toko-tutup',
            'is_active' => false,
        ]);

        MapLocation::create([
            'locationable_id' => $umkm->id,
            'locationable_type' => UmkmProfile::class,
            'latitude' => -8.4223,
            'longitude' => 115.3594,
            'name' => 'Toko Tutup',
            'category' => 'umkm',
            'is_accessible' => true,
        ]);

        $response = $this->get('/umkm');
        $response->assertStatus(200);
        $response->assertDontSee('Toko Tutup');
    }

    /**
     * Test that the directory tab paginates results (12 per page).
     */
    public function test_directory_pagination(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            $owner = User::factory()->create(['role' => 'umkm_owner']);

            $umkm = UmkmProfile::create([
                'user_id' => $owner->id,
                'owner_name' => $owner->name,
                'business_name' => "UMKM Test {$i}",
                'slug' => "umkm-test-{$i}",
                'is_active' => true,
            ]);
            MapLocation::create([
                'locationable_id' => $umkm->id,
                'locationable_type' => UmkmProfile::class,
                'latitude' => -8.4223 + ($i * 0.0001),
                'longitude' => 115.3594 + ($i * 0.0001),
                'name' => "UMKM Test {$i}",
                'category' => 'umkm',
                'is_accessible' => true,
            ]);
        }

        $response = $this->get('/umkm');
        $response->assertStatus(200);

        // Page 1 shows first 12 profiles
        $response->assertSee('UMKM Test 1');
        $response->assertSee('UMKM Test 12');

        // Page 2 profiles not on page 1
        $response->assertDontSee('UMKM Test 13');
        $response->assertDontSee('UMKM Test 15');

        // Load More link present since hasMorePages() is true
        $response->assertSee('Muat Lebih Banyak');
    }

    /**
     * Test that guest is redirected to login when trying to recommend.
     */
    public function test_guest_redirected_to_login_on_recommend(): void
    {
        $response = $this->post('/umkm/recommend', [
            'category_ids' => [1],
        ]);
        $response->assertRedirect('/login');
    }

    /**
     * Test that user can access the UMKM store detail page.
     */
    public function test_user_can_access_umkm_detail_page(): void
    {
        $owner = User::factory()->create(['role' => 'umkm_owner']);
        $umkm = UmkmProfile::create([
            'user_id' => $owner->id,
            'owner_name' => $owner->name,
            'business_name' => 'Wayan Coffee',
            'slug' => 'wayan-coffee',
            'is_active' => true,
        ]);

        $response = $this->get("/umkm/store/{$umkm->id}");
        $response->assertStatus(200);
        $response->assertSee('Wayan Coffee');
    }
}
