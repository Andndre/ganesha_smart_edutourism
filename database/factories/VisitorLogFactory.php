<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VisitorLogFactory extends Factory
{
    protected $model = VisitorLog::class;

    public function definition(): array
    {
        return [
            'session_id' => $this->faker->word(),
            'event_type' => $this->faker->word(),
            'event_data' => $this->faker->word(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'device_type' => $this->faker->word(),
            'browser' => $this->faker->word(),
            'nationality' => $this->faker->word(),
            'logged_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }
}
