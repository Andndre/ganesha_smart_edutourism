<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'description', 'difficulty', 'estimated_duration_minutes', 'distance_meters', 'is_active'])]
class TourRoute extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
