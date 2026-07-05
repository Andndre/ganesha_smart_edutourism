<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableArrayOutput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'description', 'difficulty', 'gamification_key', 'estimated_duration_minutes', 'distance_meters', 'is_active'])]
class TourRoute extends Model
{
    use HasFactory;
    use HasTranslatableArrayOutput;
    use HasTranslations;

    /**
     * Gamification profiles a route can be assigned (badge/collectible/avatar logic).
     * Null = a plain waypoint route with no gamification.
     */
    public const GAMIFICATION_KEYS = ['heritage_quest', 'cultural_adventure', 'eco_quest'];

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
}
