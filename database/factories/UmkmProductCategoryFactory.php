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
        $suffix = Str::lower(Str::random(6));
        $nameEn = ucfirst(fake()->word()).' '.$suffix;
        $nameId = 'Kategori '.$nameEn;

        return [
            'name' => ['en' => $nameEn, 'id' => $nameId],
            'slug' => Str::slug($nameEn.'-'.$suffix),
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
