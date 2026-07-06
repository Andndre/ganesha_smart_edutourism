<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableArrayOutput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['tour_route_id', 'locationable_type', 'locationable_id', 'order', 'estimated_visit_minutes', 'storytelling_content', 'qr_code_token', 'intro_video_paths', 'intro_audio_paths'])]
class TourRoutePoint extends Model
{
    use HasFactory;
    use HasTranslatableArrayOutput;
    use HasTranslations;

    public array $translatable = ['storytelling_content'];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'estimated_visit_minutes' => 'integer',
            'intro_video_paths' => 'array',
            'intro_audio_paths' => 'array',
        ];
    }

    /**
     * Resolve the intro video path for the current locale, falling back to the
     * app's fallback locale. Optional — most points have no intro video.
     */
    public function getIntroVideoPathAttribute(): ?string
    {
        $locale = app()->getLocale();
        $paths = $this->intro_video_paths ?? [];

        return $paths[$locale] ?? $paths[config('app.fallback_locale', 'en')] ?? null;
    }

    /**
     * Resolve the intro audio path for the current locale, falling back to the
     * app's fallback locale. Optional — most points have no intro audio.
     */
    public function getIntroAudioPathAttribute(): ?string
    {
        $locale = app()->getLocale();
        $paths = $this->intro_audio_paths ?? [];

        return $paths[$locale] ?? $paths[config('app.fallback_locale', 'en')] ?? null;
    }

    /**
     * Get the tour route that owns this point.
     *
     * @return BelongsTo<TourRoute, TourRoutePoint>
     */
    public function tourRoute(): BelongsTo
    {
        return $this->belongsTo(TourRoute::class);
    }

    /**
     * Get the owning locationable model.
     */
    public function locationable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Gamified missions attached to this point, in play order.
     *
     * @return HasMany<RouteMission, TourRoutePoint>
     */
    public function missions(): HasMany
    {
        return $this->hasMany(RouteMission::class)->orderBy('order');
    }
}
