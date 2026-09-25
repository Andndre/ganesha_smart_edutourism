<?php

namespace Tests\Unit;

use Tests\TestCase;

class TranslateValueTest extends TestCase
{
    public function test_returns_empty_string_for_null_or_empty(): void
    {
        $this->assertSame('', translateValue(null));
        $this->assertSame('', translateValue(''));
        $this->assertSame('', translateValue([]));
    }

    public function test_returns_plain_string_if_not_json(): void
    {
        $this->assertSame('Simple String', translateValue('Simple String'));
    }

    public function test_decodes_json_string_properly(): void
    {
        app()->setLocale('id');
        $json = json_encode(['en' => 'Temple', 'id' => 'Pura']);

        $this->assertSame('Pura', translateValue($json));
    }

    public function test_resolves_requested_locale(): void
    {
        $data = ['en' => 'Temple', 'id' => 'Pura'];

        $this->assertSame('Pura', translateValue($data, 'id'));
        $this->assertSame('Temple', translateValue($data, 'en'));
    }

    public function test_falls_back_when_requested_locale_is_missing(): void
    {
        config(['app.fallback_locale' => 'id']);
        $data = ['id' => 'Pura'];

        $this->assertSame('Pura', translateValue($data, 'en'));
    }

    public function test_falls_back_when_requested_locale_is_empty_string(): void
    {
        config(['app.fallback_locale' => 'id']);
        $data = ['en' => '', 'id' => 'Pura'];

        // If active locale is 'en', empty string should not be returned; it must fall back to 'id'
        $this->assertSame('Pura', translateValue($data, 'en'));
    }

    public function test_falls_back_when_requested_locale_is_whitespace_only(): void
    {
        config(['app.fallback_locale' => 'id']);
        $data = ['en' => "   \n\t  ", 'id' => 'Pura'];

        $this->assertSame('Pura', translateValue($data, 'en'));
    }

    public function test_falls_back_to_en_when_fallback_locale_also_empty(): void
    {
        config(['app.fallback_locale' => 'id']);
        $data = ['es' => '   ', 'id' => '', 'en' => 'English Fallback'];

        $this->assertSame('English Fallback', translateValue($data, 'es'));
    }

    public function test_falls_back_to_first_non_empty_scalar(): void
    {
        config(['app.fallback_locale' => 'id']);
        $data = ['es' => '', 'id' => '', 'en' => '', 'fr' => 'Bonjour'];

        $this->assertSame('Bonjour', translateValue($data, 'es'));
    }

    public function test_returns_empty_string_when_all_values_are_empty_or_whitespace(): void
    {
        $data = ['en' => '', 'id' => '   ', 'fr' => "\t"];

        $this->assertSame('', translateValue($data, 'en'));
    }
}
