<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TusService
{
    /**
     * Move file from tus temp to final storage.
     *
     * @param string $tempKey  e.g. "be937902-6239-4b87-a4cd-4ab561334701.glb" (uuid.extension)
     * @param string $destDir  e.g. "models" or "models_usdz"
     * @param string|null $customFilename  optional override (e.g. Str::random(40).'.usdz')
     * @return string  relative path from public disk root
     */
    public static function moveFromTemp(string $tempKey, string $destDir, ?string $customFilename = null): string
    {
        $uuid = pathinfo($tempKey, PATHINFO_FILENAME);
        $ext = pathinfo($tempKey, PATHINFO_EXTENSION);

        // Tus-php dev-main stores files with their original filename (from metadata).
        // We need the cache JSON to find the actual file path from the UUID.
        $actualPath = self::resolveTempPath($uuid);

        $finalName = $customFilename ?: Str::random(40) . '.' . $ext;
        $destPath = $destDir . '/' . $finalName;

        $storage = Storage::disk('public');
        $stream = fopen($actualPath, 'r');
        if ($stream === false) {
            throw new \RuntimeException("Failed to open temp file: {$actualPath}");
        }
        if ($storage->writeStream($destPath, $stream) === false) {
            fclose($stream);
            throw new \RuntimeException("Failed to write file to storage: {$destPath}");
        }
        fclose($stream);

        // Remove temp file
        unlink($actualPath);

        // Remove tus cache entry
        self::removeCacheEntry($uuid);

        return $destPath;
    }

    /**
     * Resolve actual temp file path from UUID via tus cache.
     */
    private static function resolveTempPath(string $uuid): string
    {
        // Look for tus cache file (stored at cache dir root)
        $cacheFilePath = storage_path('app/tus/cache/tus_php.server.cache');
        if (file_exists($cacheFilePath)) {
            $cache = json_decode(file_get_contents($cacheFilePath), true);
            $key = 'tus:server:' . $uuid;
            if (isset($cache[$key]['file_path']) && file_exists($cache[$key]['file_path'])) {
                return $cache[$key]['file_path'];
            }
        }

        // Fallback: try direct filename = UUID (for non-dev-main behavior)
        $fallback = storage_path('app/tus/temp/' . $uuid);
        if (file_exists($fallback)) {
            return $fallback;
        }

        throw new \RuntimeException("Temp file not found: {$uuid}");
    }

    /**
     * Remove tus cache entry for given UUID.
     */
    private static function removeCacheEntry(string $uuid): void
    {
        $cacheFilePath = storage_path('app/tus/cache/tus_php.server.cache');
        if (! file_exists($cacheFilePath)) {
            return;
        }

        $cache = json_decode(file_get_contents($cacheFilePath), true);
        $key = 'tus:server:' . $uuid;
        if (isset($cache[$key])) {
            unset($cache[$key]);
            file_put_contents($cacheFilePath, json_encode($cache));
        }
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

        // Load cache to know file_path for each upload
        $cacheFilePath = storage_path('app/tus/cache/tus_php.server.cache');
        $cache = file_exists($cacheFilePath) ? json_decode(file_get_contents($cacheFilePath), true) : [];

        $tempFilesOnDisk = [];
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..' || $file === '.gitkeep') {
                continue;
            }
            $tempFilesOnDisk[$file] = $dir . '/' . $file;
        }

        // Check cache entries for expiry
        $updatedCache = $cache;
        foreach ($cache as $key => $entry) {
            $expiresAt = isset($entry['expires_at']) ? strtotime($entry['expires_at']) : 0;
            if ($expiresAt > 0 && $expiresAt < time()) {
                // Expired — delete temp file if it still exists
                if (isset($entry['file_path']) && file_exists($entry['file_path'])) {
                    unlink($entry['file_path']);
                    $count++;
                }
                unset($updatedCache[$key]);
            }
        }

        // Also delete any orphan files (on disk but not in cache, old)
        foreach ($tempFilesOnDisk as $name => $path) {
            if (filemtime($path) < $expire) {
                unlink($path);
                $count++;
            }
        }

        // Write updated cache
        file_put_contents($cacheFilePath, json_encode($updatedCache));

        return $count;
    }
}
