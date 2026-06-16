<?php

namespace App\Http\Controllers;

use App\Events\VisitorLocationUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TrackingController extends Controller
{
    /**
     * Receive GPS ping from clients and broadcast to heatmap.
     */
    public function ping(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'session_id' => 'required|string',
        ]);

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;

        // Approximate bounding box for Desa Penglipuran
        // Center: -8.4223, 115.3594
        $inBounds = true; // ($lat < -8.410 && $lat > -8.435) && ($lng > 115.350 && $lng < 115.370);

        if ($inBounds) {
            // Store active visitor in Cache
            $activeVisitors = Cache::get('active_visitors', []);

            $activeVisitors[$request->session_id] = [
                'lat' => $lat,
                'lng' => $lng,
                'last_seen' => now()->timestamp,
            ];

            // Clean up visitors older than 5 minutes (300 seconds)
            $activeVisitors = array_filter($activeVisitors, function ($visitor) {
                return (now()->timestamp - $visitor['last_seen']) < 300;
            });

            Cache::put('active_visitors', $activeVisitors, now()->addMinutes(5));

            broadcast(new VisitorLocationUpdated($lat, $lng, $request->session_id));

            return response()->json(['status' => 'tracked']);
        }

        return response()->json(['status' => 'ignored_outside_polygon']);
    }
}
