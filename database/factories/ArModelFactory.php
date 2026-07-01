<?php

namespace Database\Factories;

use App\Models\ArModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArModelFactory extends Factory
{
    protected $model = ArModel::class;

    public function definition(): array
    {
        return [
            'ar_marker_id' => $this->faker->unique()->uuid(),
            'ar_marker_patt_path' => 'ar_markers/'.$this->faker->uuid().'.patt',
            'model_3d_path' => 'ar_models/'.$this->faker->uuid().'.glb',
            'model_3d_usdz_path' => null,
            'audio_narration_paths' => ['en' => 'audio/'.$this->faker->uuid().'.mp3'],
            'thumbnail_path' => null,
            'name' => ['en' => $this->faker->words(2, true), 'id' => $this->faker->words(2, true)],
            'description' => ['en' => $this->faker->sentence(), 'id' => $this->faker->sentence()],
        ];
    }
}
