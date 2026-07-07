<?php

namespace Database\Factories;

use App\Models\CulturalObject;
use App\Models\CulturalObjectRating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CulturalObjectRating>
 */
class CulturalObjectRatingFactory extends Factory
{
    protected $model = CulturalObjectRating::class;

    public function definition(): array
    {
        return [
            'cultural_object_id' => CulturalObject::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
