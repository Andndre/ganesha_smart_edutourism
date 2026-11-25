<?php

namespace App\Models;

use App\Models\Concerns\HasMapLocation;
use App\Models\Concerns\HasTranslatableArrayOutput;
use Database\Factories\TraditionalHouseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Validation\ValidationException;
use Spatie\Translatable\HasTranslations;

#[Fillable(['number', 'head_name', 'male_count', 'female_count', 'description', 'cover_image_path', 'gallery', 'ar_marker_id', 'is_open_to_visitors', 'is_published'])]
class TraditionalHouse extends Model
{
    protected static function booted(): void
    {
        static::saved(function (self $house): void {
            if ($house->wasChanged('number')) {
                $house->umkmProfile()->where('profile_type', 'house')->first()?->save();
                $house->mapLocation?->update(['name' => $house->getMapDisplayName()]);
            }
        });
        static::deleting(function (self $house): void {
            if ($house->umkmProfile()->where('profile_type', 'house')->exists()) {
                throw ValidationException::withMessages(['house' => 'Hapus profil UMKM Rumah terlebih dahulu sebelum menghapus rumah.']);
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

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
            ->whereHas('umkmProfile', fn (Builder $profiles) => $profiles->active());
    }

    public function getMapDisplayName(): string
    {
        return __('Pekarangan No.').' '.$this->number;
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (! $this->cover_image_path) {
            return null;
        }

        return str_starts_with($this->cover_image_path, 'images/')
            ? asset($this->cover_image_path)
            : asset('storage/'.$this->cover_image_path);
    }

    /** @return MorphOne<MapLocation> */
    public function mapLocation(): MorphOne
    {
        return $this->morphOne(MapLocation::class, 'locationable');
    }

    /** @return HasOne<UmkmProfile, $this> */
    public function umkmProfile(): HasOne
    {
        return $this->hasOne(UmkmProfile::class);
    }
}
