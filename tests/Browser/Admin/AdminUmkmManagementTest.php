<?php

namespace Tests\Browser\Admin;

use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminUmkmManagementTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_create_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'umkm_owner']);

        $profile = UmkmProfile::create([
            'user_id' => $owner->id,
            'owner_name' => 'Owner Dusk',
            'business_name' => ['en' => 'Toko Dusk', 'id' => 'Toko Dusk'],
            'slug' => 'toko-dusk',
            'is_active' => true,
        ]);

        $category = UmkmProductCategory::create([
            'name' => ['en' => 'Test Category', 'id' => 'Kategori Test'],
            'slug' => 'test-category',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $profile, $category) {
            $browser->loginAs($admin)
                ->visit('/admin/umkm')
                ->waitForText('UMKM Desa')
                ->press('Tambah Produk')
                ->waitForText('Tambah Produk UMKM')
                ->type('name[en]', 'Produk Dusk')
                ->press('Indonesia')
                ->waitFor('#field-name-id')
                ->type('name[id]', 'Produk Dusk ID')
                ->select('umkm_product_category_id', $category->id)
                ->select('umkm_profile_id', $profile->id)
                ->type('price', '50000')
                ->type('stock', '10')
                ->type('unit', 'pcs')
                ->press('Simpan')
                ->waitForText('Produk Dusk')
                ->assertSee('Produk Dusk');
        });
    }
}
