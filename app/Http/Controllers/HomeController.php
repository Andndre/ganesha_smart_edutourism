<?php

namespace App\Http\Controllers;

use App\Models\CapacityZone;
use App\Models\TourRoute;
use App\Models\WeatherReport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the tourist homepage dashboard.
     */
    public function index(): View
    {
        $zones = CapacityZone::where('is_active', true)->get();
        $totalCurrent = $zones->sum('current_count');
        $totalMax = $zones->sum('max_capacity');
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

        $recommendedRoutes = TourRoute::where('is_active', true)
            ->withCount('routePoints')
            ->take(3)
            ->get();

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

        return view('home', \compact('densityText', 'densityClass', 'densityBg', 'recommendedRoutes', 'weather'));
    }
}
