<?php

namespace Database\Factories;

use App\Models\ArModel;
use App\Models\MapLocation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ArModelFactory extends Factory
{
    protected $model = ArModel::class;

    public function definition(): array
    {
        return [
            'ar_marker_id' => $this->faker->word(),
            'ar_marker_patt_path' => $this->faker->word(),
            'model_3d_path' => $this->faker->word(),
            'model_3d_usdz_path' => $this->faker->word(),
            'audio_narration_paths' => $this->faker->word(),
            'thumbnail_path' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'audio_narration_path' => $this->faker->word(),

            'map_location_id' => MapLocation::factory(),
        ];
    }
}
