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
        $zones = Cache::tags(['capacity'])->flexible('capacity_zones_active_array', [60, 300], function () {
            return CapacityZone::where('is_active', true)->get()->append('occupancy_percentage')->toArray();
        });
        $zones = CapacityZone::withLiveCounts($zones);
        $masterZone = collect($zones)->firstWhere('zone_identifier', 'desa_penglipuran');

        $totalCurrent = collect($zones)->sum('current_count');
        $totalMax = collect($zones)->sum('max_capacity');
        $densityPercent = $totalMax > 0 ? ($totalCurrent / $totalMax) * 100 : 0;

        $warningThreshold = $masterZone['warning_threshold'] ?? 60;
        $criticalThreshold = $masterZone['critical_threshold'] ?? 80;
        $densityStatus = CapacityZone::statusFor($densityPercent, $warningThreshold, $criticalThreshold);

        $densityMap = [
            'safe' => ['class' => 'text-green-500', 'bg' => 'bg-green-50'],
            'medium' => ['class' => 'text-warning', 'bg' => 'bg-orange-50'],
            'full' => ['class' => 'text-red-500', 'bg' => 'bg-red-50'],
        ][$densityStatus['key']];

        $densityText = $densityStatus['label'].' ('.round($densityPercent).'%)';
        $densityClass = $densityMap['class'];
        $densityBg = $densityMap['bg'];

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

        return view('home', \compact(
            'densityText', 'densityClass', 'densityBg', 'weather',
            'totalCurrent', 'totalMax', 'densityStatus', 'warningThreshold', 'criticalThreshold'
        ));
    }
}
