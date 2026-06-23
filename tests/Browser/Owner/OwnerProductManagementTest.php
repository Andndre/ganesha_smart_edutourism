<?php

namespace Tests\Browser\Owner;

use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class OwnerProductManagementTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_edit_product(): void
    {
        $category = UmkmProductCategory::create([
            'name' => ['en' => 'Test Category', 'id' => 'Kategori Test'],
            'slug' => 'test-category-'.Str::random(5),
        ]);

        $owner = User::factory()->create(['role' => 'umkm_owner']);

        $profile = UmkmProfile::create([
            'user_id' => $owner->id,
            'owner_name' => 'Owner Dusk',
            'business_name' => 'Toko Dusk',
            'slug' => 'toko-dusk-'.Str::random(5),
            'description' => 'Toko untuk test',
            'is_active' => true,
        ]);

        $product = UmkmProduct::create([
            'umkm_profile_id' => $profile->id,
            'umkm_product_category_id' => $category->id,
            'name' => ['en' => 'Produk Original EN', 'id' => 'Produk Original ID'],
            'slug' => 'produk-original-'.Str::random(5),
            'price' => 25000,
            'stock' => 10,
            'unit' => 'pcs',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($owner) {
            $browser->loginAs($owner)
                ->visit('/owner/products')
                ->waitForLocation('/owner/products')
                ->assertSee('Produk Original')
                ->press('Ubah')
                ->waitForText('Edit Produk UMKM')
                ->clear('name[en]')
                ->type('name[en]', 'Produk Updated Dusk EN')
                ->press('Indonesia')
                ->waitFor('#field-name-id')
                ->clear('name[id]')
                ->type('name[id]', 'Produk Updated Dusk ID')
                ->press('Simpan Produk')
                ->waitForLocation('/owner/products')
                ->assertSee('Produk Updated')
                ->assertDontSee('Produk Original');
        });
    }
}
