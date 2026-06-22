<?php

if (! function_exists('slugFromTranslatable')) {
    /**
     * Extract a slug-safe string from a translatable array field.
     * Falls back through: fallback locale → 'en' → first available value.
     */
    function slugFromTranslatable(array $translations): string
    {
        $locale = config('app.fallback_locale', 'en');

        return $translations[$locale] ?? $translations['en'] ?? reset($translations) ?? '';
    }
}
