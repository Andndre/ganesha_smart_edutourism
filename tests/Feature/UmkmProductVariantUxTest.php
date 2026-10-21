<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmProductVariantUxTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private UmkmProfile $profile;

    private UmkmProductCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'role' => UserRole::UmkmOwner,
        ]);

        $this->profile = UmkmProfile::create([
            'user_id' => $this->owner->id,
            'owner_name' => 'Ni Wayan Suri',
            'business_name' => ['id' => 'Warung Suri', 'en' => 'Suri Shop'],
            'slug' => 'warung-suri',
            'is_active' => true,
        ]);

        $this->category = UmkmProductCategory::create([
            'name' => ['id' => 'Camilan Khas', 'en' => 'Traditional Snacks'],
            'slug' => 'camilan-khas',
            'price' => 15000,
            'unit' => 'pack',
        ]);
    }

    public function test_owner_can_add_variants_without_providing_english_label(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.products.store'), [
            'umkm_product_category_id' => $this->category->id,
            'is_active' => 1,
            'variants' => [
                [
                    'label' => [
                        'id' => 'Ukuran Sedang (250gr)',
                        'en' => '', // left empty by merchant
                    ],
                    'price' => 20000,
                    'stock' => 15,
                    'is_active' => 1,
                ],
                [
                    'label' => [
                        'id' => 'Ukuran Besar (500gr)',
                    ],
                    'price' => 35000,
                    'stock' => 10,
                    'is_active' => 1,
                ],
            ],
        ]);

        $response->assertRedirect(route('owner.products'));

        $product = UmkmProduct::where('umkm_profile_id', $this->profile->id)->firstOrFail();
        $this->assertCount(2, $product->variants);

        $v1 = $product->variants()->where('price', 20000)->firstOrFail();
        $this->assertEquals('Ukuran Sedang (250gr)', $v1->getTranslation('label', 'id'));
        // Automatically populated from id
        $this->assertEquals('Ukuran Sedang (250gr)', $v1->getTranslation('label', 'en'));

        $v2 = $product->variants()->where('price', 35000)->firstOrFail();
        $this->assertEquals('Ukuran Besar (500gr)', $v2->getTranslation('label', 'id'));
        $this->assertEquals('Ukuran Besar (500gr)', $v2->getTranslation('label', 'en'));
    }

    public function test_owner_products_modal_renders_human_friendly_variant_ui(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.products'));

        $response->assertStatus(200);
        $response->assertSee('Pilihan Varian Produk');
        $response->assertSee('Contoh: Rasa Pedas, Ukuran L, Isi 10 Pcs, 250gr');
        $response->assertSee('Harga Varian');
        $response->assertSee('Tambah terjemahan Bahasa Inggris (opsional)');
        $response->assertDontSee('placeholder="Label ID"', false);
        $response->assertDontSee('placeholder="Label EN"', false);
    }
}
