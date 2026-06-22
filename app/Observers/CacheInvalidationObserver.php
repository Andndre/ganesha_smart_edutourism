<?php

namespace App\Observers;

use App\Models\ArModel;
use App\Models\CapacityZone;
use App\Models\CulturalObject;
use App\Models\Event;
use App\Models\Facility;
use App\Models\MapLocation;
use App\Models\TourPackage;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CacheInvalidationObserver
{
    /**
     * Handle the Model "saved" event.
     */
    public function saved(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Invalidate caches related to the model.
     */
    protected function invalidateCache(Model $model): void
    {
        if ($model instanceof CapacityZone) {
            Cache::forget('capacity_zones_active_array');
        } elseif ($model instanceof CulturalObject) {
            Cache::forget('cultural_objects_all_array');
            Cache::forget('explore_map_locations_array');

            // Invalidate specific slug cache
            Cache::forget('cultural_object_array_'.$model->slug);
            if ($model->isDirty('slug')) {
                Cache::forget('cultural_object_array_'.$model->getOriginal('slug'));
            }
        } elseif ($model instanceof Event) {
            foreach (['all', 'ceremony', 'cultural', 'workshop', 'culinary'] as $categoryKey) {
                Cache::forget('public_events_upcoming_'.$categoryKey);
                Cache::forget('public_events_calendar_'.$categoryKey);
            }
        } elseif ($model instanceof TourPackage) {
            Cache::forget('tour_packages_active_array');
        } elseif ($model instanceof TourRoute || $model instanceof TourRoutePoint) {
            Cache::forget('explore_map_routes_array');
            Cache::forget('edutourism_routes_array');
        } elseif ($model instanceof UmkmProductCategory) {
            Cache::forget('umkm_categories_array');
        } elseif ($model instanceof MapLocation || $model instanceof Facility || $model instanceof UmkmProfile || $model instanceof ArModel) {
            Cache::forget('explore_map_locations_array');
        }
    }
}
