<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Verifies the shared slugFromTranslatable() helper (app/helpers.php), now used by
 * 15+ controller call sites that previously reimplemented this locale fallback inline.
 */
class SlugFromTranslatableTest extends TestCase
{
    public function test_prefers_fallback_locale(): void
    {
        config(['app.fallback_locale' => 'id']);

        $this->assertSame('Pura', slugFromTranslatable(['en' => 'Temple', 'id' => 'Pura']));
    }

    public function test_falls_back_to_en_when_fallback_locale_missing(): void
    {
        config(['app.fallback_locale' => 'id']);

        $this->assertSame('Temple', slugFromTranslatable(['en' => 'Temple', 'fr' => 'Temple FR']));
    }

    public function test_falls_back_to_first_value_when_no_locale_matches(): void
    {
        config(['app.fallback_locale' => 'id']);

        $this->assertSame('Temple FR', slugFromTranslatable(['fr' => 'Temple FR']));
    }
}
