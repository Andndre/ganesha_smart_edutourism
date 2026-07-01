<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAudioNarration;
use App\Models\Concerns\HasTranslatableArrayOutput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'description', 'model_3d_path', 'model_3d_usdz_path', 'audio_narration_paths', 'ar_marker_id', 'ar_marker_patt_path', 'map_location_id', 'thumbnail_path'])]
class ArModel extends Model
{
    use HasFactory;
    use HasLocalizedAudioNarration;
    use HasTranslatableArrayOutput;
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return ['audio_narration_paths' => 'array'];
    }

    public function mapLocation(): BelongsTo
    {
        return $this->belongsTo(MapLocation::class);
    }
}
