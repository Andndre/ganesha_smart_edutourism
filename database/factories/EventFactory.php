<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $nameEn = $this->faker->words(3, true);
        $start = $this->faker->dateTimeBetween('now', '+2 months');

        return [
            'registration_url' => $this->faker->url(),
            'current_participants' => 0,
            'max_participants' => $this->faker->numberBetween(20, 200),
            'price' => $this->faker->randomFloat(2, 0, 150000),
            'is_free' => $this->faker->boolean(30),
            'location_name' => ['en' => $this->faker->words(2, true), 'id' => $this->faker->words(2, true)],
            'start_datetime' => $start,
            'end_datetime' => (clone $start)->modify('+3 hours'),
            'category' => $this->faker->randomElement(['cultural', 'culinary', 'workshop', 'ceremony']),
            'description' => ['en' => $this->faker->paragraph(), 'id' => $this->faker->paragraph()],
            'slug' => $this->faker->unique()->slug(),
            'name' => ['en' => ucfirst($nameEn), 'id' => 'Acara '.ucfirst($nameEn)],
        ];
    }
}
