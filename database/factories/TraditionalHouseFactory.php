<?php

namespace Database\Factories;

use App\Models\TraditionalHouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TraditionalHouse>
 */
class TraditionalHouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numberBetween(1, 9999),
            'head_name' => fake()->name(),
            'male_count' => 2,
            'female_count' => 2,
            'description' => ['id' => 'Pekarangan tradisional Penglipuran.', 'en' => 'A traditional Penglipuran courtyard.'],
            'is_open_to_visitors' => true,
            'is_published' => false,
        ];
    }
}
