<?php

namespace Tests\Unit;

use App\Models\ArModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies the shared audio_narration_path accessor (used by ArModel and CulturalObject).
 */
class HasLocalizedAudioNarrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_current_locale_path(): void
    {
        $model = new ArModel(['audio_narration_paths' => ['en' => 'en.mp3', 'id' => 'id.mp3']]);

        app()->setLocale('id');

        $this->assertSame('id.mp3', $model->audio_narration_path);
    }

    public function test_falls_back_to_fallback_locale_when_missing(): void
    {
        $model = new ArModel(['audio_narration_paths' => ['en' => 'en.mp3']]);

        app()->setLocale('id');
        config(['app.fallback_locale' => 'en']);

        $this->assertSame('en.mp3', $model->audio_narration_path);
    }

    public function test_returns_null_when_no_paths(): void
    {
        $model = new ArModel(['audio_narration_paths' => []]);

        $this->assertNull($model->audio_narration_path);
    }
}
