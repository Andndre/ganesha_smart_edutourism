<?php

namespace Database\Factories;

use App\Models\ArModel;
use App\Models\CulturalObject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArModelFactory extends Factory
{
    protected $model = ArModel::class;

    public function definition(): array
    {
        return [
            'cultural_object_id' => CulturalObject::factory(),
            'ar_marker_id' => $this->faker->unique()->uuid(),
            'ar_marker_patt_path' => 'ar_markers/'.$this->faker->uuid().'.patt',
            'model_3d_path' => 'ar_models/'.$this->faker->uuid().'.glb',
            'model_3d_usdz_path' => null,
            'thumbnail_path' => null,
        ];
    }
}
