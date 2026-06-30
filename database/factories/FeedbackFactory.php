<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        return [
            'feedback_type' => $this->faker->word(),
            'rating' => $this->faker->randomNumber(),
            'comment' => $this->faker->word(),
            'photos' => $this->faker->words(),
            'is_public' => $this->faker->boolean(),
            'admin_response' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
            'reservation_id' => Reservation::factory(),
        ];
    }
}
