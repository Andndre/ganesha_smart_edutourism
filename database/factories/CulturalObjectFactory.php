<?php

namespace Database\Factories;

use App\Models\CulturalObject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CulturalObject>
 */
class CulturalObjectFactory extends Factory
{
    protected $model = CulturalObject::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'slug' => fake()->unique()->slug(),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['temple', 'house', 'craft', 'tradition']),
        ];
    }
}
