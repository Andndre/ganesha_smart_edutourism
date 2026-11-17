<?php

namespace Tests\Feature;

use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomeFeaturedUmkmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_home_page_diversifies_featured_umkm_categories(): void
    {
        $categoryCraft = UmkmProductCategory::factory()->create([
            'name' => ['en' => 'Balinese Mask', 'id' => 'Topeng Bali'],
        ]);
        $categoryDrink = UmkmProductCategory::factory()->create([
            'name' => ['en' => 'Herbal Drink', 'id' => 'Loloh Herbal'],
        ]);

        $umkm1 = UmkmProfile::factory()->create(['is_active' => true]);
        $umkm2 = UmkmProfile::factory()->create(['is_active' => true]);

        // Both UMKM 1 and UMKM 2 have craft as their first product
        UmkmProduct::factory()->create([
            'umkm_profile_id' => $umkm1->id,
            'umkm_product_category_id' => $categoryCraft->id,
            'name' => ['en' => 'Rangda Mask', 'id' => 'Topeng Rangda'],
            'is_active' => true,
        ]);

        UmkmProduct::factory()->create([
            'umkm_profile_id' => $umkm2->id,
            'umkm_product_category_id' => $categoryCraft->id,
            'name' => ['en' => 'Rangda Mask', 'id' => 'Topeng Rangda'],
            'is_active' => true,
        ]);

        // UMKM 2 also has a drink product
        UmkmProduct::factory()->create([
            'umkm_profile_id' => $umkm2->id,
            'umkm_product_category_id' => $categoryDrink->id,
            'name' => ['en' => 'Loloh Cemcem', 'id' => 'Loloh Cemcem Segar'],
            'is_active' => true,
        ]);

        $response = $this->get(route('home'))->assertOk();

        // The view should diversify: one Topeng and one Loloh, never two identical Topeng products
        $response->assertSee('Topeng Bali');
        $response->assertSee('Loloh Herbal');
    }

    /**
     * The homepage UMKM carousel reads from a cached ->toArray() payload, so
     * `business_name` arrives as a raw translation JSON string rather than an
     * array. Guards that translateValue() unwraps it instead of printing JSON.
     */
    public function test_home_page_renders_featured_umkm_with_resolved_locale_name(): void
    {
        $umkm = UmkmProfile::factory()->create([
            'business_name' => ['en' => 'Bamboo Craft House', 'id' => 'Warung Kerajinan Bambu'],
            'is_active' => true,
            'rating' => 4.8,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Warung Kerajinan Bambu')
            ->assertDontSee('{"en"', false)
            ->assertSee(route('umkm.store', $umkm->id), false)
            ->assertSee(route('umkm', ['tab' => 'direktori']), false)
            ->assertSee('Direktori UMKM');
    }

    public function test_home_page_renders_featured_umkm_in_english_locale(): void
    {
        $umkm = UmkmProfile::factory()->create([
            'business_name' => ['en' => 'Bamboo Craft House', 'id' => 'Warung Kerajinan Bambu'],
            'is_active' => true,
        ]);

        $this->get('/?locale=en')
            ->assertOk()
            ->assertSee('Bamboo Craft House')
            ->assertSee('See all')
            ->assertSee('See All')
            ->assertSee('Merchant Directory');
    }

    public function test_home_page_hides_umkm_section_when_none_are_active(): void
    {
        UmkmProfile::factory()->create(['is_active' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('UMKM Desa');
    }
}
