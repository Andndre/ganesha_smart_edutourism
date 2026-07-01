<?php

namespace App\Models\Concerns;

use App\Models\MapLocation;

trait HasMapLocation
{
    /**
     * Cascade-delete the map location when the owning model is deleted.
     */
    protected static function bootHasMapLocation(): void
    {
        static::deleted(function (self $model) {
            $model->mapLocation()->delete();
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
