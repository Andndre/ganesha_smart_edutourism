<?php

namespace Tests\Feature;

use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Models\CulturalStory;
use App\Models\MapLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CulturalPublicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a CulturalObject with a full AR setup (MapLocation + ArMarker + ArModel).
     *
     * @param  array<string, mixed>  $objectData
     */
    private function createCulturalObjectWithAr(array $objectData, string $markerId = 'MARKER_TEST'): CulturalObject
    {
        $object = CulturalObject::create($objectData);

        $location = MapLocation::create([
            'locationable_type' => CulturalObject::class,
            'locationable_id' => $object->id,
            'name' => $object->name,
            'latitude' => -8.4223,
            'longitude' => 115.3839,
            'category' => 'cultural',
        ]);

        ArModel::create([
            'name' => $object->name.' 3D Model',
            'model_3d_path' => 'models/test.glb',
            'ar_marker_id' => $markerId,
            'map_location_id' => $location->id,
        ]);

        return $object;
    }

    /**
     * Test public cultural objects index page renders successfully.
     */
    public function test_public_cultural_objects_index_page_renders_successfully(): void
    {
        // Arrange — create objects with full AR setup via normalized schema
        $this->createCulturalObjectWithAr([
            'name' => 'Pura Penataran Agung',
            'slug' => 'pura-penataran-agung',
            'short_description' => 'Jantung Spiritual Desa Penglipuran',
            'description' => 'Pura utama di desa Penglipuran.',
            'category' => 'temple',
        ], 'marker_pura_penataran');

        $this->createCulturalObjectWithAr([
            'name' => 'Pura Dadia Penarukan',
            'slug' => 'pura-dadia-penarukan',
            'description' => 'Pura keluarga leluhur Penarukan.',
            'category' => 'temple',
        ], 'marker_dadia_penarukan');

        // Act
        $response = $this->get(route('cultural-objects'));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Pura Penataran Agung');
        $response->assertSee('Pura Dadia Penarukan');
        $response->assertSee('AR Tersedia');
    }

    /**
     * Test public cultural object detail page renders successfully with stories.
     */
    public function test_public_cultural_object_detail_page_renders_successfully_with_stories(): void
    {
        // Arrange — create object with full AR setup
        $object = $this->createCulturalObjectWithAr([
            'name' => 'Pura Penataran Agung',
            'slug' => 'pura-penataran-agung',
            'short_description' => 'Jantung Spiritual Desa Penglipuran',
            'description' => 'Pura utama di desa Penglipuran.',
            'category' => 'temple',
        ], 'marker_pura_penataran');

        CulturalStory::create([
            'cultural_object_id' => $object->id,
            'title' => 'Asal Usul Pura Penataran',
            'content' => 'Kisah pendirian pura penataran agung oleh leluhur.',
            'story_type' => 'history',
            'order' => 1,
        ]);

        // Act
        $response = $this->get(route('cultural-object', ['slug' => $object->slug]));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Pura Penataran Agung');
        $response->assertSee('Jantung Spiritual Desa Penglipuran');
        $response->assertSee('Asal Usul Pura Penataran');
        $response->assertSee('Kisah pendirian pura penataran agung oleh leluhur.');
        $response->assertSee('Jelajahi dalam Mode AR');
    }

    /**
     * Test public cultural object detail page does not render AR button if not available.
     */
    public function test_public_cultural_object_detail_page_does_not_render_ar_button_if_not_available(): void
    {
        // Arrange — create object WITHOUT AR (no MapLocation, no ArMarker)
        $object = CulturalObject::create([
            'name' => 'Hutan Bambu Penglipuran',
            'slug' => 'hutan-bambu-penglipuran',
            'description' => 'Hutan bambu pelindung desa.',
            'category' => 'tradition',
        ]);

        // Act
        $response = $this->get(route('cultural-object', ['slug' => $object->slug]));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Hutan Bambu Penglipuran');
        $response->assertDontSee('Jelajahi dalam Mode AR');
    }

    /**
     * Test public cultural object detail page returns 404 if not found.
     */
    public function test_public_cultural_object_detail_page_returns_404_if_not_found(): void
    {
        // Act
        $response = $this->get(route('cultural-object', ['slug' => 'non-existent-slug']));

        // Assert
        $response->assertStatus(404);
    }
}
