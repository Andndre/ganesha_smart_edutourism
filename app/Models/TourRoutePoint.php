<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * TourRoutePoint model for points within tour routes.
 *
 * @property int $id
 * @property int $tour_route_id
 * @property string|null $locationable_type
 * @property int|null $locationable_id
 * @property int|null $order
 * @property int|null $estimated_visit_minutes
 * @property string|null $storytelling_content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['tour_route_id', 'locationable_type', 'locationable_id', 'order', 'estimated_visit_minutes', 'storytelling_content'])]
class TourRoutePoint extends Model
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
            'order' => 'integer',
            'estimated_visit_minutes' => 'integer',
        ];
    }

    /**
     * Get the tour route that owns this point.
     *
     * @return BelongsTo<TourRoute, TourRoutePoint>
     */
    public function tourRoute(): BelongsTo
    {
        return $this->belongsTo(TourRoute::class);
    }

    /**
     * Get the owning locationable model.
     */
    public function locationable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to order by sequence.
     *
     * @param  Builder<TourRoutePoint>  $query
     * @return Builder<TourRoutePoint>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope a query to filter by route.
     *
     * @param  Builder<TourRoutePoint>  $query
     * @return Builder<TourRoutePoint>
     */
    public function scopeInRoute($query, int $routeId)
    {
        return $query->where('tour_route_id', $routeId)->ordered();
    }
}
