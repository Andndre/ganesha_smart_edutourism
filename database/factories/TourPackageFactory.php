<?php

namespace Database\Factories;

use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class TourPackageFactory extends Factory
{
    protected $model = TourPackage::class;

    public function definition(): array
    {
        $nameEn = $this->faker->words(3, true);

        return [
            'slug' => $this->faker->unique()->slug(),
            'inclusions' => $this->faker->words(3),
            'exclusions' => $this->faker->words(2),
            'price' => $this->faker->randomFloat(2, 100000, 2000000),
            'duration_hours' => $this->faker->randomFloat(1, 1, 8),
            'max_capacity' => $this->faker->numberBetween(10, 30),
            'min_capacity' => 1,
            'images' => null,
            'is_active' => true,
            'description' => ['en' => $this->faker->paragraph(), 'id' => $this->faker->paragraph()],
            'name' => ['en' => ucfirst($nameEn), 'id' => 'Paket '.ucfirst($nameEn)],
        ];
    }
}
