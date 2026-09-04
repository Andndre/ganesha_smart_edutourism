<?php

namespace App\Models\Concerns;

use App\Models\MapLocation;
use App\Models\TourRoutePoint;

trait HasMapLocation
{
    /**
     * Cascade-delete the map location when the owning model is deleted.
     */
    protected static function bootHasMapLocation(): void
    {
        static::deleted(function (self $model) {
            $model->mapLocation()->delete();

            // Titik rute menunjuk ke model ini secara polymorphic, tanpa foreign key, jadi
            // menghapus objeknya meninggalkan titik hantu: tanpa nama (jatuh ke "Titik
            // Perhentian"), tanpa koordinat, sehingga tidak pernah bisa dicapai GPS — dan
            // form edit rute di admin ikut menyembunyikannya, jadi tidak bisa dihapus dari sana.
            TourRoutePoint::where('locationable_type', $model->getMorphClass())
                ->where('locationable_id', $model->getKey())
                ->delete();
        });
    }

    /**
     * Override in model to specify which field holds the map display name.
     */
    protected function mapLocationNameField(): string
    {
        return 'name';
    }

    /**
     * Extract the localized name string for the map pin label.
     */
    public function getMapDisplayName(): string
    {
        $source = $this->{$this->mapLocationNameField()};
        $locale = config('app.fallback_locale', 'en');

        return \is_string($source) ? $source : ($source[$locale] ?? $source['en'] ?? reset($source));
    }

    /**
     * Sync (create or update) the map location for this model.
     * Pass location-specific attrs; 'name' is auto-populated.
     */
    public function syncMapLocation(array $attrs, bool $isUpdate = false): MapLocation
    {
        $attrs['name'] = $this->getMapDisplayName();

        if ($isUpdate) {
            return $this->mapLocation()->updateOrCreate([], $attrs);
        }

        return $this->mapLocation()->create($attrs);
    }
}
