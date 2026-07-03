<?php

namespace Database\Factories;

use App\Models\CulturalObject;
use App\Models\CulturalObjectQuiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class CulturalObjectQuizFactory extends Factory
{
    protected $model = CulturalObjectQuiz::class;

    public function definition(): array
    {
        // ponytail: Faker's Lorem provider has no id_ID text generator, so "id" would
        // read identically to "en" (both Latin lorem-ipsum). Prefix "id" to make the
        // two locales visibly distinct in seeded/demo data.
        $bilingual = fn () => ['en' => $this->faker->words(3, true), 'id' => '[ID] '.$this->faker->words(3, true)];

        return [
            'correct_option' => $this->faker->randomElement(['a', 'b', 'c', 'd']),
            'question' => ['en' => $this->faker->sentence().'?', 'id' => '[ID] '.$this->faker->sentence().'?'],
            'option_a' => $bilingual(),
            'option_b' => $bilingual(),
            'option_c' => $bilingual(),
            'option_d' => $bilingual(),

            'cultural_object_id' => CulturalObject::factory(),
        ];
    }
}
