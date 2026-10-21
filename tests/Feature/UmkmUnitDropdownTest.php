<?php

namespace Tests\Feature;

use App\Enums\ProductUnit;
use App\Enums\UserRole;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UmkmUnitDropdownTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $owner;

    private UmkmProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->owner = User::factory()->create([
            'role' => UserRole::UmkmOwner,
        ]);

        $this->profile = UmkmProfile::create([
            'user_id' => $this->owner->id,
            'owner_name' => 'Pemilik Toko',
            'business_name' => ['id' => 'Toko Barokah', 'en' => 'Barokah Shop'],
            'slug' => 'toko-barokah',
            'is_active' => true,
        ]);
    }

    public function test_admin_category_page_renders_unit_dropdown_with_options(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.umkm.categories'));

        $response->assertStatus(200);
        $response->assertSee('<select name="unit" id="field-unit"', false);
        $response->assertSee('Porsi (portion)');
        $response->assertSee('Botol (bottle)');
        $response->assertSee('Bungkus / Pack (pack)');
    }

    public function test_admin_can_create_category_with_dropdown_unit(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)->post(route('admin.umkm.categories.store'), [
            'name' => ['id' => 'Minuman Herbal', 'en' => 'Herbal Drinks'],
            'description' => ['id' => 'Minuman khas Bali', 'en' => 'Balinese traditional drinks'],
            'price' => 10000,
            'unit' => 'bottle',
            'is_culinary' => 1,
        ]);

        $response->assertRedirect(route('admin.umkm.categories'));

        $category = UmkmProductCategory::where('name->en', 'Herbal Drinks')->firstOrFail();
        $this->assertEquals('bottle', $category->unit);

        // Verification of automatic translation
        app()->setLocale('id');
        $this->assertEquals('botol', $category->display_unit);

        app()->setLocale('en');
        $this->assertEquals('bottle', $category->display_unit);
    }

    public function test_owner_products_page_renders_unit_dropdown_with_options(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.products'));

        $response->assertStatus(200);
        $response->assertSee('<select name="unit" id="field-unit"', false);
        $response->assertSee('<select name="unit" id="cat-unit"', false);
        $response->assertSee('Sama seperti kategori (atau pilih satuan)');
        $response->assertSee('Porsi (portion)');
        $response->assertSee('Botol (bottle)');
    }

    public function test_owner_can_create_custom_category_with_unit(): void
    {
        $response = $this->actingAs($this->owner)->postJson(route('owner.categories.store'), [
            'name' => 'Kopi Tradisional',
            'price' => 12000,
            'unit' => 'cup',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'unit' => 'cup',
            'unit_label' => ProductUnit::labelFor('cup', app()->getLocale()),
        ]);

        $category = UmkmProductCategory::where('name->id', 'Kopi Tradisional')->firstOrFail();
        $this->assertEquals('cup', $category->unit);
    }

    public function test_catalog_index_translates_unit_based_on_locale(): void
    {
        UmkmProductCategory::create([
            'name' => ['id' => 'Makanan Ringan', 'en' => 'Light Snacks'],
            'slug' => 'makanan-ringan',
            'description' => ['id' => 'Deskripsi', 'en' => 'Description'],
            'price' => 5000,
            'unit' => 'portion',
        ]);

        // Request in English
        $responseEn = $this->get('/umkm?locale=en');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('/ portion');

        // Request in Indonesian
        $responseId = $this->get('/umkm?locale=id');
        $responseId->assertStatus(200);
        $responseId->assertSee('/ porsi');
    }
}
