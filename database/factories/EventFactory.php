<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'updated_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'registration_url' => $this->faker->url(),
            'current_participants' => $this->faker->randomNumber(),
            'max_participants' => $this->faker->randomNumber(),
            'price' => $this->faker->randomFloat(),
            'is_free' => $this->faker->boolean(),
            'location_name' => $this->faker->name(),
            'end_datetime' => Carbon::now(),
            'start_datetime' => Carbon::now(),
            'category' => $this->faker->word(),
            'description' => $this->faker->text(),
            'slug' => $this->faker->slug(),
            'name' => $this->faker->name(),
        ];
    }
}
