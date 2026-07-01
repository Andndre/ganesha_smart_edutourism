<?php

namespace Database\Factories;

use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use Illuminate\Database\Eloquent\Factories\Factory;

// ponytail: no default `locationable` — TourRoutePoint always points at an
// existing CulturalObject/Facility, never a throwaway one. Callers must pass
// it explicitly (e.g. ->for($culturalObject, 'locationable')).
class TourRoutePointFactory extends Factory
{
    protected $model = TourRoutePoint::class;

    public function definition(): array
    {
        return [
            'storytelling_content' => ['en' => $this->faker->paragraph(), 'id' => $this->faker->paragraph()],
            'estimated_visit_minutes' => $this->faker->numberBetween(5, 30),
            'order' => $this->faker->numberBetween(1, 10),

            'tour_route_id' => TourRoute::factory(),
        ];
    }
}
