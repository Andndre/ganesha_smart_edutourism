<?php

namespace App\Providers;

use App\Models\ArModel;
use App\Models\CapacityZone;
use App\Models\CulturalObject;
use App\Models\Event;
use App\Models\Facility;
use App\Models\MapLocation;
use App\Models\RouteSession;
use App\Models\TourPackage;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Observers\CacheInvalidationObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

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
        if (app()->environment('local') && isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }

        LogViewer::auth(function ($request) {
            return $request->user() && $request->user()->isAdmin();
        });

        View::composer(['layouts.app', 'user.umkm.index'], function ($view) {
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

        // Register Cache Invalidation Observer
        $observer = CacheInvalidationObserver::class;
        ArModel::observe($observer);
        CapacityZone::observe($observer);
        CulturalObject::observe($observer);
        Event::observe($observer);
        Facility::observe($observer);
        MapLocation::observe($observer);
        TourPackage::observe($observer);
        TourRoute::observe($observer);
        TourRoutePoint::observe($observer);
        UmkmProductCategory::observe($observer);
        UmkmProfile::observe($observer);
    }
}
