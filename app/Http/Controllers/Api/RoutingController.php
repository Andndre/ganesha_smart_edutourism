<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RoutingController extends Controller
{
    private const GRID_SNAP_RESOLUTION = 0.00005;

    /**
     * Get routing directions from local OpenRouteService with Redis caching.
     */
    public function directions(Request $request)
    {
        $validated = $request->validate([
            'coordinates' => 'required|array|min:2|max:25',
            'coordinates.*' => 'required|array|size:2', // [lng, lat]
            'coordinates.*.0' => 'required|numeric|between:-180,180',
            'coordinates.*.1' => 'required|numeric|between:-90,90',
        ]);

        $snappedCoordinates = $this->snapCoordinates($validated['coordinates']);
        $cacheKey = 'ors:route:foot-walking:'.md5(json_encode($snappedCoordinates));

        $cachedRoute = Cache::get($cacheKey);
        if (\is_array($cachedRoute)) {
            return response()->json($cachedRoute)->header('X-Cache', 'HIT');
        }

        $baseUrl = config('services.ors.base_url', 'http://localhost:8080');
        $timeout = (int) config('services.ors.timeout', 3);
        $cacheTtl = (int) config('services.ors.cache_ttl', 604800);
        $endpoint = rtrim($baseUrl, '/').'/ors/v2/directions/foot-walking/geojson';

        try {
            $response = Http::timeout($timeout)->post($endpoint, [
                'coordinates' => $snappedCoordinates,
                'elevation' => false,
            ]);

            if ($response->successful()) {
                $routeData = $response->json();

                if (\is_array($routeData) && $cacheTtl > 0) {
                    Cache::put($cacheKey, $routeData, $cacheTtl);
                }

                return response()->json($routeData)->header('X-Cache', 'MISS');
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

    /**
     * Snap [lng, lat] coordinate pairs to a ~5.5m grid (0.00005 degrees).
     *
     * @param  array<int, array<int, float|int|string>>  $coordinates
     * @return array<int, array{0: float, 1: float}>
     */
    private function snapCoordinates(array $coordinates): array
    {
        return array_map(fn (array $point): array => [
            round(round((float) $point[0] / self::GRID_SNAP_RESOLUTION) * self::GRID_SNAP_RESOLUTION, 5),
            round(round((float) $point[1] / self::GRID_SNAP_RESOLUTION) * self::GRID_SNAP_RESOLUTION, 5),
        ], $coordinates);
    }
}
