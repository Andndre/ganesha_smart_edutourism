<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorLogFactory extends Factory
{
    protected $model = VisitorLog::class;

    public function definition(): array
    {
        return [
            'session_id' => $this->faker->uuid(),
            'event_type' => $this->faker->randomElement(['page_view', 'feature_use', 'location_visit', 'purchase']),
            'event_data' => ['page' => $this->faker->word()],
            'latitude' => $this->faker->latitude(-8.5, -8.4),
            'longitude' => $this->faker->longitude(115.3, 115.4),
            'device_type' => $this->faker->randomElement(['mobile', 'desktop', 'tablet']),
            'browser' => $this->faker->randomElement(['Chrome', 'Safari', 'Firefox', 'Edge']),
            'nationality' => $this->faker->countryCode(),
            'logged_at' => now(),

            'user_id' => User::factory(),
        ];
    }
}
