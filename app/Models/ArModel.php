<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['model_3d_path', 'model_3d_usdz_path', 'ar_marker_id', 'ar_marker_patt_path', 'cultural_object_id', 'thumbnail_path'])]
class ArModel extends Model
{
    use HasFactory;

    public function getNameAttribute(): ?string
    {
        return $this->culturalObject?->name;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->culturalObject?->description;
    }

    public function getAudioNarrationPathAttribute(): ?string
    {
        return $this->culturalObject?->audio_narration_path;
    }

    public function culturalObject(): BelongsTo
    {
        return $this->belongsTo(CulturalObject::class);
    }
}
