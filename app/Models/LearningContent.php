<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * LearningContent model for educational content within modules.
 *
 * @property int $id
 * @property int $learning_module_id
 * @property string $content_type
 * @property string $title
 * @property string|null $content
 * @property string|null $media_path
 * @property int|null $duration_seconds
 * @property int|null $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['learning_module_id', 'content_type', 'title', 'content', 'media_path', 'duration_seconds', 'order'])]
class LearningContent extends Model
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
            'duration_seconds' => 'integer',
            'order' => 'integer',
        ];
    }

    /**
     * Get the module that owns this content.
     *
     * @return BelongsTo<LearningModule, LearningContent>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class);
    }

    /**
     * Get the quizzes for this content.
     *
     * @return HasMany<LearningQuiz>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(LearningQuiz::class)->orderBy('order');
    }

    /**
     * Scope a query to order by content sequence.
     *
     * @param  Builder<LearningContent>  $query
     * @return Builder<LearningContent>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope a query to filter by content type.
     *
     * @param  Builder<LearningContent>  $query
     * @return Builder<LearningContent>
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('content_type', $type);
    }

    /**
     * Scope a query to filter by module.
     *
     * @param  Builder<LearningContent>  $query
     * @return Builder<LearningContent>
     */
    public function scopeInModule($query, int $moduleId)
    {
        return $query->where('learning_module_id', $moduleId);
    }
}
