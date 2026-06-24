<?php

namespace Database\Factories;

use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UmkmProduct>
 */
class UmkmProductFactory extends Factory
{
    protected $model = UmkmProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nameEn = fake()->unique()->words(2, true);
        $nameId = 'Produk '.ucfirst($nameEn);

        return [
            'umkm_profile_id' => UmkmProfile::factory(),
            'umkm_product_category_id' => UmkmProductCategory::factory(),
            'name' => ['en' => ucfirst($nameEn), 'id' => $nameId],
            'slug' => Str::slug($nameEn),
            'description' => [
                'en' => fake()->sentence(),
                'id' => 'Deskripsi untuk '.$nameId,
            ],
            'price' => fake()->randomFloat(2, 10000, 200000),
            'stock' => fake()->numberBetween(0, 50),
            'unit' => fake()->randomElement(['pcs', 'box', 'pack', 'bottle']),
            'images' => null,
            'is_active' => true,
        ];
    }
}
