<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Throwable;

class ImageOptimizerService
{
    public const DEFAULT_MAX_WIDTH = 1600;

    public const DEFAULT_MAX_HEIGHT = 1600;

    public const DEFAULT_QUALITY = 80;

    public const AVATAR_MAX_WIDTH = 500;

    public const AVATAR_MAX_HEIGHT = 500;

    public const AVATAR_QUALITY = 80;

    protected ImageManager $manager;

    public function __construct(?ImageManager $manager = null)
    {
        $this->manager = $manager ?? new ImageManager(new Driver);
    }

    /**
     * Optimize an uploaded image: auto-rotate EXIF, scale down, and encode as WebP.
     * Returns the relative path on the target disk.
     */
    public function optimizeAndStore(
        UploadedFile $file,
        string $directory = 'images',
        int $maxWidth = self::DEFAULT_MAX_WIDTH,
        int $maxHeight = self::DEFAULT_MAX_HEIGHT,
        int $quality = self::DEFAULT_QUALITY,
        string $disk = 'public',
        ?string $customFilename = null,
    ): string {
        try {
            $image = $this->manager->decodePath($file->getRealPath());
            $image->orient();
            $image->scaleDown(width: $maxWidth, height: $maxHeight);
            $encoded = $image->encode(new WebpEncoder(quality: $quality));

            $filename = $customFilename
                ? (pathinfo($customFilename, PATHINFO_FILENAME).'.webp')
                : (Str::random(40).'.webp');

            $targetPath = trim($directory, '/').'/'.$filename;
            Storage::disk($disk)->put($targetPath, (string) $encoded);

            return $targetPath;
        } catch (Throwable $e) {
            Log::warning('Image optimization failed, falling back to direct store: '.$e->getMessage());

            return $file->store($directory, $disk);
        }
    }

    /**
     * Optimize an existing image file on disk.
     * Optionally backs up original file before converting.
     * Returns the new relative path (or null on failure).
     */
    public function optimizeExistingFile(
        string $relativePath,
        int $maxWidth = self::DEFAULT_MAX_WIDTH,
        int $maxHeight = self::DEFAULT_MAX_HEIGHT,
        int $quality = self::DEFAULT_QUALITY,
        string $disk = 'public',
        ?string $backupDirectory = null,
        bool $deleteOld = true,
    ): ?string {
        if (! Storage::disk($disk)->exists($relativePath)) {
            return null;
        }

        $fullPath = Storage::disk($disk)->path($relativePath);
        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        // Skip non-raster assets
        if (in_array($extension, ['patt', 'glb', 'usdz', 'mp3', 'wav', 'ogg', 'mp4', 'pdf', 'txt', 'json'])) {
            return $relativePath;
        }

        try {
            $image = $this->manager->decodePath($fullPath);
            $image->orient();
            $image->scaleDown(width: $maxWidth, height: $maxHeight);
            $encoded = $image->encode(new WebpEncoder(quality: $quality));

            // Backup original file if requested
            if ($backupDirectory) {
                $backupPath = trim($backupDirectory, '/').'/'.$relativePath;
                Storage::disk($disk)->copy($relativePath, $backupPath);
            }

            $dirname = pathinfo($relativePath, PATHINFO_DIRNAME);
            $filename = pathinfo($relativePath, PATHINFO_FILENAME);
            $newRelativePath = ($dirname === '.' ? '' : $dirname.'/').$filename.'.webp';

            Storage::disk($disk)->put($newRelativePath, (string) $encoded);

            // If path changed and deleteOld is true, delete old file
            if ($deleteOld && $newRelativePath !== $relativePath) {
                Storage::disk($disk)->delete($relativePath);
            }

            return $newRelativePath;
        } catch (Throwable $e) {
            Log::error('Failed to optimize existing image ('.$relativePath.'): '.$e->getMessage());

            return null;
        }
    }
}
