<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CulturalObjectQuiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'cultural_object_id',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
    ];

    /**
     * Get the cultural object that owns this quiz.
     */
    public function culturalObject(): BelongsTo
    {
        return $this->belongsTo(CulturalObject::class);
    }
}
