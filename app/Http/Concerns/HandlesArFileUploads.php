<?php

namespace App\Http\Concerns;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesArFileUploads
{
    /**
     * Store a replacement file, then delete the old one (store-before-delete
     * avoids losing the old file if the new upload fails).
     */
    protected function replaceStoredFile(UploadedFile $file, string $directory, ?string $oldPath, ?string $filename = null): string
    {
        $newPath = $filename
            ? $file->storeAs($directory, $filename, 'public')
            : $file->store($directory, 'public');

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $newPath;
    }

    /**
     * Merge newly uploaded per-locale (en/id) audio files into $existingPaths,
     * replacing (and deleting) any prior file for a locale that was re-uploaded.
     */
    protected function replaceLocalizedAudio(Request $request, string $fileKeyPrefix, array $existingPaths): array
    {
        foreach (['en', 'id'] as $locale) {
            $fileKey = "{$fileKeyPrefix}.{$locale}";
            if ($request->hasFile($fileKey)) {
                $existingPaths[$locale] = $this->replaceStoredFile(
                    $request->file($fileKey),
                    'audio',
                    $existingPaths[$locale] ?? null,
                );
            }
        }

        return $existingPaths;
    }
}
