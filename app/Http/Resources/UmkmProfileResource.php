<?php

namespace App\Http\Resources;

use App\Models\UmkmProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UmkmProfile
 */
class UmkmProfileResource extends JsonResource
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
            'business_name' => $this->business_name,
            'slug' => $this->slug,
            'owner_name' => $this->owner_name,
            'description' => $this->description,
            'category' => $this->category,
            'rating' => (float) $this->rating,
            'location' => $this->whenLoaded('mapLocation', fn () => [
                'latitude' => (float) $this->mapLocation->latitude,
                'longitude' => (float) $this->mapLocation->longitude,
            ], null),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
