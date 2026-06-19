<?php

namespace App\Http\Controllers;

use App\Models\CulturalObject;
use App\Models\Facility;
use App\Models\MapLocation;
use App\Models\TourRoute;
use App\Models\UmkmProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ExploreController extends Controller
{
    /**
     * Display the interactive tourist map.
     */
    public function index(): View
    {
        $locations = MapLocation::with(['locationable', 'arModel'])->get()->map(function ($loc) {
            // Map category to match JavaScript filters
            $category = $loc->category;
            if ($loc->locationable_type === Facility::class && $loc->locationable && $loc->locationable->type === 'toilet') {
                $category = 'toilets';
            } elseif ($category === 'facility') {
                $category = 'facilities';
            } elseif ($category === 'toilet') {
                $category = 'toilets';
            } elseif ($category === 'emergency') {
                $category = 'facilities';
            }

            $description = '';
            $detailUrl = null;
            $hasAr = false;
            $images = [];

            if ($loc->locationable) {
                $description = $loc->locationable->description ?? '';
                if ($loc->locationable_type === CulturalObject::class) {
                    $detailUrl = route('cultural-object', ['slug' => $loc->locationable->slug]);
                    $hasAr = $loc->arModel !== null && $loc->arModel->model_3d_path !== null;
                    if ($loc->locationable->historical_images && is_array($loc->locationable->historical_images)) {
                        foreach ($loc->locationable->historical_images as $img) {
                            $images[] = asset('storage/'.$img);
                        }
                    }
                } elseif ($loc->locationable_type === UmkmProfile::class) {
                    $detailUrl = route('umkm');
                    if (! empty($loc->locationable->image)) {
                        $images[] = asset('storage/'.$loc->locationable->image);
                    }
                }
            }

            return [
                'lat' => (float) $loc->latitude,
                'lng' => (float) $loc->longitude,
                'name' => $loc->name,
                'cat' => $category,
                'desc' => $description,
                'is_accessible' => (bool) $loc->is_accessible,
                'accessibility' => $loc->accessibility_notes ?? '',
                'detail_url' => $detailUrl,
                'has_ar' => $hasAr,
                'image' => $images[0] ?? null,
                'images' => $images,
            ];
        });

        // Load active tour routes with points
        $routes = TourRoute::where('is_active', true)->with('routePoints.locationable')->get();
        $formattedRoutes = $routes->map(function ($route) {
            $points = $route->routePoints->map(function ($point) {
                $locationable = $point->locationable;
                if (! $locationable) {
                    return null;
                }

                $lat = $locationable->latitude ?? null;
                $lng = $locationable->longitude ?? null;
                if (! $lat || ! $lng) {
                    if (\method_exists($locationable, 'mapLocation') && $locationable->mapLocation) {
                        $lat = $locationable->mapLocation->latitude;
                        $lng = $locationable->mapLocation->longitude;
                    }
                }

                if ($lat !== null && $lng !== null) {
                    return [$lat, $lng];
                }

                return null;
            })->filter()->values();

            return [
                'id' => $route->id,
                'name' => $route->name,
                'is_smart_route' => $route->is_smart_route,
                'coordinates' => $points,
            ];
        });

        // Initialize empty heatmap data array for real-time live visitors only
        $heatmapData = [];

        // Add live tracked visitors from Cache
        $activeVisitors = Cache::get('active_visitors', []);
        foreach ($activeVisitors as $sessionId => $visitor) {
            if ((now()->timestamp - $visitor['last_seen']) < 300) {
                $heatmapData[] = [
                    'lat' => (float) $visitor['lat'],
                    'lng' => (float) $visitor['lng'],
                    'intensity' => 0.9, // High intensity for live users
                    'category' => 'cultural', // Map to cultural for now so it shows up in default filters, or we can make it always visible
                    'name' => 'Pengunjung Aktif',
                    'is_live_user' => true,
                    'session_id' => $sessionId,
                ];
            }
        }

        $defaultLat = (float) config('services.penglipuran.latitude');
        $defaultLon = (float) config('services.penglipuran.longitude');

        return view('user.explore.index', compact('locations', 'formattedRoutes', 'heatmapData', 'defaultLat', 'defaultLon'));
    }
}
