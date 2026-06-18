<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'model_3d_path', 'model_3d_usdz_path', 'audio_narration_path'])]
class ArModel extends Model
{
    use HasFactory;

    /**
     * Get the markers that use this 3D model.
     *
     * @return HasMany<ArMarker>
     */
    public function arMarkers(): HasMany
    {
        return $this->hasMany(ArMarker::class);
    }
}
