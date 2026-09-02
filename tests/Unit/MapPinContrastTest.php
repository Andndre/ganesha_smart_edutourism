<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Map pins draw a white glyph on the category colour, so every colour has to clear
 * the WCAG 3:1 minimum for non-text contrast. Cyan-500 (2.4:1) and amber-500
 * (2.1:1) both shipped here once and were unreadable on a phone outdoors.
 */
class MapPinContrastTest extends TestCase
{
    private const PIN_SCRIPT = 'resources/views/components/map-pin-script.blade.php';

    public function test_every_category_colour_is_readable_behind_a_white_glyph(): void
    {
        $colors = $this->categoryColors();

        $this->assertNotEmpty($colors, 'No CATEGORY_COLORS block found in '.self::PIN_SCRIPT);

        foreach ($colors as $name => $hex) {
            $ratio = round($this->contrastWithWhite($hex), 2);

            $this->assertGreaterThanOrEqual(
                3.0,
                $ratio,
                "Category '{$name}' ({$hex}) is only {$ratio}:1 against a white glyph; needs 3:1. Use a darker shade."
            );
        }
    }

    public function test_every_category_colour_has_a_matching_glyph(): void
    {
        $source = file_get_contents(base_path(self::PIN_SCRIPT));
        preg_match('/const CATEGORY_GLYPHS = \{(.*?)\n        \};/s', $source, $block);

        foreach (array_keys($this->categoryColors()) as $name) {
            $this->assertStringContainsString(
                "{$name}: '<",
                $block[1] ?? '',
                "Category '{$name}' has a colour but no glyph, so its pins fall back to the cultural icon."
            );
        }
    }

    /** @return array<string, string> */
    private function categoryColors(): array
    {
        $source = file_get_contents(base_path(self::PIN_SCRIPT));
        preg_match('/const CATEGORY_COLORS = \{(.*?)\};/s', $source, $block);
        preg_match_all('/(\w+):\s*\'(#[0-9A-Fa-f]{6})\'/', $block[1] ?? '', $matches, PREG_SET_ORDER);

        return array_column($matches, 2, 1);
    }

    private function contrastWithWhite(string $hex): float
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        $luminance = 0.2126 * $this->linearise($r)
            + 0.7152 * $this->linearise($g)
            + 0.0722 * $this->linearise($b);

        // White's relative luminance is 1.0, so (1.0 + 0.05) / (L + 0.05).
        return 1.05 / ($luminance + 0.05);
    }

    private function linearise(int $channel): float
    {
        $c = $channel / 255;

        return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
    }
}
