<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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
     * Classify an occupancy percentage against a zone's warning/critical thresholds.
     *
     * @return array{key: string, label: string, color: string, barColor: string}
     */
    public static function statusFor(float $percent, int $warningThreshold, int $criticalThreshold): array
    {
        if ($percent >= $criticalThreshold) {
            return ['key' => 'full', 'label' => 'Penuh', 'color' => 'text-warning', 'barColor' => 'bg-warning'];
        }

        if ($percent >= $warningThreshold) {
            return ['key' => 'medium', 'label' => 'Sedang', 'color' => 'text-secondary', 'barColor' => 'bg-secondary'];
        }

        return ['key' => 'safe', 'label' => 'Aman', 'color' => 'text-primary', 'barColor' => 'bg-primary'];
    }

    /**
     * Recompute `current_count` for each zone (array form, e.g. from a cached
     * ->toArray() list) from currently active visitor GPS pings, instead of
     * trusting whatever stale/seeded value is stored in the DB column.
     *
     * @param  array<int, array<string, mixed>>  $zones
     * @return array<int, array<string, mixed>>
     */
    public static function withLiveCounts(array $zones): array
    {
        $now = now()->timestamp;
        $locations = [];

        foreach (Cache::get('active_visitors', []) as $visitor) {
            if (($now - $visitor['last_seen']) < 300) {
                $locations[] = ['lat' => (float) $visitor['lat'], 'lng' => (float) $visitor['lng']];
            }
        }

        foreach ($zones as &$zone) {
            $zone['current_count'] = 0;

            if (empty($zone['polygon_coordinates'])) {
                continue;
            }

            $tempZone = new self;
            $tempZone->polygon_coordinates = $zone['polygon_coordinates'];

            foreach ($locations as $location) {
                if ($tempZone->containsPoint($location['lat'], $location['lng'])) {
                    $zone['current_count']++;
                }
            }
        }
        unset($zone);

        return $zones;
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
