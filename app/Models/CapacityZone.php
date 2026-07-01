<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
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
