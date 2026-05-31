<?php

namespace App\Http\Controllers;

use App\Models\CapacityZone;
use App\Models\CulturalObject;
use App\Models\Facility;
use App\Models\MapLocation;
use App\Models\TourRoute;
use App\Models\UmkmProfile;
use Illuminate\View\View;

class ExploreController extends Controller
{
    /**
     * Display the interactive tourist map.
     */
    public function index(): View
    {
        $locations = MapLocation::with('locationable')->get()->map(function ($loc) {
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
            $image = null;

            if ($loc->locationable) {
                $description = $loc->locationable->description ?? '';
                if ($loc->locationable_type === CulturalObject::class) {
                    $detailUrl = route('cultural-object', $loc->locationable->id);
                    $hasAr = ! empty($loc->locationable->ar_marker_id) || ! empty($loc->locationable->model_3d_path);
                    if ($loc->locationable->historical_images && is_array($loc->locationable->historical_images) && count($loc->locationable->historical_images) > 0) {
                        $image = asset('storage/'.$loc->locationable->historical_images[0]);
                    }
                } elseif ($loc->locationable_type === UmkmProfile::class) {
                    $detailUrl = route('umkm');
                }
            }

            return [
                'lat' => (float) $loc->latitude,
                'lng' => (float) $loc->longitude,
                'name' => $loc->name,
                'cat' => $category,
                'desc' => $description,
                'accessibility' => $loc->accessibility_notes ?? '',
                'detail_url' => $detailUrl,
                'has_ar' => $hasAr,
                'image' => $image,
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

        // Build crowd density heatmap data
        $heatmapData = MapLocation::with('locationable')->get()->map(function ($loc) {
            $intensity = 0.5;
            if ($loc->locationable_type === CapacityZone::class) {
                $zone = $loc->locationable;
                if ($zone) {
                    $intensity = $zone->max_capacity > 0 ? ($zone->current_count / $zone->max_capacity) : 0.5;
                }
            } elseif ($loc->locationable_type === UmkmProfile::class) {
                $intensity = 0.6;
            } elseif ($loc->locationable_type === CulturalObject::class) {
                $intensity = 0.8;
            }

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

            return [
                'lat' => (float) $loc->latitude,
                'lng' => (float) $loc->longitude,
                'intensity' => \round($intensity, 2),
                'category' => $category,
                'name' => $loc->name,
            ];
        });

        $defaultLat = (float) env('PENGLIPURAN_LAT', -8.422303596762355);
        $defaultLon = (float) env('PENGLIPURAN_LON', 115.35948833933173);

        return view('user.explore.index', compact('locations', 'formattedRoutes', 'heatmapData', 'defaultLat', 'defaultLon'));
    }
}
