<?php

namespace Tests\Feature;

use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
        ]);

        $mapLocation = $object->mapLocation()->create([
            'name' => 'Test Model Location',
            'category' => 'cultural',
            'latitude' => -8.423,
            'longitude' => 115.359,
        ]);

        ArModel::create([
            'name' => 'Test Model asset',
            'model_3d_path' => 'models/test-model.glb',
            'model_3d_usdz_path' => 'models_usdz/test-model.usdz',
            'ar_marker_id' => 'marker-1',
            'map_location_id' => $mapLocation->id,
        ]);

        $response = $this->getJson('/api/ar/model?slug=test-cultural-object');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'name' => $object->name,
                'short_description' => 'Short desc',
                'usdz_url' => route('usdz.serve', ['path' => 'test-model.usdz']),
            ]);
    }

    public function test_api_ar_model_returns_404_for_invalid_slug()
    {
        $response = $this->getJson('/api/ar/model?slug=invalid-slug');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Model 3D tidak tersedia untuk objek ini',
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
        ]);

        $object->mapLocation()->create([
            'name' => 'No Model Object Location',
            'category' => 'cultural',
            'latitude' => -8.423,
            'longitude' => 115.359,
        ]);

        $response = $this->getJson('/api/ar/model?slug=no-model-object');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Model 3D tidak tersedia untuk objek ini',
            ]);
    }

    public function test_api_ar_model_returns_model_data_for_valid_id()
    {
        $model = ArModel::create([
            'name' => 'Model By ID',
            'model_3d_path' => 'models/by-id.glb',
            'ar_marker_id' => 'MARKER_BY_ID',
        ]);

        $response = $this->getJson('/api/ar/model?id='.$model->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'name' => 'Model By ID',
            ]);
    }

    public function test_api_ar_model_returns_404_for_invalid_id()
    {
        $response = $this->getJson('/api/ar/model?id=99999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Model 3D tidak tersedia untuk objek ini',
            ]);
    }

    public function test_serve_usdz_returns_correct_response()
    {
        Storage::disk('public')->put('models_usdz/test-model.usdz', 'dummy content');

        $response = $this->get('/usdz-file/test-model.usdz');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'model/vnd.usdz+zip');
        $response->assertHeader('Content-Disposition', 'inline; filename="test-model.usdz"');
        $file = $response->getFile();
        $this->assertEquals('dummy content', file_get_contents($file->getPathname()));

        Storage::disk('public')->delete('models_usdz/test-model.usdz');
    }

    public function test_serve_usdz_returns_404_for_non_existent_file()
    {
        $response = $this->get('/usdz-file/non-existent.usdz');

        $response->assertStatus(404);
    }

    public function test_serve_usdz_resolves_zip_stored_file()
    {
        Storage::disk('public')->put('models_usdz/test-model.zip', 'zip content');

        $response = $this->get('/usdz-file/test-model.zip.usdz');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'model/vnd.usdz+zip');
        $response->assertHeader('Content-Disposition', 'inline; filename="test-model.usdz"');
        $file = $response->getFile();
        $this->assertEquals('zip content', file_get_contents($file->getPathname()));

        Storage::disk('public')->delete('models_usdz/test-model.zip');
    }

    public function test_ar_scan_redirects_to_cultural_object()
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Test Temple', 'id' => 'Candi Test'],
            'slug' => 'test-temple',
            'description' => ['en' => 'A test', 'id' => 'Test'],
            'short_description' => ['en' => 'Short', 'id' => 'Pendek'],
            'category' => 'temple',
        ]);

        $mapLocation = $object->mapLocation()->create([
            'name' => 'Test Location',
            'category' => 'cultural',
            'latitude' => -8.423,
            'longitude' => 115.359,
        ]);

        ArModel::create([
            'name' => ['en' => 'Test Model', 'id' => 'Model Test'],
            'model_3d_path' => 'models/test.glb',
            'ar_marker_id' => 'MARKER_TEMPLE_01',
            'map_location_id' => $mapLocation->id,
        ]);

        $response = $this->get('/ar/scan/MARKER_TEMPLE_01');
        $response->assertRedirect('/cultural/test-temple');
    }

    public function test_ar_scan_redirects_to_viewer_for_no_map_location()
    {
        $model = ArModel::create([
            'name' => ['en' => 'Standalone Model', 'id' => 'Model Mandiri'],
            'model_3d_path' => 'models/standalone.glb',
            'ar_marker_id' => 'MARKER_STANDALONE',
        ]);

        $response = $this->get('/ar/scan/MARKER_STANDALONE');
        $response->assertRedirect(route('ar-viewer', ['arMarkerId' => $model->ar_marker_id]));
    }

    public function test_ar_scan_returns_404_for_invalid_marker()
    {
        $response = $this->get('/ar/scan/DOES_NOT_EXIST');
        $response->assertNotFound();
    }

    public function test_ar_viewer_redirects_to_ar_scan_with_model_id()
    {
        $model = ArModel::create([
            'name' => ['en' => 'Viewer Model', 'id' => 'Model Viewer'],
            'model_3d_path' => 'models/viewer.glb',
            'ar_marker_id' => 'MARKER_VIEWER',
        ]);

        $response = $this->get(route('ar-viewer', ['arMarkerId' => $model->ar_marker_id]));
        $response->assertRedirect(route('ar-scan', ['marker' => $model->ar_marker_id]));
    }

    public function test_ar_viewer_returns_404_for_invalid_marker()
    {
        $response = $this->get('/ar/viewer/DOES_NOT_EXIST');
        $response->assertNotFound();
    }

    public function test_ar_scan_redirects_to_viewer_for_non_cultural_locationable()
    {
        $facility = Facility::create([
            'name' => 'Test Facility',
            'type' => 'toilet',
            'description' => 'A test facility',
        ]);

        $mapLocation = \App\Models\MapLocation::create([
            'name' => 'Facility Location',
            'category' => 'facility',
            'latitude' => -8.424,
            'longitude' => 115.360,
            'locationable_type' => \App\Models\Facility::class,
            'locationable_id' => $facility->id,
        ]);

        $model = ArModel::create([
            'name' => ['en' => 'Facility Model', 'id' => 'Model Fasilitas'],
            'model_3d_path' => 'models/facility.glb',
            'ar_marker_id' => 'MARKER_FACILITY_01',
            'map_location_id' => $mapLocation->id,
        ]);

        $response = $this->get('/ar/scan/MARKER_FACILITY_01');
        $response->assertRedirect(route('ar-viewer', ['arMarkerId' => $model->ar_marker_id]));
    }

    public function test_ar_scan_redirects_to_viewer_for_orphaned_map_location()
    {
        $mapLocation = \App\Models\MapLocation::create([
            'name' => 'Orphaned Location',
            'category' => 'cultural',
            'latitude' => -8.425,
            'longitude' => 115.361,
            'locationable_type' => \App\Models\CulturalObject::class,
            'locationable_id' => 99999, // Doesn't exist
        ]);

        $model = ArModel::create([
            'name' => ['en' => 'Orphan Model', 'id' => 'Model Yatim'],
            'model_3d_path' => 'models/orphan.glb',
            'ar_marker_id' => 'MARKER_ORPHAN_01',
            'map_location_id' => $mapLocation->id,
        ]);

        $response = $this->get('/ar/scan/MARKER_ORPHAN_01');
        $response->assertRedirect(route('ar-viewer', ['arMarkerId' => $model->ar_marker_id]));
    }
}
