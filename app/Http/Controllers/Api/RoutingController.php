<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RoutingController extends Controller
{
    /**
     * Get routing directions from local OpenRouteService.
     */
    public function directions(Request $request)
    {
        $request->validate([
            'coordinates' => 'required|array|min:2',
            'coordinates.*' => 'required|array|size:2', // [lng, lat]
        ]);

        $baseUrl = config('services.ors.base_url', 'http://localhost:8080');
        $endpoint = rtrim($baseUrl, '/').'/ors/v2/directions/foot-walking/geojson';

        try {
            $response = Http::post($endpoint, [
                'coordinates' => $request->input('coordinates'),
                'elevation' => false,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('ORS API Error: '.$response->body());

            return response()->json([
                'error' => 'Failed to calculate route from routing service',
                'details' => $response->json(),
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('ORS Connection Error: '.$e->getMessage());

            return response()->json([
                'error' => 'Could not connect to routing service',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
