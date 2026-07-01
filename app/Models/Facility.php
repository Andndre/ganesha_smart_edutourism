<?php

namespace App\Models;

use App\Models\Concerns\HasMapLocation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'type', 'description', 'is_active'])]
class Facility extends Model
{
    use HasMapLocation;
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    /**
     * Get the map location for this facility.
     *
     * @return MorphOne<MapLocation>
     */
    public function mapLocation(): MorphOne
    {
        return $this->morphOne(MapLocation::class, 'locationable');
    }
}
