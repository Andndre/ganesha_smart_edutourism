<?php

namespace App\Http\Controllers;

use App\Models\CapacityZone;
use App\Models\CulturalObject;
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
        $locations = MapLocation::all()->map(function ($loc) {
            // Map category to match JavaScript filters
            $category = $loc->category;
            if ($category === 'facility') {
                $category = 'facilities';
            } elseif ($category === 'toilet') {
                $category = 'toilets';
            } elseif ($category === 'emergency') {
                $category = 'toilets'; // or facilities
            }

            return [
                'lat' => $loc->latitude,
                'lng' => $loc->longitude,
                'name' => $loc->name,
                'cat' => $category,
                'desc' => $loc->accessibility_notes ?? '',
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
        $heatmapData = MapLocation::all()->map(function ($loc) {
            $intensity = 0.5;
            if ($loc->locationable_type === CapacityZone::class) {
                $zone = CapacityZone::find($loc->locationable_id);
                if ($zone) {
                    $intensity = $zone->max_capacity > 0 ? ($zone->current_count / $zone->max_capacity) : 0.5;
                }
            } elseif ($loc->locationable_type === UmkmProfile::class) {
                $intensity = 0.6;
            } elseif ($loc->locationable_type === CulturalObject::class) {
                $intensity = 0.8;
            }

            $category = $loc->category;
            if ($category === 'facility') {
                $category = 'facilities';
            } elseif ($category === 'toilet') {
                $category = 'toilets';
            } elseif ($category === 'emergency') {
                $category = 'facilities';
            }

            return [
                'lat' => $loc->latitude,
                'lng' => $loc->longitude,
                'intensity' => \round($intensity, 2),
                'category' => $category,
            ];
        });

        return view('pages.explore.index', \compact('locations', 'formattedRoutes', 'heatmapData'));
    }
}
