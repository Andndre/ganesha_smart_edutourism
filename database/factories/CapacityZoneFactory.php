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
            'max_capacity' => $this->faker->randomNumber(2, 100),
            'warning_threshold' => $this->faker->randomNumber(2, 50),
            'critical_threshold' => $this->faker->randomNumber(2, 30),
            'current_count' => $this->faker->randomNumber(1, 50),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'polygon_coordinates' => [
                ['lat' => $this->faker->latitude(-8.5, -8.4), 'lng' => $this->faker->longitude(115.3, 115.4)],
                ['lat' => $this->faker->latitude(-8.5, -8.4), 'lng' => $this->faker->longitude(115.3, 115.4)],
                ['lat' => $this->faker->latitude(-8.5, -8.4), 'lng' => $this->faker->longitude(115.3, 115.4)],
            ],
        ];
    }
}
