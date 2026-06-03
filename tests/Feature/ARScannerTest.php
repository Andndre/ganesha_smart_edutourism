<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ARScannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ar_scan_page_is_accessible()
    {
        $response = $this->get('/ar-scan');

        $response->assertStatus(200);
        $response->assertViewIs('user.ar.index');
    }

    public function test_api_ar_model_returns_model_data_for_valid_slug()
    {
        $object = CulturalObject::create([
            'name' => 'Test Model',
            'slug' => 'test-cultural-object',
            'description' => 'A test description',
            'short_description' => 'Short desc',
            'category' => 'temple',
            'latitude' => -8.423,
            'longitude' => 115.359,
            'ar_marker_id' => 'marker-1',
            'model_3d_path' => 'models/test-model.glb',
        ]);

        $response = $this->getJson('/api/ar/model?slug=test-cultural-object');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'name' => $object->name,
                'short_description' => 'Short desc',
            ]);
    }

    public function test_api_ar_model_returns_404_for_invalid_slug()
    {
        $response = $this->getJson('/api/ar/model?slug=invalid-slug');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Objek tidak ditemukan',
            ]);
    }

    public function test_api_ar_model_returns_404_when_model_is_null()
    {
        $object = CulturalObject::create([
            'name' => 'No Model Object',
            'slug' => 'no-model-object',
            'description' => 'A test description',
            'short_description' => 'Short desc',
            'category' => 'temple',
            'latitude' => -8.423,
            'longitude' => 115.359,
            'ar_marker_id' => 'marker-2',
            'model_3d_path' => null,
        ]);

        $response = $this->getJson('/api/ar/model?slug=no-model-object');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Model 3D tidak tersedia untuk objek ini',
            ]);
    }
}
