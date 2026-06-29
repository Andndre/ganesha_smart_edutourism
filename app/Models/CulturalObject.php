<?php

namespace App\Models;

use App\Models\Concerns\HasMapLocation;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'short_description', 'description', 'category', 'historical_images', 'audio_narration_paths'])]
class CulturalObject extends Model
{
    use HasFactory;
    use HasMapLocation;
    use HasSlug;
    use HasTranslations;

    public array $translatable = ['name', 'short_description', 'description'];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'historical_images' => 'array',
            'audio_narration_paths' => 'array',
        ];
    }

    /**
     * Override attributesToArray to handle Spatie translatable attributes.
     * Spatie's getAttributeValue() override is not called by Laravel's default
     * attributesToArray(), so translatable fields would be serialized as raw JSON
     * strings in toArray() output. We use getTranslations() to return all locales
     * as ['en' => ..., 'id' => ...] instead of a single-locale string.
     */
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

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::deleted(function (CulturalObject $culturalObject) {
            $culturalObject->mapLocation()->delete();
        });
    }

    /**
     * Get the stories associated with this cultural object.
     *
     * @return HasMany<CulturalStory>
     */
    public function stories(): HasMany
    {
        return $this->hasMany(CulturalStory::class)->orderBy('order');
    }

    /**
     * Get the map location for this cultural object.
     *
     * @return MorphOne<MapLocation>
     */
    public function mapLocation(): MorphOne
    {
        return $this->morphOne(MapLocation::class, 'locationable');
    }

    /**
     * Get the quizzes associated with this cultural object.
     *
     * @return HasMany<CulturalObjectQuiz>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(CulturalObjectQuiz::class);
    }

    /**
     * Scope a query to only include objects with coordinates.
     *
     * @param  Builder<CulturalObject>  $query
     * @return Builder<CulturalObject>
     */
    public function scopeWithCoordinates(Builder $query): Builder
    {
        return $query->whereHas('mapLocation');
    }

    /**
     * Scope a query to filter by category.
     *
     * @param  Builder<CulturalObject>  $query
     * @return Builder<CulturalObject>
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by AR availability.
     *
     * @param  Builder<CulturalObject>  $query
     * @return Builder<CulturalObject>
     */
    public function scopeWithAr(Builder $query): Builder
    {
        return $query->whereHas('mapLocation.arModel');
    }

    public function getArMarkerIdAttribute(): ?string
    {
        return $this->mapLocation?->arModel?->ar_marker_id;
    }

    public function getArMarkerPattPathAttribute(): ?string
    {
        return $this->mapLocation?->arModel?->ar_marker_patt_path;
    }

    public function getModel3dPathAttribute(): ?string
    {
        return $this->mapLocation?->arModel?->model_3d_path;
    }

    public function getModel3dUsdzPathAttribute(): ?string
    {
        return $this->mapLocation?->arModel?->model_3d_usdz_path;
    }

    public function getAudioNarrationPathAttribute(): ?string
    {
        $locale = app()->getLocale();
        $paths = $this->audio_narration_paths ?? [];
        return $paths[$locale] ?? $paths[config('app.fallback_locale', 'en')] ?? null;
    }

    /**
     * Get the favorites for this cultural object.
     *
     * @return MorphMany<UserFavorite>
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(UserFavorite::class, 'favoritable');
    }

    /**
     * Get the visits for this cultural object.
     *
     * @return MorphMany<UserVisit>
     */
    public function visits(): MorphMany
    {
        return $this->morphMany(UserVisit::class, 'visitable');
    }

    /**
     * Check if this cultural object is favorited by the given user.
     */
    public function isFavoritedBy(User $user): bool
    {
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if this cultural object is visited by the given user.
     */
    public function isVisitedBy(User $user): bool
    {
        return $this->visits()->where('user_id', $user->id)->exists();
    }
}
