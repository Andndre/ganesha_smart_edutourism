<?php

namespace Database\Factories;

use App\Models\TourRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourRoute>
 */
class TourRouteFactory extends Factory
{
    protected $model = TourRoute::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'difficulty' => fake()->randomElement(['easy', 'moderate', 'challenging']),
            'is_active' => true,

            'estimated_duration_minutes' => fake()->numberBetween(30, 180),
            'distance_meters' => fake()->numberBetween(100, 5000),
        ];
    }
}
