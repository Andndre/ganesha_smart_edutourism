<?php

namespace Tests\Unit;

use App\Models\MapLocation;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use App\Services\UmkmRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UmkmRecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    private UmkmRecommendationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UmkmRecommendationService;
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeOwner(): User
    {
        return User::factory()->create(['role' => 'umkm_owner']);
    }

    private function makeUmkm(User $owner, array $overrides = []): UmkmProfile
    {
        return UmkmProfile::create(array_merge([
            'user_id' => $owner->id,
            'owner_name' => $owner->name,
            'business_name' => 'UMKM '.$owner->id,
            'slug' => 'umkm-'.$owner->id,
            'is_active' => true,
            'recommendation_count' => 0,
        ], $overrides));
    }

    private function makeCategory(string $name = 'Kopi'): UmkmProductCategory
    {
        return UmkmProductCategory::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
        ]);
    }

    private function makeProduct(UmkmProfile $umkm, UmkmProductCategory $category, array $overrides = []): UmkmProduct
    {
        return UmkmProduct::create(array_merge([
            'umkm_profile_id' => $umkm->id,
            'umkm_product_category_id' => $category->id,
            'name' => 'Produk',
            'slug' => 'produk-'.uniqid(),
            'is_active' => true,
            'stock' => null, // unlimited
        ], $overrides));
    }

    private function attachLocation(UmkmProfile $umkm, float $lat = -8.4, float $lng = 115.3): MapLocation
    {
        return MapLocation::create([
            'locationable_type' => UmkmProfile::class,
            'locationable_id' => $umkm->id,
            'name' => $umkm->business_name,
            'category' => 'umkm',
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
    }

    // -----------------------------------------------------------------------
    // recommendForCategories
    // -----------------------------------------------------------------------

    public function test_recommend_for_categories_returns_null_for_empty_input(): void
    {
        $result = $this->service->recommendForCategories([]);

        $this->assertNull($result);
    }

    public function test_recommend_for_categories_returns_null_when_no_active_umkm(): void
    {
        $category = $this->makeCategory();

        $result = $this->service->recommendForCategories([$category->id]);

        $this->assertNull($result);
    }

    public function test_recommend_for_categories_returns_matching_umkm(): void
    {
        $owner = $this->makeOwner();
        $umkm = $this->makeUmkm($owner);
        $category = $this->makeCategory();
        $this->makeProduct($umkm, $category);

        $result = $this->service->recommendForCategories([$category->id]);

        $this->assertNotNull($result);
        $this->assertEquals($umkm->id, $result->id);
    }

    public function test_recommend_for_categories_increments_recommendation_count(): void
    {
        $owner = $this->makeOwner();
        $umkm = $this->makeUmkm($owner, ['recommendation_count' => 0]);
        $category = $this->makeCategory();
        $this->makeProduct($umkm, $category);

        $this->service->recommendForCategories([$category->id]);

        $this->assertDatabaseHas('umkm_profiles', [
            'id' => $umkm->id,
            'recommendation_count' => 1,
        ]);
    }

    public function test_recommend_for_categories_prefers_lower_recommendation_count(): void
    {
        $category = $this->makeCategory();

        $owner1 = $this->makeOwner();
        $umkmHigh = $this->makeUmkm($owner1, ['recommendation_count' => 10]);
        $this->makeProduct($umkmHigh, $category);

        $owner2 = $this->makeOwner();
        $umkmLow = $this->makeUmkm($owner2, ['recommendation_count' => 0]);
        $this->makeProduct($umkmLow, $category);

        // Run multiple times — should always prefer the lower-count one (ties broken randomly,
        // but with count 0 vs 10 the lower should win every time in practice)
        for ($i = 0; $i < 5; $i++) {
            $result = $this->service->recommendForCategories([$category->id]);
            $this->assertEquals($umkmLow->id, $result->id);
            // Reset counts to maintain the gap
            $umkmHigh->recommendation_count = 10;
            $umkmHigh->save();
            $umkmLow->recommendation_count = 0;
            $umkmLow->save();
        }
    }

    public function test_recommend_for_categories_skips_inactive_umkm(): void
    {
        $owner = $this->makeOwner();
        $umkm = $this->makeUmkm($owner, ['is_active' => false]);
        $category = $this->makeCategory();
        $this->makeProduct($umkm, $category);

        $result = $this->service->recommendForCategories([$category->id]);

        $this->assertNull($result);
    }

    public function test_recommend_for_categories_skips_out_of_stock_products(): void
    {
        $owner = $this->makeOwner();
        $umkm = $this->makeUmkm($owner);
        $category = $this->makeCategory();
        $this->makeProduct($umkm, $category, ['stock' => 0]);

        $result = $this->service->recommendForCategories([$category->id]);

        $this->assertNull($result);
    }

    public function test_recommend_for_categories_requires_all_categories_to_be_present(): void
    {
        $cat1 = $this->makeCategory('Kopi');
        $cat2 = $this->makeCategory('Kerajinan');

        $owner = $this->makeOwner();
        $umkm = $this->makeUmkm($owner);
        $this->makeProduct($umkm, $cat1); // only has cat1, not cat2

        $result = $this->service->recommendForCategories([$cat1->id, $cat2->id]);

        $this->assertNull($result);
    }

    // -----------------------------------------------------------------------
    // recommendMultipleForCategories
    // -----------------------------------------------------------------------

    public function test_recommend_multiple_returns_null_for_empty_input(): void
    {
        $result = $this->service->recommendMultipleForCategories([]);

        $this->assertNull($result);
    }

    public function test_recommend_multiple_returns_null_when_no_active_umkm(): void
    {
        $category = $this->makeCategory();

        $result = $this->service->recommendMultipleForCategories([$category->id]);

        $this->assertNull($result);
    }

    public function test_recommend_multiple_single_umkm_covers_all_categories(): void
    {
        $cat1 = $this->makeCategory('Kopi');
        $cat2 = $this->makeCategory('Jajan');

        $owner = $this->makeOwner();
        $umkm = $this->makeUmkm($owner);
        $this->attachLocation($umkm);
        $this->makeProduct($umkm, $cat1);
        $this->makeProduct($umkm, $cat2);

        $result = $this->service->recommendMultipleForCategories([$cat1->id, $cat2->id]);

        $this->assertNotNull($result);
        $this->assertCount(1, $result['route']);
        $this->assertEquals($umkm->id, $result['route'][0]['umkm_id']);
        $this->assertEmpty($result['missing']);
    }

    public function test_recommend_multiple_uses_two_umkms_when_needed(): void
    {
        $cat1 = $this->makeCategory('Kopi');
        $cat2 = $this->makeCategory('Kerajinan');

        $owner1 = $this->makeOwner();
        $umkm1 = $this->makeUmkm($owner1);
        $this->attachLocation($umkm1, -8.40, 115.30);
        $this->makeProduct($umkm1, $cat1);

        $owner2 = $this->makeOwner();
        $umkm2 = $this->makeUmkm($owner2);
        $this->attachLocation($umkm2, -8.41, 115.31);
        $this->makeProduct($umkm2, $cat2);

        $result = $this->service->recommendMultipleForCategories([$cat1->id, $cat2->id]);

        $this->assertNotNull($result);
        $this->assertCount(2, $result['route']);
        $this->assertEmpty($result['missing']);

        $routeUmkmIds = array_column($result['route'], 'umkm_id');
        $this->assertContains($umkm1->id, $routeUmkmIds);
        $this->assertContains($umkm2->id, $routeUmkmIds);
    }

    public function test_recommend_multiple_reports_missing_categories(): void
    {
        $cat1 = $this->makeCategory('Kopi');
        $cat2 = $this->makeCategory('Kerajinan'); // no UMKM has this

        $owner = $this->makeOwner();
        $umkm = $this->makeUmkm($owner);
        $this->attachLocation($umkm);
        $this->makeProduct($umkm, $cat1);

        $result = $this->service->recommendMultipleForCategories([$cat1->id, $cat2->id]);

        $this->assertNotNull($result);
        $this->assertCount(1, $result['route']);
        $this->assertContains($cat2->id, $result['missing']);
    }

    public function test_recommend_multiple_does_not_revisit_same_umkm(): void
    {
        $cat1 = $this->makeCategory('Kopi');
        $cat2 = $this->makeCategory('Jajan');

        $owner = $this->makeOwner();
        $umkm = $this->makeUmkm($owner);
        $this->attachLocation($umkm);
        $this->makeProduct($umkm, $cat1);
        $this->makeProduct($umkm, $cat2);

        $result = $this->service->recommendMultipleForCategories([$cat1->id, $cat2->id]);

        // The single UMKM covers both — should appear only once
        $this->assertNotNull($result);
        $this->assertCount(1, $result['route']);
    }

    public function test_recommend_multiple_increments_recommendation_count(): void
    {
        $cat1 = $this->makeCategory('Kopi');

        $owner = $this->makeOwner();
        $umkm = $this->makeUmkm($owner, ['recommendation_count' => 0]);
        $this->attachLocation($umkm);
        $this->makeProduct($umkm, $cat1);

        $this->service->recommendMultipleForCategories([$cat1->id]);

        $this->assertDatabaseHas('umkm_profiles', [
            'id' => $umkm->id,
            'recommendation_count' => 1,
        ]);
    }
}
