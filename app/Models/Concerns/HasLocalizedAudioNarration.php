<?php

namespace App\Models\Concerns;

trait HasLocalizedAudioNarration
{
    /**
     * Resolve the audio narration path for the current locale, falling back
     * to the app's fallback locale. Requires an `audio_narration_paths`
     * array-cast attribute keyed by locale.
     */
    public function getAudioNarrationPathAttribute(): ?string
    {
        $locale = app()->getLocale();
        $paths = $this->audio_narration_paths ?? [];

        return $paths[$locale] ?? $paths[config('app.fallback_locale', 'en')] ?? null;
    }
}
