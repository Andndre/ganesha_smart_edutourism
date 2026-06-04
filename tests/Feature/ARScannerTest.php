<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
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
            'latitude' => -8.423,
            'longitude' => 115.359,
            'ar_marker_id' => 'marker-1',
            'model_3d_path' => 'models/test-model.glb',
            'model_3d_usdz_path' => 'models_usdz/test-model.usdz',
        ]);

        $response = $this->getJson('/api/ar/model?slug=test-cultural-object');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'name' => $object->name,
                'short_description' => 'Short desc',
                'usdz_url' => route('usdz.serve', ['path' => 'models_usdz/test-model.usdz']),
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

    public function test_serve_usdz_returns_correct_response()
    {
        Storage::disk('public')->put('models_usdz/test-model.usdz', 'dummy content');

        $response = $this->get('/usdz-file/models_usdz/test-model.usdz');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'model/vnd.usdz+zip');
        $response->assertHeader('Content-Disposition', 'inline; filename="test-model.usdz"');
        $file = $response->getFile();
        $this->assertEquals('dummy content', file_get_contents($file->getPathname()));

        Storage::disk('public')->delete('models_usdz/test-model.usdz');
    }

    public function test_serve_usdz_returns_404_for_non_existent_file()
    {
        $response = $this->get('/usdz-file/models_usdz/non-existent.usdz');

        $response->assertStatus(404);
    }

    public function test_serve_usdz_resolves_zip_stored_file()
    {
        Storage::disk('public')->put('models_usdz/test-model.zip', 'zip content');

        $response = $this->get('/usdz-file/models_usdz/test-model.zip.usdz');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'model/vnd.usdz+zip');
        $response->assertHeader('Content-Disposition', 'inline; filename="test-model.usdz"');
        $file = $response->getFile();
        $this->assertEquals('zip content', file_get_contents($file->getPathname()));

        Storage::disk('public')->delete('models_usdz/test-model.zip');
    }

    public function test_serve_usdz_repackages_compressed_zip()
    {
        $zipPath = storage_path('app/public/models_usdz/compressed.zip');
        if (! is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }
        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('model.usda', 'usda file content');
        $zip->close();

        $repackagedPath = $zipPath.'.repackaged.usdz';
        if (file_exists($repackagedPath)) {
            unlink($repackagedPath);
        }

        $response = $this->get('/usdz-file/models_usdz/compressed.zip.usdz');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'model/vnd.usdz+zip');
        $response->assertHeader('Content-Disposition', 'inline; filename="compressed.usdz"');

        $this->assertFileExists($repackagedPath);
        $repackagedZip = new \ZipArchive;
        $repackagedZip->open($repackagedPath);
        $stat = $repackagedZip->statName('model.usda');
        $this->assertEquals(\ZipArchive::CM_STORE, $stat['comp_method']);
        $this->assertEquals('usda file content', $repackagedZip->getFromName('model.usda'));
        $repackagedZip->close();

        unlink($zipPath);
        unlink($repackagedPath);
    }
}
