<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'description', 'model_3d_path', 'model_3d_usdz_path', 'audio_narration_paths', 'ar_marker_id', 'ar_marker_patt_path', 'map_location_id', 'thumbnail_path'])]
class ArModel extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return ['audio_narration_paths' => 'array'];
    }

    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();
        foreach ($this->getTranslatableAttributes() as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = $this->getTranslations($key);
            }
        }
        return $attributes;
    }

    public function getAudioNarrationPathAttribute(): ?string
    {
        $locale = app()->getLocale();
        $paths = $this->audio_narration_paths ?? [];
        return $paths[$locale] ?? $paths[config('app.fallback_locale', 'en')] ?? null;
    }

    public function mapLocation(): BelongsTo
    {
        return $this->belongsTo(MapLocation::class);
    }
}
