<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'category', 'locationable_type', 'locationable_id', 'latitude', 'longitude', 'is_accessible', 'accessibility_notes'])]
class MapLocation extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['accessibility_notes'];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_accessible' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Get the owning locationable model.
     */
    public function locationable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the AR model associated with this location.
     *
     * @return HasOne<ArModel>
     */
    public function arModel(): HasOne
    {
        return $this->hasOne(ArModel::class, 'map_location_id');
    }

    /**
     * Scope a query to only include accessible locations.
     *
     * @param  Builder<MapLocation>  $query
     * @return Builder<MapLocation>
     */
    public function scopeAccessible(Builder $query)
    {
        return $query->where('is_accessible', true);
    }

    /**
     * Scope a query to filter by category.
     *
     * @param  Builder<MapLocation>  $query
     * @return Builder<MapLocation>
     */
    public function scopeCategory(Builder $query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by locationable type.
     *
     * @param  Builder<MapLocation>  $query
     * @return Builder<MapLocation>
     */
    public function scopeOfType(Builder $query, string $type)
    {
        return $query->where('locationable_type', $type);
    }
}
