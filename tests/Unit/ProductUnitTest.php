<?php

namespace Tests\Unit;

use App\Enums\ProductUnit;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_unit_cases_and_dropdown_labels(): void
    {
        $this->assertNotEmpty(ProductUnit::cases());

        foreach (ProductUnit::cases() as $case) {
            $this->assertNotEmpty($case->dropdownLabel());
            $this->assertNotEmpty($case->label('id'));
            $this->assertNotEmpty($case->label('en'));
        }
    }

    public function test_normalization_of_aliases(): void
    {
        $this->assertEquals('portion', ProductUnit::normalize('porsi'));
        $this->assertEquals('portion', ProductUnit::normalize('portion'));
        $this->assertEquals('portion', ProductUnit::normalize('Portion '));
        $this->assertEquals('bottle', ProductUnit::normalize('botol'));
        $this->assertEquals('bottle', ProductUnit::normalize('Bottle'));
        $this->assertEquals('pack', ProductUnit::normalize('bungkus'));
        $this->assertEquals('pack', ProductUnit::normalize('paket'));
        $this->assertEquals('box', ProductUnit::normalize('kotak'));
        $this->assertEquals('box', ProductUnit::normalize('dus'));
        $this->assertEquals('pcs', ProductUnit::normalize('buah'));
        $this->assertEquals('pcs', ProductUnit::normalize('pcs'));
        $this->assertEquals('kg', ProductUnit::normalize('kilogram'));
        $this->assertNull(ProductUnit::normalize(''));
        $this->assertNull(ProductUnit::normalize(null));
    }

    public function test_label_for_translates_correctly(): void
    {
        // Indonesian locale
        $this->assertEquals('porsi', ProductUnit::labelFor('portion', 'id'));
        $this->assertEquals('porsi', ProductUnit::labelFor('porsi', 'id'));
        $this->assertEquals('botol', ProductUnit::labelFor('bottle', 'id'));
        $this->assertEquals('botol', ProductUnit::labelFor('botol', 'id'));
        $this->assertEquals('bungkus', ProductUnit::labelFor('pack', 'id'));

        // English locale
        $this->assertEquals('portion', ProductUnit::labelFor('portion', 'en'));
        $this->assertEquals('portion', ProductUnit::labelFor('porsi', 'en'));
        $this->assertEquals('bottle', ProductUnit::labelFor('bottle', 'en'));
        $this->assertEquals('bottle', ProductUnit::labelFor('botol', 'en'));
        $this->assertEquals('pack', ProductUnit::labelFor('bungkus', 'en'));

        // Fallback for custom or empty
        $this->assertEquals('', ProductUnit::labelFor(null));
        $this->assertEquals('custom_unit', ProductUnit::labelFor('custom_unit'));
    }

    public function test_model_mutator_and_display_unit_translation(): void
    {
        $category = UmkmProductCategory::create([
            'name' => ['id' => 'Kategori Makanan', 'en' => 'Food Category'],
            'slug' => 'kategori-makanan',
            'description' => ['id' => 'Deskripsi', 'en' => 'Description'],
            'price' => 15000,
            'unit' => 'porsi', // should be normalized to portion
        ]);

        $this->assertEquals('portion', $category->unit);

        app()->setLocale('id');
        $this->assertEquals('porsi', $category->display_unit);

        app()->setLocale('en');
        $this->assertEquals('portion', $category->display_unit);

        $user = User::factory()->create();
        $profile = UmkmProfile::create([
            'user_id' => $user->id,
            'owner_name' => 'Owner Test',
            'business_name' => ['id' => 'Warung Test', 'en' => 'Test Stall'],
            'slug' => 'warung-test',
        ]);

        $product = UmkmProduct::create([
            'umkm_profile_id' => $profile->id,
            'umkm_product_category_id' => $category->id,
            'name' => ['id' => 'Tipat Cantok', 'en' => 'Tipat Cantok'],
            'slug' => 'tipat-cantok-test',
            'price' => 15000,
            'unit' => null, // inherits from category
        ]);

        app()->setLocale('id');
        $this->assertEquals('porsi', $product->display_unit);

        app()->setLocale('en');
        $this->assertEquals('portion', $product->display_unit);

        // When category has no unit, product's own unit is used
        $categoryNoUnit = UmkmProductCategory::create([
            'name' => ['id' => 'Kategori Tanpa Satuan', 'en' => 'Category No Unit'],
            'slug' => 'kategori-tanpa-satuan',
            'unit' => null,
        ]);

        $productWithOwnUnit = UmkmProduct::create([
            'umkm_profile_id' => $profile->id,
            'umkm_product_category_id' => $categoryNoUnit->id,
            'name' => ['id' => 'Loloh Cemcem', 'en' => 'Loloh Cemcem'],
            'slug' => 'loloh-cemcem-test',
            'unit' => 'botol',
        ]);

        $this->assertEquals('bottle', $productWithOwnUnit->unit);

        app()->setLocale('id');
        $this->assertEquals('botol', $productWithOwnUnit->display_unit);

        app()->setLocale('en');
        $this->assertEquals('bottle', $productWithOwnUnit->display_unit);
    }
}
