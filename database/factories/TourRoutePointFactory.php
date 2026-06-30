<?php

namespace Database\Factories;

use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TourRoutePointFactory extends Factory
{
    protected $model = TourRoutePoint::class;

    public function definition(): array
    {
        return [
            'updated_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'storytelling_content' => $this->faker->words(),
            'estimated_visit_minutes' => $this->faker->randomNumber(),
            'order' => $this->faker->randomNumber(),
            'locationable_id' => $this->faker->randomNumber(),
            'locationable_type' => $this->faker->word(),

            'tour_route_id' => TourRoute::factory(),
        ];
    }
}
