<?php

namespace App\Http\Controllers;

use App\Events\CrowdAlertSent;
use App\Events\VillageDensityUpdated;
use App\Events\VisitorLocationRemoved;
use App\Events\VisitorLocationUpdated;
use App\Events\ZoneOccupancyUpdated;
use App\Models\CapacityZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TrackingController extends Controller
{
    /**
     * Receive GPS ping from clients and broadcast to heatmap.
     */
    public function ping(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'session_id' => 'required|string',
            'user_name' => 'nullable|string|max:255',
        ]);

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;
        $userName = $request->input('user_name');

        // Approximate bounding box for Desa Penglipuran
        // Center: -8.4223, 115.3594
        $inBounds = true; // ($lat < -8.410 && $lat > -8.435) && ($lng > 115.350 && $lng < 115.370);

        if ($inBounds) {
            $shouldRunFullCheck = false;
            $activeVisitors = [];

            $updateActiveVisitors = function () use ($request, $lat, $lng, $userName, &$shouldRunFullCheck, &$activeVisitors) {
                // Store active visitor in Cache
                $activeVisitors = Cache::get('active_visitors', []) ?? [];

                $activeVisitors[$request->session_id] = [
                    'lat' => $lat,
                    'lng' => $lng,
                    'last_seen' => now()->timestamp,
                    'user_name' => $userName,
                ];

                // Under high concurrency (>25 active visitors), throttle O(Z*N) polygon math & stale sweep to once per 3s
                $shouldRunFullCheck = count($activeVisitors) <= 25
                    || (bool) Cache::add('tracking_crowd_check_lock', 1, 3);

                if ($shouldRunFullCheck) {
                    // Clean up stale visitors (no ping for 30s — covers force-close browser)
                    $beforeIds = array_keys($activeVisitors);
                    $nowTs = now()->timestamp;
                    $activeVisitors = array_filter($activeVisitors, function ($visitor) use ($nowTs) {
                        return ($nowTs - $visitor['last_seen']) < 30;
                    });

                    // Broadcast removal for stale sessions that just got cleaned up
                    foreach (array_diff($beforeIds, array_keys($activeVisitors)) as $removedId) {
                        broadcast(new VisitorLocationRemoved($removedId));
                    }
                }

                Cache::put('active_visitors', $activeVisitors, now()->addMinutes(1));
            };

            $lock = rescue(fn () => Cache::lock('active_visitors_lock', 2), rescue: null, report: false);
            if ($lock) {
                rescue(fn () => $lock->block(1, $updateActiveVisitors), $updateActiveVisitors, false);
            } else {
                $updateActiveVisitors();
            }

            broadcast(new VisitorLocationUpdated($lat, $lng, $request->session_id, $userName));

            if ($shouldRunFullCheck) {
                // Check capacity zones for crowd alerts
                $this->checkCrowdAlerts($activeVisitors);
            }

            return response()->json(['status' => 'tracked']);
        }

        return response()->json(['status' => 'ignored_outside_polygon']);
    }

    /**
     * Remove a visitor from the active cache and notify others.
     */
    public function leave(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $removeVisitor = function () use ($request) {
            $activeVisitors = Cache::get('active_visitors', []);

            if (isset($activeVisitors[$request->session_id])) {
                unset($activeVisitors[$request->session_id]);
                Cache::put('active_visitors', $activeVisitors, now()->addMinutes(5));

                broadcast(new VisitorLocationRemoved($request->session_id));
                $this->checkCrowdAlerts($activeVisitors);
            }
        };

        $lock = rescue(fn () => Cache::lock('active_visitors_lock', 2), rescue: null, report: false);
        if ($lock) {
            rescue(fn () => $lock->block(1, $removeVisitor), $removeVisitor, false);
        } else {
            $removeVisitor();
        }

        return response()->json(['status' => 'removed']);
    }

    /**
     * Check all active capacity zones, broadcast deduplicated occupancy updates,
     * and trigger crowd alerts when thresholds are exceeded.
     *
     * @param  array<string, array{lat: float, lng: float, last_seen: int}>  $activeVisitors
     */
    private function checkCrowdAlerts(array $activeVisitors): void
    {
        $taggedCache = Cache::tags(['capacity']);
        $zonesData = $taggedCache
            ? $taggedCache->flexible('capacity_zones_active_array', [60, 300], fn (): array => CapacityZone::where('is_active', true)
                ->get()
                ->append('occupancy_percentage')
                ->toArray())
            : CapacityZone::where('is_active', true)
                ->get()
                ->append('occupancy_percentage')
                ->toArray();

        if ($zonesData === []) {
            return;
        }

        $previousZoneState = Cache::get('capacity_zone_last_broadcast_state_array', []) ?? [];
        $nextZoneState = $previousZoneState;
        $zoneStateChanged = false;
        $countsByZoneId = [];

        $totalCurrent = 0;
        $totalMax = 0;
        $masterZone = null;

        foreach ($zonesData as $zoneData) {
            if (($zoneData['zone_identifier'] ?? null) === 'desa_penglipuran') {
                $masterZone = $zoneData;
            }

            $zone = new CapacityZone($zoneData);
            $zone->id = $zoneData['id'];
            $countInZone = 0;

            if (! empty($zone->polygon_coordinates)) {
                foreach ($activeVisitors as $visitor) {
                    if ($zone->containsPoint((float) $visitor['lat'], (float) $visitor['lng'])) {
                        $countInZone++;
                    }
                }
            }

            $countsByZoneId[$zone->id] = $countInZone;
            $totalCurrent += $countInZone;
            $totalMax += (int) $zone->max_capacity;

            if (empty($zone->polygon_coordinates)) {
                continue;
            }

            $occupancy = ($zone->max_capacity > 0)
                ? (int) round(($countInZone / $zone->max_capacity) * 100)
                : 0;

            $zoneSignature = "{$countInZone}:{$zone->max_capacity}";
            if (($previousZoneState[$zone->id] ?? null) !== $zoneSignature) {
                $nextZoneState[$zone->id] = $zoneSignature;
                $zoneStateChanged = true;

                broadcast(new ZoneOccupancyUpdated(
                    $zone->id,
                    $zone->zone_identifier,
                    $countInZone,
                    $zone->max_capacity,
                    $occupancy,
                    CapacityZone::statusFor($occupancy, $zone->warning_threshold, $zone->critical_threshold),
                ));
            }

            $cacheKey = "crowd_alert_sent:{$zone->id}";

            $centerLat = 0;
            $centerLng = 0;
            if (\is_array($zone->polygon_coordinates) && count($zone->polygon_coordinates) > 0) {
                $centerLat = collect($zone->polygon_coordinates)->avg('lat');
                $centerLng = collect($zone->polygon_coordinates)->avg('lng');
            }

            if ($occupancy >= $zone->critical_threshold && ! Cache::has($cacheKey)) {
                broadcast(new CrowdAlertSent(
                    $zone->name,
                    'critical',
                    $occupancy,
                    (float) $centerLat,
                    (float) $centerLng,
                ));
                Cache::put($cacheKey, true, now()->addMinutes(5));
            } elseif ($occupancy >= $zone->warning_threshold && ! Cache::has($cacheKey)) {
                broadcast(new CrowdAlertSent(
                    $zone->name,
                    'warning',
                    $occupancy,
                    (float) $centerLat,
                    (float) $centerLng,
                ));
                Cache::put($cacheKey, true, now()->addMinutes(5));
            }
        }

        if ($taggedCache && count($activeVisitors) > 25) {
            $taggedCache->put('capacity_zone_live_counts_array', $countsByZoneId, 3);
        }

        if ($zoneStateChanged) {
            Cache::put('capacity_zone_last_broadcast_state_array', $nextZoneState, now()->addMinutes(10));
        }

        $densityPercent = $totalMax > 0 ? (int) round(($totalCurrent / $totalMax) * 100) : 0;
        $warningThreshold = (int) ($masterZone['warning_threshold'] ?? 60);
        $criticalThreshold = (int) ($masterZone['critical_threshold'] ?? 80);
        $densityStatus = CapacityZone::statusFor($densityPercent, $warningThreshold, $criticalThreshold);

        $villageSignature = "{$totalCurrent}:{$totalMax}:{$densityStatus['key']}";
        if (Cache::get('village_density_last_broadcast_signature') !== $villageSignature) {
            Cache::put('village_density_last_broadcast_signature', $villageSignature, now()->addMinutes(10));

            broadcast(new VillageDensityUpdated(
                $totalCurrent,
                $totalMax,
                $densityPercent,
                $densityStatus,
            ));
        }
    }
}
