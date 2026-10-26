<?php

namespace Tests\Unit;

use App\Models\RouteMission;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RouteMissionAssetResolutionTest extends TestCase
{
    public function test_resolve_asset_url_resolves_webp_fallback(): void
    {
        Storage::fake('public');

        // Create a webp file on disk
        Storage::disk('public')->put('mission_assets/sample.webp', 'fake webp content');

        // Original JPG does not exist, but URL references JPG
        $jpgUrl = 'https://penglipuran.digowave.com/storage/mission_assets/sample.jpg';
        $resolved = RouteMission::resolveAssetUrl($jpgUrl);

        $this->assertStringContainsString('sample.webp', $resolved);
    }

    public function test_resolve_asset_url_resolves_relative_path(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('mission_assets/sample.webp', 'fake webp content');

        $resolved = RouteMission::resolveAssetUrl('mission_assets/sample.png');

        $this->assertStringContainsString('sample.webp', $resolved);
    }

    public function test_localized_config_resolves_nested_mission_images(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('mission_assets/puzzle.webp', 'fake webp content');

        $mission = new RouteMission([
            'type' => 'puzzle',
            'title' => ['id' => 'Susun Foto', 'en' => 'Photo Puzzle'],
            'config' => [
                'prompt' => ['id' => 'Susun gambar', 'en' => 'Assemble image'],
                'image' => 'https://penglipuran.digowave.com/storage/mission_assets/puzzle.jpg',
                'grid_size' => 3,
            ],
        ]);

        $localized = $mission->localizedConfig();

        $this->assertEquals('Susun gambar', $localized['prompt']);
        $this->assertStringContainsString('puzzle.webp', $localized['image']);
    }
}
