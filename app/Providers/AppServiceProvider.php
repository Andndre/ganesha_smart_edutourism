<?php

namespace App\Providers;

use App\Models\RouteSession;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
    }
}
