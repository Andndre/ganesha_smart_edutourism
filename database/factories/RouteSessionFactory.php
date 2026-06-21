<?php

namespace Database\Factories;

use App\Models\RouteSession;
use App\Models\User;
use App\Models\TourRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RouteSession>
 */
class RouteSessionFactory extends Factory
{
    protected $model = RouteSession::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tour_route_id' => TourRoute::factory(),
            'status' => fake()->randomElement(['active', 'completed', 'abandoned']),
        ];
    }
}
