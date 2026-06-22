<?php

namespace App\Http\Controllers;

use App\Models\CapacityZone;
use App\Models\WeatherReport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the tourist homepage dashboard.
     */
    public function index(): View
    {
        $locale = app()->getLocale();
        $zones = Cache::tags(['capacity'])->flexible("capacity_zones_active_array_{$locale}", [60, 300], function () use ($locale) {
            return CapacityZone::where('is_active', true)->get()->append('occupancy_percentage')->toArray();
        });
        $masterZone = collect($zones)->firstWhere('zone_identifier', 'desa_penglipuran');

        if ($masterZone) {
            $totalCurrent = $masterZone['current_count'];
            $maxCapacity = $masterZone['max_capacity'];
            $pct = $maxCapacity > 0 ? ($totalCurrent / $maxCapacity) * 100 : 0;

            if ($pct >= ($masterZone['critical_threshold'] ?? 80)) {
                $statusColor = 'red';
                $statusText = 'Penuh';
            } elseif ($pct >= ($masterZone['warning_threshold'] ?? 60)) {
                $statusColor = 'yellow';
                $statusText = 'Ramai';
            }
        }

        $totalCurrent = collect($zones)->sum('current_count');
        $totalMax = collect($zones)->sum('max_capacity');

        $densityPercent = $totalMax > 0 ? ($totalCurrent / $totalMax) * 100 : 0;

        if ($densityPercent >= 90) {
            $densityText = 'Padat (Kritis)';
            $densityClass = 'text-red-500';
            $densityBg = 'bg-red-50';
        } elseif ($densityPercent >= 70) {
            $densityText = 'Ramai (Waspada)';
            $densityClass = 'text-warning';
            $densityBg = 'bg-orange-50';
        } else {
            $densityText = 'Aman (Lancar)';
            $densityClass = 'text-green-500';
            $densityBg = 'bg-green-50';
        }

        // Fetch cached weather, fallback to first-time update if empty
        $weather = WeatherReport::first();
        if (! $weather) {
            try {
                Artisan::call('app:update-weather');
                $weather = WeatherReport::first();
            } catch (\Exception $e) {
                // Ignored fallback
            }
        }

        return view('home', \compact('densityText', 'densityClass', 'densityBg', 'weather'));
    }
}
