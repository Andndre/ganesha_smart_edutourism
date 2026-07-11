<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitorLog
{
    /**
     * Log one page_view per session per day for visitor trend reporting.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('get')) {
            return $next($request);
        }

        $today = Carbon::today()->toDateString();

        if ($request->session()->get('visitor_log_date') !== $today) {
            VisitorLog::create([
                'session_id' => $request->session()->getId(),
                'user_id' => $request->user()?->id,
                'event_type' => 'page_view',
                'logged_at' => now(),
            ]);

            $request->session()->put('visitor_log_date', $today);
        }

        return $next($request);
    }
}
