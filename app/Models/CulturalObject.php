<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable(['name', 'slug', 'short_description', 'description', 'category', 'historical_images'])]
class CulturalObject extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'historical_images' => 'array',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::deleted(function (CulturalObject $culturalObject) {
            $culturalObject->mapLocation()->delete();
        });
    }

    /**
     * Get the stories associated with this cultural object.
     *
     * @return HasMany<CulturalStory>
     */
    public function stories(): HasMany
    {
        return $this->hasMany(CulturalStory::class)->orderBy('order');
    }

    /**
     * Get the map location for this cultural object.
     *
     * @return MorphOne<MapLocation>
     */
    public function mapLocation(): MorphOne
    {
        return $this->morphOne(MapLocation::class, 'locationable');
    }

    /**
     * Get the quizzes associated with this cultural object.
     *
     * @return HasMany<CulturalObjectQuiz>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(CulturalObjectQuiz::class);
    }

    /**
     * Scope a query to only include objects with coordinates.
     *
     * @param  Builder<CulturalObject>  $query
     * @return Builder<CulturalObject>
     */
    public function scopeWithCoordinates(Builder $query)
    {
        return $query->whereHas('mapLocation');
    }

    /**
     * Scope a query to filter by category.
     *
     * @param  Builder<CulturalObject>  $query
     * @return Builder<CulturalObject>
     */
    public function scopeCategory(Builder $query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by AR availability.
     *
     * @param  Builder<CulturalObject>  $query
     * @return Builder<CulturalObject>
     */
    public function scopeWithAr(Builder $query)
    {
        return $query->whereHas('mapLocation.arModel');
    }

    public function getArMarkerIdAttribute(): ?string
    {
        return $this->mapLocation?->arModel?->ar_marker_id;
    }

    public function getArMarkerPattPathAttribute(): ?string
    {
        return $this->mapLocation?->arModel?->ar_marker_patt_path;
    }

    public function getModel3dPathAttribute(): ?string
    {
        return $this->mapLocation?->arModel?->model_3d_path;
    }

    public function getModel3dUsdzPathAttribute(): ?string
    {
        return $this->mapLocation?->arModel?->model_3d_usdz_path;
    }

    public function getAudioNarrationPathAttribute(): ?string
    {
        return $this->mapLocation?->arModel?->audio_narration_path;
    }
}
