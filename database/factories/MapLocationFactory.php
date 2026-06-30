<?php

namespace Database\Factories;

use App\Models\MapLocation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MapLocationFactory extends Factory
{
    protected $model = MapLocation::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'category' => $this->faker->word(),
            'locationable_type' => $this->faker->word(),
            'locationable_id' => $this->faker->randomNumber(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'is_accessible' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'accessibility_notes' => $this->faker->words(),
        ];
    }
}
