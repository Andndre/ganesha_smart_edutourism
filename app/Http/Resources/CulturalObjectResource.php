<?php

namespace App\Http\Resources;

use App\Models\CulturalObject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CulturalObject
 */
class CulturalObjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'location' => [
                'latitude' => (float) $this->latitude,
                'longitude' => (float) $this->longitude,
            ],
            'ar_marker_id' => $this->ar_marker_id,
            'has_3d_model' => ! empty($this->model_3d_path),
            'has_audio' => ! empty($this->audio_narration_path),
            'images' => $this->when((bool) $this->historical_images, fn () => $this->historical_images),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
