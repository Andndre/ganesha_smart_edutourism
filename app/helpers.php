<?php

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

if (! function_exists('qrSvgDataUri')) {
    /**
     * Generate a QR code SVG data URI for inline use in <img> tags.
     */
    function qrSvgDataUri(string $data, int $size = 250): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($data);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}

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

if (! function_exists('valueOrMock')) {
    /**
     * Return $real unless it's falsy (0, 0.0, null, empty array), in which case
     * return $mock. Used for dashboard/report stats that fall back to demo
     * numbers when the database has no data yet.
     */
    function valueOrMock(int|float|null $real, int|float $mock): int|float
    {
        return empty($real) ? $mock : $real;
    }
}

if (! function_exists('translateValue')) {
    /**
     * Get the translated string from a value (which can be a JSON string, array, or plain string).
     */
    function translateValue(array|string|null $value, ?string $locale = null): string
    {
        if (empty($value)) {
            return '';
        }

        if (is_string($value)) {
            // Check if it's a JSON string (sometimes stored raw in SQLite/MySQL)
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && \is_array($decoded)) {
                $value = $decoded;
            } else {
                return $value;
            }
        }

        if (! \is_array($value)) {
            return '';
        }

        $locale = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale', 'en');

        return $value[$locale] ?? $value[$fallback] ?? $value['en'] ?? reset($value) ?? '';
    }
}
