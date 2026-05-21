<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * CulturalObject model representing cultural heritage sites and objects.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $category
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $ar_marker_id
 * @property string|null $model_3d_path
 * @property array|null $historical_images
 * @property string|null $audio_narration_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug', 'description', 'category', 'latitude', 'longitude', 'ar_marker_id', 'model_3d_path', 'historical_images', 'audio_narration_path'])]
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
     * Get the stories associated with this cultural object.
     *
     * @return HasMany<CulturalStory>
     */
    public function stories(): HasMany
    {
        return $this->hasMany(CulturalStory::class)->orderBy('order');
    }

    /**
     * Get the time travel reconstructions for this cultural object.
     *
     * @return HasMany<TimeTravelReconstruction>
     */
    public function timeTravels(): HasMany
    {
        return $this->hasMany(TimeTravelReconstruction::class);
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
     * Get learning contents associated with this cultural object.
     *
     * @return MorphMany<LearningContent>
     */
    public function learningContents(): MorphMany
    {
        return $this->morphMany(LearningContent::class, 'locationable');
    }

    /**
     * Scope a query to only include objects with coordinates.
     *
     * @param  Builder<CulturalObject>  $query
     * @return Builder<CulturalObject>
     */
    public function scopeWithCoordinates(Builder $query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
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
        return $query->whereNotNull('ar_marker_id')->orWhereNotNull('model_3d_path');
    }
}
