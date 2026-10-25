<?php

namespace Tests\Feature;

use App\Models\ArModel;
use App\Models\CulturalObject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Tests\TestCase;

class OptimizeExistingImagesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_dry_run_does_not_modify_files_or_database(): void
    {
        $manager = new ImageManager(new Driver);
        $initialImage = $manager->createImage(400, 300);
        $initialPng = (string) $initialImage->encodeUsingFileExtension('png');

        Storage::disk('public')->put('images/dryrun.png', $initialPng);

        $object = CulturalObject::create([
            'name' => ['en' => 'Test Object', 'id' => 'Objek Uji'],
            'description' => ['en' => 'Test Description', 'id' => 'Deskripsi Uji'],
            'category' => 'architecture',
            'slug' => 'test-object',
            'historical_images' => ['images/dryrun.png'],
        ]);

        $this->artisan('images:optimize-existing --dry-run')
            ->assertSuccessful();

        Storage::disk('public')->assertExists('images/dryrun.png');
        Storage::disk('public')->assertMissing('images/dryrun.webp');

        $this->assertSame(['images/dryrun.png'], $object->fresh()->historical_images);
    }

    public function test_it_converts_images_and_updates_database(): void
    {
        $manager = new ImageManager(new Driver);
        $initialImage = $manager->createImage(400, 300);
        $initialPng = (string) $initialImage->encodeUsingFileExtension('png');

        Storage::disk('public')->put('images/real.png', $initialPng);

        $object = CulturalObject::create([
            'name' => ['en' => 'Real Object', 'id' => 'Objek Asli'],
            'description' => ['en' => 'Real Description', 'id' => 'Deskripsi Asli'],
            'category' => 'architecture',
            'slug' => 'real-object',
            'historical_images' => ['images/real.png'],
        ]);

        $this->artisan('images:optimize-existing --force')
            ->assertSuccessful();

        Storage::disk('public')->assertExists('images/real.webp');
        Storage::disk('public')->assertMissing('images/real.png');

        $this->assertSame(['images/real.webp'], $object->fresh()->historical_images);
    }

    public function test_it_converts_ar_model_thumbnail_and_updates_database(): void
    {
        $manager = new ImageManager(new Driver);
        $initialImage = $manager->createImage(200, 200);
        $initialPng = (string) $initialImage->encodeUsingFileExtension('png');

        Storage::disk('public')->put('thumbnails/1-123.png', $initialPng);

        $arModel = ArModel::create([
            'name' => ['en' => 'Test 3D Model', 'id' => 'Model 3D Uji'],
            'model_3d_path' => 'models/test.glb',
            'thumbnail_path' => 'thumbnails/1-123.png',
        ]);

        $this->artisan('images:optimize-existing --force')
            ->assertSuccessful();

        Storage::disk('public')->assertExists('thumbnails/1-123.webp');
        Storage::disk('public')->assertMissing('thumbnails/1-123.png');

        $this->assertSame('thumbnails/1-123.webp', $arModel->fresh()->thumbnail_path);
    }

    public function test_it_heals_dangling_database_references_when_file_was_already_converted(): void
    {
        // Simulate scenario where .webp exists on disk, but database still points to old .png
        Storage::disk('public')->put('thumbnails/existing.webp', 'fake-webp-content');

        $arModel = ArModel::create([
            'name' => ['en' => 'Dangling Model', 'id' => 'Model Menggantung'],
            'model_3d_path' => 'models/test.glb',
            'thumbnail_path' => 'thumbnails/existing.png',
        ]);

        // Run command: no JPG/PNG exist to convert, so healing should kick in
        $this->artisan('images:optimize-existing --force')
            ->assertSuccessful();

        $this->assertSame('thumbnails/existing.webp', $arModel->fresh()->thumbnail_path);
    }
}
