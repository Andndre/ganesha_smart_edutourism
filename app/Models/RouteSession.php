<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'status',
    ];

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

    public function quizAnswers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
