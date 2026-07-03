<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_session_id',
        'cultural_object_quiz_id',
        'selected_option',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function routeSession(): BelongsTo
    {
        return $this->belongsTo(RouteSession::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(CulturalObjectQuiz::class, 'cultural_object_quiz_id');
    }
}
