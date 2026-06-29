<?php

namespace Database\Factories;

use App\Models\CapacityZone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CapacityZoneFactory extends Factory
{
    protected $model = CapacityZone::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'zone_identifier' => $this->faker->word(),
            'max_capacity' => $this->faker->randomNumber(),
            'warning_threshold' => $this->faker->randomNumber(),
            'critical_threshold' => $this->faker->randomNumber(),
            'current_count' => $this->faker->randomNumber(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'polygon_coordinates' => $this->faker->word(),
        ];
    }
}
