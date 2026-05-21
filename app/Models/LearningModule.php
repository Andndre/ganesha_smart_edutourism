<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * LearningModule model for educational modules.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $category
 * @property string|null $description
 * @property string|null $thumbnail_path
 * @property string|null $difficulty
 * @property int|null $estimated_duration_minutes
 * @property bool $is_active
 * @property int|null $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug', 'category', 'description', 'thumbnail_path', 'difficulty', 'estimated_duration_minutes', 'is_active', 'order'])]
class LearningModule extends Model
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
            'is_active' => 'boolean',
            'estimated_duration_minutes' => 'integer',
            'order' => 'integer',
        ];
    }

    /**
     * Get the contents for this module.
     *
     * @return HasMany<LearningContent>
     */
    public function contents(): HasMany
    {
        return $this->hasMany(LearningContent::class)->orderBy('order');
    }

    /**
     * Get the user progress records for this module.
     *
     * @return HasMany<UserLearningProgress>
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserLearningProgress::class);
    }

    /**
     * Scope a query to only include active modules.
     *
     * @param  Builder<LearningModule>  $query
     * @return Builder<LearningModule>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }

    /**
     * Scope a query to filter by category.
     *
     * @param  Builder<LearningModule>  $query
     * @return Builder<LearningModule>
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by difficulty level.
     *
     * @param  Builder<LearningModule>  $query
     * @return Builder<LearningModule>
     */
    public function scopeDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }
}
