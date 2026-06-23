<?php

namespace Tests\Browser;

use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PublicExploreTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_map_renders(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/explore')
                ->waitFor('#map', 10)
                ->waitFor('.leaflet-container', 15)
                ->assertPresent('#search-input');
        });
    }

    public function test_umkm_catalog_displays(): void
    {
        $user = User::factory()->create();
        $profile = UmkmProfile::create([
            'user_id' => $user->id,
            'owner_name' => 'Owner Test',
            'business_name' => 'Toko Dusk Test',
            'slug' => 'toko-dusk-test',
            'is_active' => true,
        ]);
        UmkmProduct::create([
            'umkm_profile_id' => $profile->id,
            'name' => 'Produk Dusk Test',
            'slug' => 'produk-dusk-test',
            'price' => 50000,
            'is_active' => true,
            'stock' => 10,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/umkm')
                // Default tab is Smart Route — text is translated to English
                ->waitForText('Explore Merchants', 10)
                ->assertSee('Explore Merchants')
                // Assert new tabbed layout: both tab labels visible
                ->assertSee('Smart Route')
                ->assertSee('Merchant Directory')
                // Assert omni-search bar present (English placeholder)
                ->assertPresent('input[placeholder*="Merchant"]')
                // Switch to Merchant Directory tab and verify UMKM card
                ->press('Merchant Directory')
                ->waitForText('Toko Dusk Test', 5)
                ->assertSee('Toko Dusk Test')
                // Switch back to Smart Route tab
                ->press('Smart Route')
                ->waitForText('Explore Merchants', 5)
                ->assertSee('Explore Merchants');
        });
    }

    public function test_umkm_category_filter(): void
    {
        $category = UmkmProductCategory::create([
            'name' => ['en' => 'Kategori Dusk Test EN', 'id' => 'Kategori Dusk Test ID'],
            'slug' => 'kategori-dusk-test',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/umkm')
                ->waitForText('Explore Merchants', 10)
                ->assertSee('Explore Merchants')
                // Category card should appear in Smart Route tab
                ->assertSee('Kategori Dusk Test EN');
        });
    }
}
