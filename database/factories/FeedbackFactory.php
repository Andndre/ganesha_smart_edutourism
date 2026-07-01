<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        return [
            'feedback_type' => $this->faker->randomElement(['general', 'cultural', 'service', 'facility', 'umkm']),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->sentence(),
            'photos' => null,
            'is_public' => $this->faker->boolean(80),
            'admin_response' => null,

            'user_id' => User::factory(),
            'reservation_id' => Reservation::factory(),
        ];
    }
}
