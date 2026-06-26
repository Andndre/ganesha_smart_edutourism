<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'zone_identifier', 'polygon_coordinates', 'max_capacity', 'warning_threshold', 'critical_threshold', 'current_count', 'is_active'])]
class CapacityZone extends Model
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
            'polygon_coordinates' => 'array',
        ];
    }

    /**
     * Get the visitor logs for this zone.
     *
     * @return HasMany<VisitorLog>
     */
    public function visitorLogs(): HasMany
    {
        return $this->hasMany(VisitorLog::class);
    }

    /**
     * Scope a query to only include active zones.
     *
     * @param  Builder<CapacityZone>  $query
     * @return Builder<CapacityZone>
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to include zones at warning threshold.
     *
     * @param  Builder<CapacityZone>  $query
     * @return Builder<CapacityZone>
     */
    public function scopeAtWarning(Builder $query)
    {
        return $query->whereRaw('(current_count::float / max_capacity) * 100 >= warning_threshold');
    }

    /**
     * Scope a query to include zones at critical threshold.
     *
     * @param  Builder<CapacityZone>  $query
     * @return Builder<CapacityZone>
     */
    public function scopeAtCritical(Builder $query)
    {
        return $query->whereRaw('(current_count::float / max_capacity) * 100 >= critical_threshold');
    }

    /**
     * Get the occupancy percentage.
     */
    public function getOccupancyPercentageAttribute(): int
    {
        if ($this->max_capacity === 0) {
            return 0;
        }

        return (int) round(($this->current_count / $this->max_capacity) * 100);
    }

    /**
     * Check if the zone is at warning threshold.
     */
    public function isAtWarning(): bool
    {
        return $this->occupancy_percentage >= $this->warning_threshold;
    }

    /**
     * Check if the zone is at critical threshold.
     */
    public function isAtCritical(): bool
    {
        return $this->occupancy_percentage >= $this->critical_threshold;
    }

    /**
     * Check if a given latitude and longitude is inside the zone's polygon.
     * Uses the Ray-Casting algorithm.
     */
    public function containsPoint(float $lat, float $lng): bool
    {
        $polygon = $this->polygon_coordinates;

        if (empty($polygon) || ! \is_array($polygon) || count($polygon) < 3) {
            return false;
        }

        $inside = false;
        $j = count($polygon) - 1;

        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i]['lat'];
            $yi = $polygon[$i]['lng'];
            $xj = $polygon[$j]['lat'];
            $yj = $polygon[$j]['lng'];

            $intersect = (($yi > $lng) != ($yj > $lng))
                && ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = ! $inside;
            }

            $j = $i;
        }

        return $inside;
    }
}
