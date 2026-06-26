<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TusService
{
    /**
     * Move file from tus temp to final storage.
     *
     * @param string $tempKey  e.g. "abc123.glb" (uuid.extension)
     * @param string $destDir  e.g. "models" or "models_usdz"
     * @param string|null $customFilename  optional override (e.g. Str::random(40).'.usdz')
     * @return string  relative path from public disk root
     */
    public static function moveFromTemp(string $tempKey, string $destDir, ?string $customFilename = null): string
    {
        $uuid = pathinfo($tempKey, PATHINFO_FILENAME);
        $ext = pathinfo($tempKey, PATHINFO_EXTENSION);

        $tempPath = storage_path('app/tus/temp/' . $uuid);
        if (! file_exists($tempPath)) {
            throw new \RuntimeException("Temp file not found: {$uuid}");
        }

        $finalName = $customFilename ?: Str::random(40) . '.' . $ext;
        $destPath = $destDir . '/' . $finalName;

        $storage = Storage::disk('public');
        $stream = fopen($tempPath, 'r');
        if ($stream === false) {
            throw new \RuntimeException("Failed to open temp file: {$tempPath}");
        }
        if ($storage->writeStream($destPath, $stream) === false) {
            fclose($stream);
            throw new \RuntimeException("Failed to write file to storage: {$destPath}");
        }
        fclose($stream);

        // Remove temp file
        unlink($tempPath);

        // Remove tus cache entry if exists
        $cacheFile = storage_path('app/tus/cache/' . $uuid);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        return $destPath;
    }

    /**
     * Get temp upload dir.
     */
    public static function tempDir(): string
    {
        return storage_path('app/tus/temp');
    }

    /**
     * Clean up temp files older than $hours.
     */
    public static function cleanTemp(int $hours = 24): int
    {
        $count = 0;
        $dir = self::tempDir();
        if (! is_dir($dir)) {
            return 0;
        }

        $expire = now()->subHours($hours)->timestamp;

        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir . '/' . $file;
            if (filemtime($path) < $expire) {
                unlink($path);
                $count++;
            }
        }

        // Clean cache entries too
        $cacheDir = storage_path('app/tus/cache');
        if (is_dir($cacheDir)) {
            foreach (scandir($cacheDir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $cacheDir . '/' . $file;
                if (filemtime($path) < $expire) {
                    unlink($path);
                }
            }
        }

        return $count;
    }
}
