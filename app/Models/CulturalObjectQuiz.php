<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class CulturalObjectQuiz extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['question', 'option_a', 'option_b', 'option_c', 'option_d'];

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
