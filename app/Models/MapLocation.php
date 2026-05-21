<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * MapLocation model for geographical locations.
 *
 * @property int $id
 * @property string $name
 * @property string|null $category
 * @property string $locationable_type
 * @property int $locationable_id
 * @property float|null $latitude
 * @property float|null $longitude
 * @property bool $is_accessible
 * @property string|null $accessibility_notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'category', 'locationable_type', 'locationable_id', 'latitude', 'longitude', 'is_accessible', 'accessibility_notes'])]
class MapLocation extends Model
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
     * Scope a query to only include accessible locations.
     *
     * @param  Builder<MapLocation>  $query
     * @return Builder<MapLocation>
     */
    public function scopeAccessible($query)
    {
        return $query->where('is_accessible', true);
    }

    /**
     * Scope a query to filter by category.
     *
     * @param  Builder<MapLocation>  $query
     * @return Builder<MapLocation>
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by locationable type.
     *
     * @param  Builder<MapLocation>  $query
     * @return Builder<MapLocation>
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('locationable_type', $type);
    }
}
