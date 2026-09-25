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
     * Falls back through: fallback locale → 'en' → first available non-empty value.
     */
    function slugFromTranslatable(array $translations): string
    {
        $fallback = config('app.fallback_locale', 'en');

        foreach ([$fallback, 'en'] as $l) {
            if (isset($translations[$l]) && is_scalar($translations[$l]) && trim((string) $translations[$l]) !== '') {
                return (string) $translations[$l];
            }
        }

        foreach ($translations as $val) {
            if (is_scalar($val) && trim((string) $val) !== '') {
                return (string) $val;
            }
        }

        return '';
    }
}

if (! function_exists('translateValue')) {
    /**
     * Get the translated string from a value (which can be a JSON string, array, or plain string).
     * Robustly skips empty and whitespace-only strings during fallback traversal.
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

        // Traverse priority candidates: requested locale → fallback locale → 'en'
        foreach ([$locale, $fallback, 'en'] as $l) {
            if (isset($value[$l]) && is_scalar($value[$l]) && trim((string) $value[$l]) !== '') {
                return (string) $value[$l];
            }
        }

        // Fall back to first non-empty scalar value
        foreach ($value as $val) {
            if (is_scalar($val) && trim((string) $val) !== '') {
                return (string) $val;
            }
        }

        return '';
    }
}
