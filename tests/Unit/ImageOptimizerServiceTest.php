<?php

namespace Tests\Unit;

use App\Services\ImageOptimizerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Tests\TestCase;

class ImageOptimizerServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_it_optimizes_uploaded_image_to_webp_and_downscales(): void
    {
        $optimizer = new ImageOptimizerService;

        // 2000x1500 test image
        $file = UploadedFile::fake()->image('large_photo.png', 2000, 1500);

        $path = $optimizer->optimizeAndStore(
            file: $file,
            directory: 'test_images',
            maxWidth: 1200,
            maxHeight: 1200,
            quality: 80,
            disk: 'public',
        );

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        // Inspect resulting image dimensions
        $manager = new ImageManager(new Driver);
        $image = $manager->decodeBinary(Storage::disk('public')->get($path));

        $this->assertLessThanOrEqual(1200, $image->width());
        $this->assertLessThanOrEqual(1200, $image->height());
    }

    public function test_it_optimizes_existing_file_with_backup(): void
    {
        $optimizer = new ImageOptimizerService;

        // Create an unoptimized image in storage
        $manager = new ImageManager(new Driver);
        $initialImage = $manager->createImage(1800, 1200);
        $initialPng = (string) $initialImage->encodeUsingFileExtension('png');

        Storage::disk('public')->put('existing/photo.png', $initialPng);

        $newPath = $optimizer->optimizeExistingFile(
            relativePath: 'existing/photo.png',
            maxWidth: 1000,
            maxHeight: 1000,
            quality: 80,
            disk: 'public',
            backupDirectory: 'backup_test',
            deleteOld: true,
        );

        $this->assertSame('existing/photo.webp', $newPath);
        Storage::disk('public')->assertExists('existing/photo.webp');
        Storage::disk('public')->assertExists('backup_test/existing/photo.png');
        Storage::disk('public')->assertMissing('existing/photo.png');

        // Verify dimensions downscaled
        $optimizedImage = $manager->decodeBinary(Storage::disk('public')->get($newPath));
        $this->assertLessThanOrEqual(1000, $optimizedImage->width());
        $this->assertLessThanOrEqual(1000, $optimizedImage->height());
    }

    public function test_it_skips_non_raster_or_special_assets(): void
    {
        $optimizer = new ImageOptimizerService;

        Storage::disk('public')->put('markers/pattern.patt', 'fake-patt-content');

        $result = $optimizer->optimizeExistingFile(
            relativePath: 'markers/pattern.patt',
            disk: 'public',
        );

        $this->assertSame('markers/pattern.patt', $result);
        Storage::disk('public')->assertExists('markers/pattern.patt');
    }
}
