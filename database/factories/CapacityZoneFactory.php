<?php

namespace Database\Factories;

use App\Models\CapacityZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CapacityZone>
 */
class CapacityZoneFactory extends Factory
{
    protected $model = CapacityZone::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'zone_identifier' => fake()->unique()->slug(1),
            'max_capacity' => fake()->numberBetween(50, 500),
            'warning_threshold' => fake()->numberBetween(60, 80),
            'critical_threshold' => fake()->numberBetween(85, 95),
            'current_count' => 0,
            'is_active' => true,
        ];
    }
}
