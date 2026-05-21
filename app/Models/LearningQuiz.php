<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * LearningQuiz model for quizzes within learning content.
 *
 * @property int $id
 * @property int $learning_content_id
 * @property string $question
 * @property array|null $options
 * @property string|null $explanation
 * @property int|null $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['learning_content_id', 'question', 'options', 'explanation', 'order'])]
class LearningQuiz extends Model
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
            'options' => 'array',
            'order' => 'integer',
        ];
    }

    /**
     * Get the content that owns this quiz.
     *
     * @return BelongsTo<LearningContent, LearningQuiz>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(LearningContent::class);
    }

    /**
     * Scope a query to order by quiz sequence.
     *
     * @param  Builder<LearningQuiz>  $query
     * @return Builder<LearningQuiz>
     */
    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope a query to filter by content.
     *
     * @param  Builder<LearningQuiz>  $query
     * @return Builder<LearningQuiz>
     */
    public function scopeInContent(Builder $query, int $contentId)
    {
        return $query->where('learning_content_id', $contentId);
    }
}
