<?php

namespace App\Providers;

use App\Models\ArModel;
use App\Models\CapacityZone;
use App\Models\CulturalObject;
use App\Models\Event;
use App\Models\Facility;
use App\Models\MapLocation;
use App\Models\RouteSession;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\TraditionalHouse;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\WeatherReport;
use App\Observers\CacheInvalidationObserver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (
            config('octane.https')
            || (app()->environment('production') && str_starts_with(config('app.url'), 'https://'))
        ) {
            URL::forceScheme('https');
        }

        // Definikan gate resmi dari package Log Viewer
        Gate::define('viewLogViewer', function ($user) {
            return $user && $user->isAdmin();
        });

        View::composer(['layouts.app', 'user.umkm.index', 'components.session-state-sync'], function ($view) {
            $userId = auth()->id();
            $guestToken = session('guest_token') ?? request()->cookie('visitor_token');

            // Sync session if cookie exists but session is empty
            if (! $userId && $guestToken && ! session()->has('guest_token')) {
                session(['guest_token' => $guestToken, 'guest_name' => 'Wisatawan']);
            }

            $activeSession = null;
            if ($userId || $guestToken) {
                $query = RouteSession::with('tourRoute')->where('status', 'active');
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('guest_token', $guestToken);
                }
                $activeSession = $query->first();
            }
            $view->with('activeEdutourismSession', $activeSession);
        });

        View::composer(['layouts.app', 'user.edutourism.active'], function (ViewInstance $view): void {
            $pageData = $view->getData();
            $weather = $pageData['weather'] ?? WeatherReport::query()->first();

            $hasDensitySnapshot = isset(
                $pageData['totalCurrent'],
                $pageData['totalMax'],
                $pageData['densityStatus'],
                $pageData['densityPercent'],
                $pageData['warningThreshold'],
                $pageData['criticalThreshold'],
            );

            if ($hasDensitySnapshot) {
                $totalCurrent = $pageData['totalCurrent'];
                $totalMax = $pageData['totalMax'];
                $densityStatus = $pageData['densityStatus'];
                $densityPercent = $pageData['densityPercent'];
                $warningThreshold = $pageData['warningThreshold'];
                $criticalThreshold = $pageData['criticalThreshold'];
            } else {
                $zones = Cache::tags(['capacity'])->flexible(
                    'capacity_zones_active_array',
                    [60, 300],
                    fn (): array => CapacityZone::where('is_active', true)
                        ->get()
                        ->append('occupancy_percentage')
                        ->toArray(),
                );
                $zones = CapacityZone::withLiveCounts($zones);
                $masterZone = collect($zones)->firstWhere('zone_identifier', 'desa_penglipuran');

                $totalCurrent = collect($zones)->sum('current_count');
                $totalMax = collect($zones)->sum('max_capacity');
                $densityPercent = $totalMax > 0 ? ($totalCurrent / $totalMax) * 100 : 0;
                $warningThreshold = $masterZone['warning_threshold'] ?? 60;
                $criticalThreshold = $masterZone['critical_threshold'] ?? 80;
                $densityStatus = CapacityZone::statusFor($densityPercent, $warningThreshold, $criticalThreshold);
            }

            $view->with([
                'sharedWeather' => $weather,
                'sharedTotalCurrent' => $totalCurrent,
                'sharedTotalMax' => $totalMax,
                'sharedDensityStatus' => $densityStatus,
                'sharedDensityPercent' => $densityPercent,
                'sharedWarningThreshold' => $warningThreshold,
                'sharedCriticalThreshold' => $criticalThreshold,
            ]);
        });

        // Register Cache Invalidation Observer
        $observer = CacheInvalidationObserver::class;
        ArModel::observe($observer);
        CapacityZone::observe($observer);
        CulturalObject::observe($observer);
        Event::observe($observer);
        Facility::observe($observer);
        MapLocation::observe($observer);
        TourRoute::observe($observer);
        TourRoutePoint::observe($observer);
        TraditionalHouse::observe($observer);
        UmkmProduct::observe($observer);
        UmkmProductCategory::observe($observer);
        UmkmProfile::observe($observer);
    }
}
