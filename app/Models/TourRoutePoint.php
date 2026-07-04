<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableArrayOutput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['tour_route_id', 'locationable_type', 'locationable_id', 'order', 'estimated_visit_minutes', 'storytelling_content', 'qr_code_token'])]
class TourRoutePoint extends Model
{
    use HasFactory;
    use HasTranslatableArrayOutput;
    use HasTranslations;

    public array $translatable = ['storytelling_content'];

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
     * Gamified missions attached to this point, in play order.
     *
     * @return HasMany<RouteMission, TourRoutePoint>
     */
    public function missions(): HasMany
    {
        return $this->hasMany(RouteMission::class)->orderBy('order');
    }
}
