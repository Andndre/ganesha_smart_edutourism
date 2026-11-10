<?php

namespace App\Models;

use App\Models\Concerns\HasMapLocation;
use App\Models\Concerns\HasTranslatableArrayOutput;
use Database\Factories\TraditionalHouseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;

#[Fillable(['number', 'head_name', 'male_count', 'female_count', 'description', 'cover_image_path', 'gallery', 'ar_marker_id', 'is_open_to_visitors', 'is_published'])]
class TraditionalHouse extends Model
{
    /** @use HasFactory<TraditionalHouseFactory> */
    use HasFactory;

    use HasMapLocation;
    use HasTranslatableArrayOutput;
    use HasTranslations;

    public array $translatable = ['description'];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'is_open_to_visitors' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereHas('mapLocation')
            ->whereHas('umkmProfiles', fn (Builder $profiles) => $profiles->active());
    }

    public function getMapDisplayName(): string
    {
        return __('Pekarangan No.').' '.$this->number;
    }

    /** @return MorphOne<MapLocation> */
    public function mapLocation(): MorphOne
    {
        return $this->morphOne(MapLocation::class, 'locationable');
    }

    /** @return HasMany<UmkmProfile> */
    public function umkmProfiles(): HasMany
    {
        return $this->hasMany(UmkmProfile::class);
    }

    /** @return BelongsToMany<CulturalObject> */
    public function culturalObjects(): BelongsToMany
    {
        return $this->belongsToMany(CulturalObject::class);
    }
}
