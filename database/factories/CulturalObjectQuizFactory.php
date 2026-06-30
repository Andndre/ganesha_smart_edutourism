<?php

namespace Database\Factories;

use App\Models\CulturalObject;
use App\Models\CulturalObjectQuiz;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CulturalObjectQuizFactory extends Factory
{
    protected $model = CulturalObjectQuiz::class;

    public function definition(): array
    {
        return [
            'correct_option' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'question' => $this->faker->word(),
            'option_a' => $this->faker->word(),
            'option_b' => $this->faker->word(),
            'option_c' => $this->faker->word(),
            'option_d' => $this->faker->word(),

            'cultural_object_id' => CulturalObject::factory(),
        ];
    }
}
