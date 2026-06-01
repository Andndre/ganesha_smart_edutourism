<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\CulturalStory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CulturalPublicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test public cultural objects index page renders successfully.
     */
    public function test_public_cultural_objects_index_page_renders_successfully(): void
    {
        // Arrange
        CulturalObject::create([
            'name' => 'Pura Penataran Agung',
            'slug' => 'pura-penataran-agung',
            'description' => 'Pura utama di desa Penglipuran.',
            'category' => 'temple',
            'ar_marker_id' => 'marker_pura_penataran',
        ]);

        CulturalObject::create([
            'name' => 'Pura Dadia Penarukan',
            'slug' => 'pura-dadia-penarukan',
            'description' => 'Pura keluarga leluhur Penarukan.',
            'category' => 'temple',
            'ar_marker_id' => 'marker_dadia_penarukan',
        ]);

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
        // Arrange
        $object = CulturalObject::create([
            'name' => 'Pura Penataran Agung',
            'slug' => 'pura-penataran-agung',
            'description' => 'Pura utama di desa Penglipuran.',
            'category' => 'temple',
            'ar_marker_id' => 'marker_pura_penataran',
            'model_3d_path' => 'models/pura.glb',
        ]);

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
        $response->assertSee('Asal Usul Pura Penataran');
        $response->assertSee('Kisah pendirian pura penataran agung oleh leluhur.');
        $response->assertSee('Jelajahi dalam Mode AR');
    }

    /**
     * Test public cultural object detail page does not render AR button if not available.
     */
    public function test_public_cultural_object_detail_page_does_not_render_ar_button_if_not_available(): void
    {
        // Arrange
        $object = CulturalObject::create([
            'name' => 'Hutan Bambu Penglipuran',
            'slug' => 'hutan-bambu-penglipuran',
            'description' => 'Hutan bambu pelindung desa.',
            'category' => 'tradition',
            'ar_marker_id' => '', // Falsy empty string to simulate no AR marker in the view
            'model_3d_path' => null,
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
