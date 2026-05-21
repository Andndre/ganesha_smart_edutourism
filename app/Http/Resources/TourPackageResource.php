<?php

namespace App\Http\Resources;

use App\Models\TourPackage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TourPackage
 */
class TourPackageResource extends JsonResource
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
            'price' => (float) $this->price,
            'duration_hours' => (float) $this->duration_hours,
            'capacity' => [
                'min' => $this->min_capacity,
                'max' => $this->max_capacity,
            ],
            'inclusions' => $this->when((bool) $this->inclusions, fn () => $this->inclusions),
            'exclusions' => $this->when((bool) $this->exclusions, fn () => $this->exclusions),
            'images' => $this->when((bool) $this->images, fn () => $this->images),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
