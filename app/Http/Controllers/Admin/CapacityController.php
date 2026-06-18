<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CapacityZone;
use App\Models\VisitorLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CapacityController extends Controller
{
    /**
     * Display the capacity monitoring dashboard.
     */
    public function index(): View
    {
        $zones = CapacityZone::where('is_active', true)->get();
        if ($zones->isEmpty()) {
            $zones = collect([
                new CapacityZone(['name' => 'Zona Utama (Jalan Utama)', 'current_count' => 0, 'max_capacity' => 400, 'warning_threshold' => 70, 'critical_threshold' => 90, 'zone_identifier' => 'main_street', 'latitude' => -8.422303, 'longitude' => 115.359488, 'radius_meters' => 200]),
                new CapacityZone(['name' => 'Area UMKM & Pasar', 'current_count' => 0, 'max_capacity' => 300, 'warning_threshold' => 70, 'critical_threshold' => 90, 'zone_identifier' => 'umkm_market', 'latitude' => -8.424103, 'longitude' => 115.359488, 'radius_meters' => 80]),
                new CapacityZone(['name' => 'Pura Penataran Agung', 'current_count' => 0, 'max_capacity' => 150, 'warning_threshold' => 70, 'critical_threshold' => 90, 'zone_identifier' => 'pura_penataran', 'latitude' => -8.420100, 'longitude' => 115.359500, 'radius_meters' => 60]),
                new CapacityZone(['name' => 'Kebun Bambu & Jalur Trekking', 'current_count' => 0, 'max_capacity' => 200, 'warning_threshold' => 70, 'critical_threshold' => 90, 'zone_identifier' => 'bamboo_forest', 'latitude' => -8.420500, 'longitude' => 115.361000, 'radius_meters' => 150]),
            ]);
        }

        // Reset current counts
        foreach ($zones as $zone) {
            $zone->current_count = 0;
        }

        // Yesterday's visitor hourly data for 24h chart
        $hourlyData = [];
        for ($h = 0; $h < 24; $h++) {
            $startTime = \sprintf('%02d:00:00', $h);
            $endTime = \sprintf('%02d:59:59', $h);

            $count = VisitorLog::whereDate('logged_at', Carbon::today())
                ->whereTime('logged_at', '>=', $startTime)
                ->whereTime('logged_at', '<=', $endTime)
                ->where('event_type', 'location_visit')
                ->count();
            if ($count === 0) {
                // mock data if no logs
                $mockData = [12, 8, 5, 3, 2, 8, 45, 120, 280, 390, 480, 530, 580, 617, 590, 540, 480, 410, 330, 260, 180, 120, 80, 40];
                $hourlyData[] = $mockData[$h];
            } else {
                $hourlyData[] = $count;
            }
        }

        // Add live tracked visitors from Cache for heatmap and calculate counts
        $heatmapData = [];
        $activeVisitors = Cache::get('active_visitors', []);
        foreach ($activeVisitors as $sessionId => $visitor) {
            if ((now()->timestamp - $visitor['last_seen']) < 300) {
                $lat = (float) $visitor['lat'];
                $lng = (float) $visitor['lng'];

                $heatmapData[] = [
                    'lat' => $lat,
                    'lng' => $lng,
                    'intensity' => 0.9,
                    'category' => 'cultural',
                    'name' => 'Pengunjung Aktif',
                    'is_live_user' => true,
                    'session_id' => $sessionId,
                ];

                // Dynamically increment zone counts
                foreach ($zones as $zone) {
                    if ($zone->latitude && $zone->longitude && $zone->radius_meters) {
                        $distance = $this->calculateHaversineDistance($lat, $lng, $zone->latitude, $zone->longitude);
                        if ($distance <= $zone->radius_meters) {
                            $zone->current_count++;
                        }
                    }
                }
            }
        }

        $totalCurrentCount = $zones->sum('current_count');
        $totalMaxCapacity = $zones->sum('max_capacity');

        $defaultLat = (float) env('PENGLIPURAN_LAT', -8.422303596762355);
        $defaultLon = (float) env('PENGLIPURAN_LON', 115.35948833933173);

        return view('admin.capacity.index', compact('zones', 'totalCurrentCount', 'totalMaxCapacity', 'hourlyData', 'heatmapData', 'defaultLat', 'defaultLon'));
    }

    /**
     * Update dynamic capacity warning thresholds.
     */
    public function updateThresholds(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'warning_threshold' => ['required', 'integer', 'min:1', 'max:100'],
            'critical_threshold' => ['required', 'integer', 'min:1', 'max:100', 'gt:warning_threshold'],
            'max_capacity' => ['required', 'integer', 'min:1'],
            'radius_meters' => ['nullable', 'integer', 'min:1'],
        ]);

        $zone = CapacityZone::findOrFail($id);
        $zone->update($validated);

        return redirect()->back()->with('success', 'Ambang batas kapasitas dan radius zona berhasil diperbarui.');
    }

    /**
     * Calculate Haversine distance in meters.
     */
    private function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
