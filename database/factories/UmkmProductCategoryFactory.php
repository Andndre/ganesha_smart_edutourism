<?php

namespace Database\Factories;

use App\Models\UmkmProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UmkmProductCategory>
 */
class UmkmProductCategoryFactory extends Factory
{
    protected $model = UmkmProductCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nameEn = fake()->unique()->word();
        $nameId = 'Kategori '.ucfirst($nameEn);

        return [
            'name' => ['en' => ucfirst($nameEn), 'id' => $nameId],
            'slug' => Str::slug($nameEn),
            'description' => [
                'en' => fake()->sentence(),
                'id' => 'Deskripsi untuk kategori '.$nameId,
            ],
            'price' => fake()->randomFloat(2, 10000, 200000),
            'unit' => fake()->randomElement(['pcs', 'box', 'pack', 'bottle']),
            'image_path' => null,
            'model_3d_path' => null,
            'model_3d_usdz_path' => null,
        ];
    }
}
