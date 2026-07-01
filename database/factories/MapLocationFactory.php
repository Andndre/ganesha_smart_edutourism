<?php

namespace Database\Factories;

use App\Models\CulturalObject;
use App\Models\MapLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class MapLocationFactory extends Factory
{
    protected $model = MapLocation::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'category' => $this->faker->randomElement(['cultural', 'umkm', 'facility', 'emergency', 'accessibility']),
            'locationable_type' => CulturalObject::class,
            'locationable_id' => CulturalObject::factory(),
            'latitude' => $this->faker->latitude(-8.5, -8.4),
            'longitude' => $this->faker->longitude(115.3, 115.4),
            'is_accessible' => $this->faker->boolean(),
            'accessibility_notes' => ['en' => $this->faker->sentence(), 'id' => $this->faker->sentence()],
        ];
    }
}
