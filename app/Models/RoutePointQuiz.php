<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutePointQuiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_route_point_id',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
    ];

    public function routePoint(): BelongsTo
    {
        return $this->belongsTo(TourRoutePoint::class, 'tour_route_point_id');
    }
}
