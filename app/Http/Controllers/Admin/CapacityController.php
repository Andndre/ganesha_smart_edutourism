<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CapacityZone;
use App\Models\VisitorLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
                new CapacityZone(['name' => 'Zona Utama (Jalan Utama)', 'current_count' => 312, 'max_capacity' => 400, 'warning_threshold' => 70, 'critical_threshold' => 90, 'zone_identifier' => 'main_street']),
                new CapacityZone(['name' => 'Area UMKM & Pasar', 'current_count' => 178, 'max_capacity' => 300, 'warning_threshold' => 70, 'critical_threshold' => 90, 'zone_identifier' => 'umkm_market']),
                new CapacityZone(['name' => 'Pura Penataran Agung', 'current_count' => 85, 'max_capacity' => 150, 'warning_threshold' => 70, 'critical_threshold' => 90, 'zone_identifier' => 'pura_penataran']),
                new CapacityZone(['name' => 'Kebun Bambu & Jalur Trekking', 'current_count' => 42, 'max_capacity' => 200, 'warning_threshold' => 70, 'critical_threshold' => 90, 'zone_identifier' => 'bamboo_forest']),
            ]);
        }

        $totalCurrentCount = $zones->sum('current_count');
        $totalMaxCapacity = $zones->sum('max_capacity');

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

        return view('admin.capacity.index', compact('zones', 'totalCurrentCount', 'totalMaxCapacity', 'hourlyData'));
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
        ]);

        $zone = CapacityZone::findOrFail($id);
        $zone->update($validated);

        return redirect()->back()->with('success', 'Ambang batas kapasitas zona berhasil diperbarui.');
    }
}
