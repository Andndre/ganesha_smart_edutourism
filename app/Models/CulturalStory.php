<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cultural_object_id', 'title', 'content', 'story_type', 'order'])]
class CulturalStory extends Model
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
            'cultural_object_id' => 'integer',
            'order' => 'integer',
        ];
    }

    /**
     * Get the cultural object that owns the story.
     *
     * @return BelongsTo<CulturalObject, CulturalStory>
     */
    public function culturalObject(): BelongsTo
    {
        return $this->belongsTo(CulturalObject::class);
    }

    /**
     * Scope a query to order by story sequence.
     *
     * @param  Builder<CulturalStory>  $query
     * @return Builder<CulturalStory>
     */
    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope a query to filter by story type.
     *
     * @param  Builder<CulturalStory>  $query
     * @return Builder<CulturalStory>
     */
    public function scopeOfType(Builder $query, string $type)
    {
        return $query->where('story_type', $type);
    }
}
