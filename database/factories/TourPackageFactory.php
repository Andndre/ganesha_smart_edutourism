<?php

namespace Database\Factories;

use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TourPackageFactory extends Factory
{
    protected $model = TourPackage::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->slug(),
            'inclusions' => $this->faker->words(),
            'exclusions' => $this->faker->words(),
            'price' => $this->faker->word(),
            'duration_hours' => $this->faker->word(),
            'max_capacity' => $this->faker->randomNumber(),
            'min_capacity' => $this->faker->randomNumber(),
            'images' => $this->faker->word(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'description' => $this->faker->text(),
            'name' => $this->faker->name(),
        ];
    }
}
