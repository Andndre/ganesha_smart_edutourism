<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_token',
        'tour_route_id',
        'current_point_id',
        'points_completed',
        'total_score',
        'missions_completed',
        'collectibles_earned',
        'badge_awarded',
        'selected_avatar',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'missions_completed' => 'array',
            'collectibles_earned' => 'array',
        ];
    }

    /**
     * Append a collectible slug once (e.g. digital_passport, heritage_key_1). Does not save.
     */
    public function awardCollectible(string $slug): void
    {
        $collectibles = $this->collectibles_earned ?? [];

        if (! \in_array($slug, $collectibles)) {
            $collectibles[] = $slug;
            $this->collectibles_earned = $collectibles;
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tourRoute(): BelongsTo
    {
        return $this->belongsTo(TourRoute::class);
    }

    public function currentPoint(): BelongsTo
    {
        return $this->belongsTo(TourRoutePoint::class, 'current_point_id');
    }
}
