<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * UserLearningProgress model tracking user progress in learning modules.
 *
 * @property int $id
 * @property int $user_id
 * @property int $learning_module_id
 * @property string|null $status
 * @property int|null $progress_percentage
 * @property int|null $last_content_id
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'learning_module_id', 'status', 'progress_percentage', 'last_content_id', 'completed_at'])]
class UserLearningProgress extends Model
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
            'completed_at' => 'datetime',
            'progress_percentage' => 'integer',
        ];
    }

    /**
     * Get the user that owns this progress.
     *
     * @return BelongsTo<User, UserLearningProgress>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the learning module for this progress.
     *
     * @return BelongsTo<LearningModule, UserLearningProgress>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class, 'learning_module_id');
    }

    /**
     * Get the last content viewed.
     *
     * @return BelongsTo<LearningContent, UserLearningProgress>
     */
    public function lastContent(): BelongsTo
    {
        return $this->belongsTo(LearningContent::class, 'last_content_id');
    }

    /**
     * Scope a query to filter by status.
     *
     * @param  Builder<UserLearningProgress>  $query
     * @return Builder<UserLearningProgress>
     */
    public function scopeStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to include in-progress items.
     *
     * @param  Builder<UserLearningProgress>  $query
     * @return Builder<UserLearningProgress>
     */
    public function scopeInProgress(Builder $query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to include completed items.
     *
     * @param  Builder<UserLearningProgress>  $query
     * @return Builder<UserLearningProgress>
     */
    public function scopeCompleted(Builder $query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if the module is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
