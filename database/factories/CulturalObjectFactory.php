<?php

namespace Database\Factories;

use App\Models\CulturalObject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CulturalObject>
 */
class CulturalObjectFactory extends Factory
{
    protected $model = CulturalObject::class;

    public function definition(): array
    {
        return [
            'name' => ['en' => fake()->word(), 'id' => 'Nama '.fake()->word()],
            'slug' => fake()->unique()->slug(),
            'short_description' => ['en' => fake()->sentence(), 'id' => 'Deskripsi '.fake()->sentence()],
            'description' => ['en' => fake()->paragraph(), 'id' => fake()->paragraph()],
            'category' => fake()->randomElement(['parahyangan', 'pawongan', 'palemahan']),
        ];
    }
}
