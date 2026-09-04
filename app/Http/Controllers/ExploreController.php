<?php

namespace App\Http\Controllers;

use App\Models\CulturalObject;
use App\Models\Facility;
use App\Models\MapLocation;
use App\Models\TourRoute;
use App\Models\UmkmProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExploreController extends Controller
{
    /**
     * Display the interactive tourist map.
     */
    public function index(): View
    {
        $locale = app()->getLocale();
        $locations = Cache::tags(['explore'])->flexible("explore_map_locations_array_v4_{$locale}", [86400, 172800], function () use ($locale) {
            return MapLocation::with(['locationable' => function ($morphTo) {
                $morphTo->morphWith([CulturalObject::class => ['arModel']]);
            }])->get()->map(function ($loc) use ($locale) {
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

                $name = $loc->name;
                $description = '';
                $detailUrl = null;
                $hasAr = false;
                $images = [];
                $placeType = null;
                $isDetail = false;

                if ($loc->locationable) {
                    $descTranslations = method_exists($loc->locationable, 'getTranslations')
                        ? $loc->locationable->getTranslations('description')
                        : null;
                    $description = translateValue($descTranslations ?: ($loc->locationable->description ?? ''), $locale);

                    if ($loc->locationable_type === CulturalObject::class) {
                        $nameTranslations = method_exists($loc->locationable, 'getTranslations')
                            ? $loc->locationable->getTranslations('name')
                            : null;
                        $name = translateValue($nameTranslations ?: ($loc->locationable->name ?? null), $locale) ?: $loc->name;
                        $detailUrl = route('cultural-object', ['slug' => $loc->locationable->slug]);
                        $placeType = $loc->locationable->place_type;
                        $isDetail = (bool) $loc->locationable->is_detail;
                        $hasAr = $loc->locationable->arModel !== null && $loc->locationable->arModel->model_3d_path !== null;
                        if ($loc->locationable->historical_images && \is_array($loc->locationable->historical_images)) {
                            foreach ($loc->locationable->historical_images as $img) {
                                $images[] = asset('storage/'.$img);
                            }
                        }
                    } elseif ($loc->locationable_type === UmkmProfile::class) {
                        $nameTranslations = method_exists($loc->locationable, 'getTranslations')
                            ? $loc->locationable->getTranslations('business_name')
                            : null;
                        $name = translateValue($nameTranslations ?: ($loc->locationable->business_name ?? null), $locale) ?: $loc->name;
                        $detailUrl = route('umkm');
                        if (! empty($loc->locationable->image)) {
                            $images[] = asset('storage/'.$loc->locationable->image);
                        }
                    } elseif ($loc->locationable_type === Facility::class) {
                        $nameTranslations = method_exists($loc->locationable, 'getTranslations')
                            ? $loc->locationable->getTranslations('name')
                            : null;
                        $name = translateValue($nameTranslations ?: ($loc->locationable->name ?? null), $locale) ?: $loc->name;
                    } elseif (isset($loc->locationable->name)) {
                        $nameTranslations = method_exists($loc->locationable, 'getTranslations')
                            ? $loc->locationable->getTranslations('name')
                            : null;
                        $name = translateValue($nameTranslations ?: $loc->locationable->name, $locale) ?: $loc->name;
                    }
                }

                $accTranslations = method_exists($loc, 'getTranslations')
                    ? $loc->getTranslations('accessibility_notes')
                    : null;
                $accessibility = translateValue($accTranslations ?: ($loc->accessibility_notes ?? ''), $locale);

                return [
                    'id' => $loc->id,
                    'lat' => (float) $loc->latitude,
                    'lng' => (float) $loc->longitude,
                    'name' => $name,
                    'raw_name' => $loc->name,
                    'cat' => $category,
                    // Memilih glyph pin; null jatuh balik ke candi bentar
                    'place_type' => $placeType,
                    // Objek tingkat 2 (komponen pekarangan rumah): pinnya hanya dirender saat zoom dekat
                    'is_detail' => $isDetail,
                    // Plain text only: the client searches this string and the sheet shows a 120-char preview
                    'desc' => Str::of(strip_tags((string) $description))->squish()->limit(160)->toString(),
                    'is_accessible' => (bool) $loc->is_accessible,
                    'accessibility' => $accessibility,
                    'detail_url' => $detailUrl,
                    'has_ar' => $hasAr,
                    'image' => $images[0] ?? null,
                    'images' => $images,
                ];
            })->all();
        });

        $formattedRoutes = Cache::tags(['explore'])->flexible("explore_map_routes_array_v2_{$locale}", [86400, 172800], function () use ($locale) {
            $routes = TourRoute::where('is_active', true)->with('routePoints.locationable')->get();

            return $routes->map(function ($route) use ($locale) {
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

                $nameTranslations = method_exists($route, 'getTranslations')
                    ? $route->getTranslations('name')
                    : null;
                $name = translateValue($nameTranslations ?: ($route->name ?? null), $locale) ?: $route->name;

                return [
                    'id' => $route->id,
                    'name' => $name,
                    'coordinates' => $points,
                ];
            })->all();
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
                    'name' => __('Pengunjung Aktif'),
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
