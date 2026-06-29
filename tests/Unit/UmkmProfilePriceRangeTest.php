<?php

namespace Tests\Unit;

use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmProfilePriceRangeTest extends TestCase
{
    use RefreshDatabase;

    private function makeProfile(): UmkmProfile
    {
        $user = User::factory()->create(['role' => 'umkm_owner']);

        return UmkmProfile::create([
            'user_id' => $user->id,
            'owner_name' => $user->name,
            'business_name' => 'Toko Test',
            'slug' => 'toko-test-'.uniqid(),
            'is_active' => true,
        ]);
    }

    private function makeCategory(?float $price = null): UmkmProductCategory
    {
        $uid = uniqid();

        return UmkmProductCategory::create([
            'name' => 'Kategori '.$uid,
            'slug' => 'kat-'.$uid,
            'price' => $price,
        ]);
    }

    private function makeProduct(UmkmProfile $profile, UmkmProductCategory $cat, ?float $price = null): UmkmProduct
    {
        return UmkmProduct::create([
            'umkm_profile_id' => $profile->id,
            'umkm_product_category_id' => $cat->id,
            'name' => 'Produk',
            'slug' => 'produk-'.uniqid(),
            'is_active' => true,
            'price' => $price,
        ]);
    }

    public function test_returns_null_when_no_products(): void
    {
        $profile = $this->makeProfile();
        $profile->load('activeProducts.category');

        $this->assertNull($profile->price_range);
    }

    public function test_returns_null_when_all_prices_are_null(): void
    {
        $profile = $this->makeProfile();
        $category = $this->makeCategory(null);
        $this->makeProduct($profile, $category, null);
        $profile->load('activeProducts.category');

        $this->assertNull($profile->price_range);
    }

    public function test_single_product_min_equals_max(): void
    {
        $profile = $this->makeProfile();
        $category = $this->makeCategory(null);
        $this->makeProduct($profile, $category, 25000);
        $profile->load('activeProducts.category');

        $range = $profile->price_range;
        $this->assertEquals(25000.0, $range['min']);
        $this->assertEquals(25000.0, $range['max']);
    }

    public function test_category_price_overrides_product_price(): void
    {
        $profile = $this->makeProfile();
        $category = $this->makeCategory(50000);
        $this->makeProduct($profile, $category, 10000);
        $profile->load('activeProducts.category');

        $range = $profile->price_range;
        $this->assertEquals(50000.0, $range['min']);
        $this->assertEquals(50000.0, $range['max']);
    }

    public function test_returns_correct_min_and_max_across_products(): void
    {
        $profile = $this->makeProfile();
        $cat1 = $this->makeCategory(null);
        $cat2 = $this->makeCategory(null);
        $this->makeProduct($profile, $cat1, 10000);
        $this->makeProduct($profile, $cat2, 75000);
        $profile->load('activeProducts.category');

        $range = $profile->price_range;
        $this->assertEquals(10000.0, $range['min']);
        $this->assertEquals(75000.0, $range['max']);
    }
}
