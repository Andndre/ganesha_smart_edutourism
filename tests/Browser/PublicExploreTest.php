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
            'description' => 'Test',
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
                ->waitForText('Jelajah UMKM', 10)
                ->assertSee('Jelajah UMKM');
        });
    }

    public function test_umkm_category_filter(): void
    {
        UmkmProductCategory::create([
            'name' => ['en' => 'Kategori Dusk Test EN', 'id' => 'Kategori Dusk Test ID'],
            'slug' => 'kategori-dusk-test',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/umkm')
                ->waitForText('Jelajah UMKM', 10)
                ->assertSee('Jelajah UMKM');
        });
    }
}
