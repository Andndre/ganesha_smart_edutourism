<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * TourRoute model for tourist routes.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $difficulty
 * @property int|null $estimated_duration_minutes
 * @property int|null $distance_meters
 * @property bool $is_smart_route
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'description', 'difficulty', 'estimated_duration_minutes', 'distance_meters', 'is_smart_route', 'is_active'])]
class TourRoute extends Model
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
            'is_smart_route' => 'boolean',
            'is_active' => 'boolean',
            'estimated_duration_minutes' => 'integer',
            'distance_meters' => 'integer',
        ];
    }

    /**
     * Get the route points for this tour route.
     *
     * @return HasMany<TourRoutePoint>
     */
    public function routePoints(): HasMany
    {
        return $this->hasMany(TourRoutePoint::class)->orderBy('order');
    }

    /**
     * Scope a query to only include active routes.
     *
     * @param  Builder<TourRoute>  $query
     * @return Builder<TourRoute>
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include smart routes.
     *
     * @param  Builder<TourRoute>  $query
     * @return Builder<TourRoute>
     */
    public function scopeSmartRoutes(Builder $query)
    {
        return $query->where('is_smart_route', true);
    }

    /**
     * Scope a query to filter by difficulty.
     *
     * @param  Builder<TourRoute>  $query
     * @return Builder<TourRoute>
     */
    public function scopeDifficulty(Builder $query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }
}
