<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CapacityZone;
use App\Models\VisitorLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CapacityController extends Controller
{
    /**
     * Display the capacity monitoring dashboard.
     */
    public function index(): View
    {
        $zones = Cache::tags(['capacity'])->flexible('capacity_zones_active_array', [60, 300], function () {
            return CapacityZone::where('is_active', true)->get()->append('occupancy_percentage')->toArray();
        });
        $zones = CapacityZone::withLiveCounts($zones);

        // Calculate real 24h visitor trend and dynamic hourly labels
        $hourlyData = [];
        $hourlyLabels = [];
        $now = now();
        for ($i = 23; $i >= 0; $i--) {
            $targetTime = $now->copy()->subHours($i);
            $start = $targetTime->copy()->startOfHour();
            $end = $targetTime->copy()->endOfHour();

            $count = VisitorLog::whereBetween('logged_at', [$start, $end])
                ->where('event_type', 'page_view')
                ->count();

            $hourlyData[] = $count;
            $hourlyLabels[] = $targetTime->format('H:00');
        }

        // Add live tracked visitors from Cache for heatmap and calculate counts
        $heatmapData = [];
        $visitorLocations = [];
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

                $visitorLocations[$sessionId] = ['lat' => $lat, 'lng' => $lng];
            }
        }

        $totalCurrentCount = collect($zones)->sum('current_count');
        $totalMaxCapacity = collect($zones)->sum('max_capacity');

        $defaultLat = (float) config('services.penglipuran.latitude');
        $defaultLon = (float) config('services.penglipuran.longitude');

        return view('admin.capacity.index', compact('zones', 'totalCurrentCount', 'totalMaxCapacity', 'hourlyData', 'hourlyLabels', 'heatmapData', 'defaultLat', 'defaultLon'));
    }

    /**
     * Store a new capacity zone.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('polygon_coordinates') && \is_string($request->polygon_coordinates)) {
            $request->merge([
                'polygon_coordinates' => json_decode($request->polygon_coordinates, true),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'zone_identifier' => ['nullable', 'string', 'max:255', 'unique:capacity_zones,zone_identifier'],
            'max_capacity' => ['required', 'integer', 'min:1'],
            'warning_threshold' => ['required', 'integer', 'min:1', 'max:100'],
            'critical_threshold' => ['required', 'integer', 'min:1', 'max:100', 'gt:warning_threshold'],
            'polygon_coordinates' => ['nullable', 'array'],
        ]);

        if (empty($validated['zone_identifier'])) {
            $base = (string) str($validated['name'])->slug('_');
            $identifier = $base;
            $suffix = 1;
            while (CapacityZone::where('zone_identifier', $identifier)->exists()) {
                $identifier = $base.'_'.$suffix++;
            }
            $validated['zone_identifier'] = $identifier;
        }

        CapacityZone::create($validated);

        return redirect()->back()->with('success', __('Zona baru berhasil ditambahkan.'));
    }

    /**
     * Update dynamic capacity warning thresholds and polygons.
     */
    public function updateThresholds(Request $request, int $id): RedirectResponse
    {
        if ($request->filled('polygon_coordinates') && \is_string($request->polygon_coordinates)) {
            $request->merge([
                'polygon_coordinates' => json_decode($request->polygon_coordinates, true),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'warning_threshold' => ['required', 'integer', 'min:1', 'max:100'],
            'critical_threshold' => ['required', 'integer', 'min:1', 'max:100', 'gt:warning_threshold'],
            'max_capacity' => ['required', 'integer', 'min:1'],
            'polygon_coordinates' => ['nullable', 'array'],
        ]);

        $zone = CapacityZone::findOrFail($id);
        $zone->update($validated);

        return redirect()->back()->with('success', __('Detail dan area zona berhasil diperbarui.'));
    }

    /**
     * Delete a capacity zone.
     */
    public function destroy(int $id): RedirectResponse
    {
        $zone = CapacityZone::findOrFail($id);
        $zone->delete();

        return redirect()->back()->with('success', __('Zona berhasil dihapus.'));
    }
}
