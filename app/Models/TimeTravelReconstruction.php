<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * TimeTravelReconstruction model for 3D historical reconstructions.
 *
 * @property int $id
 * @property int $cultural_object_id
 * @property int|null $year_represented
 * @property string $title
 * @property string|null $description
 * @property string|null $model_3d_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['cultural_object_id', 'year_represented', 'title', 'description', 'model_3d_path'])]
class TimeTravelReconstruction extends Model
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
            'year_represented' => 'integer',
            'cultural_object_id' => 'integer',
        ];
    }

    /**
     * Get the cultural object that owns the reconstruction.
     *
     * @return BelongsTo<CulturalObject, TimeTravelReconstruction>
     */
    public function culturalObject(): BelongsTo
    {
        return $this->belongsTo(CulturalObject::class);
    }

    /**
     * Scope a query to order by year.
     *
     * @param  Builder<TimeTravelReconstruction>  $query
     * @return Builder<TimeTravelReconstruction>
     */
    public function scopeChronological($query)
    {
        return $query->orderBy('year_represented');
    }

    /**
     * Scope a query to filter by year range.
     *
     * @param  Builder<TimeTravelReconstruction>  $query
     * @return Builder<TimeTravelReconstruction>
     */
    public function scopeYearRange($query, int $startYear, int $endYear)
    {
        return $query->whereBetween('year_represented', [$startYear, $endYear]);
    }
}
