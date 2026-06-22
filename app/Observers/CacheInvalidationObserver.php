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
            Cache::tags(['capacity'])->flush();
        } elseif ($model instanceof CulturalObject) {
            Cache::tags(['cultural', 'explore'])->flush();
        } elseif ($model instanceof Event) {
            Cache::tags(['events'])->flush();
        } elseif ($model instanceof TourPackage) {
            Cache::tags(['packages'])->flush();
        } elseif ($model instanceof TourRoute || $model instanceof TourRoutePoint) {
            Cache::tags(['explore', 'edutourism'])->flush();
        } elseif ($model instanceof UmkmProductCategory) {
            Cache::tags(['umkm'])->flush();
        } elseif ($model instanceof MapLocation || $model instanceof Facility || $model instanceof UmkmProfile || $model instanceof ArModel) {
            Cache::tags(['explore'])->flush();
        }
    }
}
