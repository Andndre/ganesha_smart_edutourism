<?php

namespace Tests\Unit;

use App\Http\Concerns\HandlesArFileUploads;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Verifies the shared file-replace logic now used by ARManagerController and
 * CulturalObjectController (previously duplicated 5x for audio, 4x for 3D model files).
 */
class HandlesArFileUploadsTest extends TestCase
{
    private function subject(): object
    {
        return new class
        {
            use HandlesArFileUploads;

            public function replace(UploadedFile $file, string $dir, ?string $old, ?string $filename = null): string
            {
                return $this->replaceStoredFile($file, $dir, $old, $filename);
            }

            public function replaceAudio(Request $request, string $prefix, array $existing): array
            {
                return $this->replaceLocalizedAudio($request, $prefix, $existing);
            }
        };
    }

    public function test_replace_stored_file_deletes_old_file_after_storing_new_one(): void
    {
        Storage::fake('public');
        $oldPath = UploadedFile::fake()->create('old.glb', 10)->store('models', 'public');

        $newPath = $this->subject()->replace(UploadedFile::fake()->create('new.glb', 10), 'models', $oldPath);

        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_replace_stored_file_keeps_new_file_when_no_old_path(): void
    {
        Storage::fake('public');

        $newPath = $this->subject()->replace(UploadedFile::fake()->create('new.glb', 10), 'models', null);

        Storage::disk('public')->assertExists($newPath);
    }

    public function test_replace_localized_audio_only_touches_uploaded_locales(): void
    {
        Storage::fake('public');
        $existingEnPath = UploadedFile::fake()->create('en.mp3', 10)->store('audio', 'public');
        $existingIdPath = UploadedFile::fake()->create('id.mp3', 10)->store('audio', 'public');

        $request = Request::create('/test', 'POST');
        $request->files->set('audio_narration_file', ['en' => UploadedFile::fake()->create('new-en.mp3', 10)]);

        $result = $this->subject()->replaceAudio($request, 'audio_narration_file', [
            'en' => $existingEnPath,
            'id' => $existingIdPath,
        ]);

        Storage::disk('public')->assertMissing($existingEnPath);
        Storage::disk('public')->assertExists($result['en']);
        $this->assertSame($existingIdPath, $result['id']);
        Storage::disk('public')->assertExists($existingIdPath);
    }
}
