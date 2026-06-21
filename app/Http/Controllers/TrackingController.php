<?php

namespace App\Http\Controllers;

use App\Events\CrowdAlertSent;
use App\Events\VisitorLocationRemoved;
use App\Events\VisitorLocationUpdated;
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
            // Store active visitor in Cache
            $activeVisitors = Cache::get('active_visitors', []);

            $activeVisitors[$request->session_id] = [
                'lat' => $lat,
                'lng' => $lng,
                'last_seen' => now()->timestamp,
                'user_name' => $userName,
            ];

            // Clean up stale visitors (no ping for 30s — covers force-close browser)
            // Ping interval is 10s, so active users always stay; force-close = gone in ~30s
            $beforeIds = array_keys($activeVisitors);
            $activeVisitors = array_filter($activeVisitors, function ($visitor) {
                return (now()->timestamp - $visitor['last_seen']) < 30;
            });

            // Broadcast removal for stale sessions that just got cleaned up
            foreach (array_diff($beforeIds, array_keys($activeVisitors)) as $removedId) {
                broadcast(new VisitorLocationRemoved($removedId));
            }

            Cache::put('active_visitors', $activeVisitors, now()->addMinutes(1));

            broadcast(new VisitorLocationUpdated($lat, $lng, $request->session_id, $userName));

            // Check capacity zones for crowd alerts
            $this->checkCrowdAlerts($activeVisitors);

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

        $activeVisitors = Cache::get('active_visitors', []);

        if (isset($activeVisitors[$request->session_id])) {
            unset($activeVisitors[$request->session_id]);
            Cache::put('active_visitors', $activeVisitors, now()->addMinutes(5));

            broadcast(new VisitorLocationRemoved($request->session_id));
        }

        return response()->json(['status' => 'removed']);
    }

    /**
     * Check all active capacity zones and broadcast crowd alerts when thresholds are exceeded.
     *
     * @param  array<string, array{lat: float, lng: float, last_seen: int}>  $activeVisitors
     */
    private function checkCrowdAlerts(array $activeVisitors): void
    {
        $zones = CapacityZone::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        foreach ($zones as $zone) {
            $countInZone = 0;

            foreach ($activeVisitors as $visitor) {
                $distance = $this->calculateHaversineDistance(
                    $visitor['lat'],
                    $visitor['lng'],
                    (float) $zone->latitude,
                    (float) $zone->longitude
                );

                if ($distance <= $zone->radius_meters) {
                    $countInZone++;
                }
            }

            $occupancy = ($zone->max_capacity > 0)
                ? (int) round(($countInZone / $zone->max_capacity) * 100)
                : 0;

            $cacheKey = "crowd_alert_sent:{$zone->id}";

            if ($occupancy >= $zone->critical_threshold && ! Cache::has($cacheKey)) {
                broadcast(new CrowdAlertSent(
                    $zone->name,
                    'critical',
                    $occupancy,
                    (float) $zone->latitude,
                    (float) $zone->longitude,
                ));
                Cache::put($cacheKey, true, now()->addMinutes(5));
            } elseif ($occupancy >= $zone->warning_threshold && ! Cache::has($cacheKey)) {
                broadcast(new CrowdAlertSent(
                    $zone->name,
                    'warning',
                    $occupancy,
                    (float) $zone->latitude,
                    (float) $zone->longitude,
                ));
                Cache::put($cacheKey, true, now()->addMinutes(5));
            }
        }
    }

    /**
     * Calculate Haversine distance in meters between two coordinates.
     */
    private function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
