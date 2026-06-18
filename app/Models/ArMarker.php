<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ar_marker_id', 'ar_marker_patt_path', 'ar_model_id', 'map_location_id'])]
class ArMarker extends Model
{
    use HasFactory;

    /**
     * Get the 3D model associated with this marker.
     *
     * @return BelongsTo<ArModel, ArMarker>
     */
    public function arModel(): BelongsTo
    {
        return $this->belongsTo(ArModel::class);
    }

    /**
     * Get the map location where this marker is placed.
     *
     * @return BelongsTo<MapLocation, ArMarker>
     */
    public function mapLocation(): BelongsTo
    {
        return $this->belongsTo(MapLocation::class);
    }
}
