<?php

namespace App\Http\Concerns;

use App\Services\TusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait HandlesFileUploads
{
    /**
     * Handle a single file field: TUS temp upload or direct file, with optional old-file cleanup.
     * Returns the new storage path or null if no upload.
     */
    protected function handleFileUpload(
        Request $request,
        string $inputName,
        string $directory = 'uploads',
        ?string $oldPath = null,
        ?string $customFilename = null,
        string $disk = 'public',
    ): ?string {
        // Check TUS temp upload first
        $tusKey = 'tmp_'.$inputName.'_path';
        if ($tmpUuid = $request->input($tusKey)) {
            if ($oldPath) {
                Storage::disk($disk)->delete($oldPath);
            }

            return TusService::moveFromTemp($tmpUuid, $directory, $customFilename);
        }

        // Fallback: direct file upload
        if ($request->hasFile($inputName)) {
            if ($oldPath) {
                Storage::disk($disk)->delete($oldPath);
            }

            return $customFilename
                ? $request->file($inputName)->storeAs($directory, $customFilename, $disk)
                : $request->file($inputName)->store($directory, $disk);
        }

        return null;
    }

    /**
     * Handle multiple file uploads (e.g. images array).
     * Returns array of storage paths or null if no upload.
     */
    protected function handleUploadedImages(
        Request $request,
        string $field = 'images',
        string $directory = 'images',
        string $disk = 'public',
    ): ?array {
        if (! $request->hasFile($field)) {
            return null;
        }

        $paths = [];
        foreach ($request->file($field) as $file) {
            $paths[] = $file->store($directory, $disk);
        }

        return $paths;
    }
}
