<?php

namespace Tests\Feature;

use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Models\TourRoutePoint;
use App\Models\UmkmProfile;
use App\Models\User;
use App\Services\ImageOptimizerService;
use Database\Seeders\Route1HeritageQuestSeeder;
use Database\Seeders\Route2CulturalAdventureSeeder;
use Database\Seeders\Route3EcoQuestSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class CulturalObjectArAssetsTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['create'])]
    #[TestWith(['update'])]
    public function test_umkm_profiles_reject_legacy_markers_without_creating_orphan_models(string $operation): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $profile = $operation === 'update' ? UmkmProfile::factory()->create() : null;
        $data = [
            'business_name' => ['id' => 'Warung Baru', 'en' => 'New Shop'],
            'owner_name' => 'Wayan', 'latitude' => -8.42, 'longitude' => 115.35,
            'ar_marker_id' => 'LEGACY-UMKM-MARKER',
        ];

        $this->actingAs($admin);
        $response = $profile
            ? $this->putJson(route('admin.umkm.profile.update', $profile->id), $data)
            : $this->postJson(route('admin.umkm.profile.store'), $data);

        $response->assertUnprocessable()->assertJsonValidationErrors('ar_marker_id')
            ->assertJsonPath('errors.ar_marker_id.0', 'Marker 3D/AR dikelola melalui objek budaya.');
        $this->assertDatabaseCount('ar_models', 0);
        $this->assertDatabaseCount('umkm_profiles', $profile ? 1 : 0);
        if ($profile) {
            $this->assertSame($profile->getTranslations('business_name'), $profile->fresh()->getTranslations('business_name'));
        }
    }

    #[TestWith([Route1HeritageQuestSeeder::class])]
    #[TestWith([Route2CulturalAdventureSeeder::class])]
    #[TestWith([Route3EcoQuestSeeder::class])]
    public function test_route_seeders_use_the_marker_owned_by_the_cultural_object(string $seeder): void
    {
        $object = CulturalObject::factory()->create([
            'name' => ['id' => 'Gerbang Desa Penglipuran', 'en' => 'Penglipuran Village Gate'],
        ]);
        $model = ArModel::factory()->create(['cultural_object_id' => $object->id, 'ar_marker_id' => 'VILLAGE-GATE']);

        $this->seed($seeder);

        $point = TourRoutePoint::where('locationable_type', $object->getMorphClass())
            ->where('locationable_id', $object->id)->firstOrFail();
        $this->assertNull($point->qr_code_token);
        $this->assertTrue(TourRoutePoint::whereNotNull('qr_code_token')->exists());
        $this->assertSame('VILLAGE-GATE', $model->fresh()->ar_marker_id);
    }

    public function test_object_can_be_created_without_a_model(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.cultural-objects.store'), [
            'name' => ['id' => 'Paon', 'en' => 'Kitchen'],
            'category' => 'pawongan',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('cultural_objects', ['slug' => 'kitchen']);
        $this->assertDatabaseCount('ar_models', 0);
    }

    public function test_object_and_model_are_saved_together_without_duplicate_content(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.cultural-objects.store'), [
            'name' => ['id' => 'Paon', 'en' => 'Kitchen'],
            'description' => ['id' => 'Dapur tradisional', 'en' => 'Traditional kitchen'],
            'category' => 'pawongan',
            'model_3d_file' => UploadedFile::fake()->create('paon.glb', 10),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $object = CulturalObject::where('slug', 'kitchen')->firstOrFail();
        $model = $object->arModel;
        $this->assertNotNull($model);
        Storage::disk('public')->assertExists($model->model_3d_path);
        $this->assertFalse(Schema::hasColumn('ar_models', 'name'));
        $this->assertFalse(Schema::hasColumn('ar_models', 'audio_narration_paths'));
    }

    public function test_standalone_model_creation_endpoint_is_no_longer_available(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->postJson('/admin/ar-manager/models', [])->assertNotFound();
        $this->assertDatabaseCount('ar_models', 0);
    }

    public function test_admin_can_add_a_model_from_the_existing_cultural_object_form(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $object = CulturalObject::factory()->create();
        $this->actingAs($admin)->put(route('admin.cultural-objects.update', $object->id), [
            'name' => $object->getTranslations('name'),
            'category' => $object->category,
            'model_3d_file' => UploadedFile::fake()->create('paon.glb', 10),
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($object->id, $object->fresh()->arModel->cultural_object_id);
        $this->assertDatabaseCount('ar_models', 1);
    }

    public function test_database_rejects_a_model_without_a_cultural_object(): void
    {
        $this->expectException(QueryException::class);
        ArModel::factory()->create(['cultural_object_id' => null]);
    }

    public function test_database_rejects_a_second_model_for_the_same_object(): void
    {
        $model = ArModel::factory()->create();
        $this->expectException(QueryException::class);
        ArModel::factory()->create(['cultural_object_id' => $model->cultural_object_id]);
    }

    public function test_subobjects_can_have_their_own_models(): void
    {
        $parent = CulturalObject::factory()->create();
        $child = CulturalObject::factory()->create(['parent_id' => $parent->id]);
        ArModel::factory()->create(['cultural_object_id' => $parent->id]);
        ArModel::factory()->create(['cultural_object_id' => $child->id]);
        $this->assertNotSame($parent->arModel->id, $child->arModel->id);
    }

    public function test_ar_reads_updated_cultural_content_and_audio_in_each_language(): void
    {
        $object = CulturalObject::factory()->create([
            'name' => ['id' => 'Paon', 'en' => 'Kitchen'],
            'description' => ['id' => 'Dapur tradisional', 'en' => 'Traditional kitchen'],
            'audio_narration_paths' => ['id' => 'audio/id.mp3', 'en' => 'audio/en.mp3'],
        ]);
        $model = ArModel::factory()->create(['cultural_object_id' => $object->id]);
        $this->getJson('/api/ar/model?id='.$model->id.'&locale=id')->assertOk()
            ->assertJsonPath('name', 'Paon')->assertJsonPath('description', 'Dapur tradisional')
            ->assertJsonPath('audio_url', route('audio.stream', ['path' => 'audio/id.mp3']));
        $object->update(['name' => ['id' => 'Paon baru', 'en' => 'Updated kitchen']]);
        $this->getJson('/api/ar/model?id='.$model->id.'&locale=en')->assertOk()
            ->assertJsonPath('name', 'Updated kitchen')->assertJsonPath('description', 'Traditional kitchen')
            ->assertJsonPath('audio_url', route('audio.stream', ['path' => 'audio/en.mp3']));
    }

    public function test_ar_has_no_audio_when_the_cultural_object_has_no_audio(): void
    {
        $object = CulturalObject::factory()->create(['audio_narration_paths' => null]);
        $model = ArModel::factory()->create(['cultural_object_id' => $object->id]);
        $this->getJson('/api/ar/model?id='.$model->id)->assertOk()->assertJsonPath('audio_url', null);
    }

    public function test_replacing_a_file_preserves_model_id_marker_and_cultural_audio(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('models/old.glb', 'old');
        $admin = User::factory()->create(['role' => 'admin']);
        $object = CulturalObject::factory()->create(['audio_narration_paths' => ['id' => 'audio/id.mp3']]);
        $model = ArModel::factory()->create([
            'cultural_object_id' => $object->id, 'model_3d_path' => 'models/old.glb', 'ar_marker_id' => 'PAON',
        ]);
        $this->actingAs($admin)->put(route('admin.cultural-objects.update', $object->id), [
            'name' => $object->getTranslations('name'),
            'category' => $object->category,
            'model_3d_file' => UploadedFile::fake()->create('replacement.glb', 10),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame($model->id, $object->fresh()->arModel->id);
        $this->assertSame('PAON', $model->fresh()->ar_marker_id);
        $this->assertSame(['id' => 'audio/id.mp3'], $object->fresh()->audio_narration_paths);
        Storage::disk('public')->assertMissing('models/old.glb');
        Storage::disk('public')->assertExists($model->fresh()->model_3d_path);
    }

    public function test_removing_a_model_preserves_cultural_content_and_audio(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('models/paon.glb', 'model');
        Storage::disk('public')->put('audio/id.mp3', 'audio');
        $admin = User::factory()->create(['role' => 'admin']);
        $object = CulturalObject::factory()->create(['audio_narration_paths' => ['id' => 'audio/id.mp3']]);
        ArModel::factory()->create(['cultural_object_id' => $object->id, 'model_3d_path' => 'models/paon.glb']);
        $this->actingAs($admin)->put(route('admin.cultural-objects.update', $object->id), [
            'name' => $object->getTranslations('name'), 'category' => $object->category, 'remove_ar_model' => true,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($object->fresh()->arModel);
        $this->assertSame(['id' => 'audio/id.mp3'], $object->fresh()->audio_narration_paths);
        Storage::disk('public')->assertExists('audio/id.mp3');
        Storage::disk('public')->assertMissing('models/paon.glb');
    }

    public function test_cultural_object_with_a_model_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $model = ArModel::factory()->create();
        $this->actingAs($admin)->delete(route('admin.cultural-objects.destroy', $model->cultural_object_id))
            ->assertSessionHasErrors('destroy');
        $this->assertModelExists($model->culturalObject);
        $this->assertModelExists($model);
    }

    public function test_invalid_asset_upload_does_not_create_an_object(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.cultural-objects.store'), [
            'name' => ['id' => 'Paon', 'en' => 'Kitchen'], 'category' => 'pawongan',
            'model_3d_usdz_file' => UploadedFile::fake()->create('paon.usdz', 10),
        ])->assertSessionHasErrors('model_3d_file');
        $this->assertDatabaseCount('cultural_objects', 0);
    }

    public function test_migration_discards_orphans_and_duplicate_content_but_preserves_cultural_audio(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $object = CulturalObject::factory()->create(['audio_narration_paths' => ['id' => 'audio/shared.mp3']]);
        $migration = require database_path('migrations/2026_11_25_074947_simplify_ar_models_to_cultural_object_assets.php');
        $migration->down();
        Storage::disk('public')->put('models/orphan.glb', 'unused');
        Storage::disk('public')->put('audio/shared.mp3', 'used');
        Storage::disk('public')->put('audio/duplicate.mp3', 'unused duplicate');
        DB::table('ar_models')->insert([
            'name' => json_encode(['id' => 'Model lama']),
            'cultural_object_id' => null,
            'model_3d_path' => 'models/orphan.glb',
            'audio_narration_paths' => json_encode(['id' => 'audio/shared.mp3']),
        ]);
        DB::table('ar_models')->insert([
            'name' => json_encode(['id' => 'Nama berbeda']),
            'cultural_object_id' => $object->id,
            'model_3d_path' => 'models/linked.glb',
            'audio_narration_paths' => json_encode(['id' => 'audio/duplicate.mp3']),
        ]);
        $migration->up();
        $this->artisan('app:cleanup-ar-assets')->assertSuccessful();
        $this->assertDatabaseCount('ar_models', 1);
        $this->assertSame(['id' => 'audio/shared.mp3'], $object->fresh()->audio_narration_paths);
        Storage::disk('public')->assertMissing('models/orphan.glb');
        Storage::disk('public')->assertMissing('audio/duplicate.mp3');
        Storage::disk('public')->assertExists('audio/shared.mp3');
    }

    public function test_saving_content_without_asset_fields_keeps_the_existing_model(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $model = ArModel::factory()->create(['ar_marker_id' => 'KEEP-MARKER']);
        $object = $model->culturalObject;
        $this->actingAs($admin)->put(route('admin.cultural-objects.update', $object->id), [
            'name' => ['id' => 'Nama terbaru', 'en' => 'Latest name'],
            'description' => ['id' => 'Cerita terbaru', 'en' => 'Latest story'],
            'category' => $object->category,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($model->id, $object->fresh()->arModel->id);
        $this->assertSame('KEEP-MARKER', $model->fresh()->ar_marker_id);
        $this->getJson('/api/ar/model?id='.$model->id.'&locale=en')
            ->assertOk()->assertJsonPath('name', 'Latest name')->assertJsonPath('description', 'Latest story');
    }

    public function test_an_existing_model_cannot_be_moved_to_a_different_owner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $model = ArModel::factory()->create();
        $other = CulturalObject::factory()->create();
        $this->actingAs($admin)->putJson(route('admin.cultural-objects.update', $model->cultural_object_id), [
            'name' => $model->culturalObject->getTranslations('name'),
            'category' => $model->culturalObject->category,
            'cultural_object_id' => $other->id,
        ])->assertRedirect();
        $this->assertSame($model->cultural_object_id, $model->fresh()->cultural_object_id);
    }

    public function test_deleting_a_model_keeps_files_referenced_by_another_model(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('models/shared.glb', 'shared');
        $admin = User::factory()->create(['role' => 'admin']);
        $first = ArModel::factory()->create(['model_3d_path' => 'models/shared.glb']);
        $second = ArModel::factory()->create(['model_3d_path' => 'models/shared.glb']);
        $this->actingAs($admin)->put(route('admin.cultural-objects.update', $first->cultural_object_id), [
            'name' => $first->culturalObject->getTranslations('name'),
            'category' => $first->culturalObject->category,
            'remove_ar_model' => true,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertModelMissing($first);
        $this->assertModelExists($second);
        Storage::disk('public')->assertExists('models/shared.glb');
    }

    public function test_asset_failure_rolls_back_content_and_cleans_new_audio_and_models(): void
    {
        Storage::fake('public');
        $this->mock(ImageOptimizerService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('optimizeBinary')->once()->andThrow(new \RuntimeException('Thumbnail failed'));
        });
        $admin = User::factory()->create(['role' => 'admin']);
        try {
            $this->withoutExceptionHandling()->actingAs($admin)->post(route('admin.cultural-objects.store'), [
                'name' => ['id' => 'Paon', 'en' => 'Kitchen'], 'category' => 'pawongan',
                'model_3d_file' => UploadedFile::fake()->create('paon.glb', 10),
                'cultural_audio_file' => ['id' => UploadedFile::fake()->create('audio.mp3', 10)],
                'thumbnail_data' => 'data:image/png;base64,'.base64_encode('thumbnail'),
            ]);
            $this->fail('The thumbnail failure should abort the save.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Thumbnail failed', $exception->getMessage());
        }
        $this->assertDatabaseCount('cultural_objects', 0);
        $this->assertDatabaseCount('ar_models', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_migration_reports_duplicate_links_before_deleting_orphans(): void
    {
        Storage::fake('local');
        $object = CulturalObject::factory()->create();
        $migration = require database_path('migrations/2026_11_25_074947_simplify_ar_models_to_cultural_object_assets.php');
        $migration->down();
        DB::table('ar_models')->insert(['cultural_object_id' => $object->id]);
        $duplicateId = DB::table('ar_models')->insertGetId(['cultural_object_id' => $object->id]);
        DB::table('ar_models')->insert(['cultural_object_id' => null]);
        $exception = null;
        try {
            $migration->up();
        } catch (\RuntimeException $failure) {
            $exception = $failure;
        }
        $this->assertNotNull($exception);
        $this->assertStringContainsString((string) $object->id, $exception->getMessage());
        $this->assertDatabaseCount('ar_models', 3);
        $this->assertTrue(Schema::hasColumn('ar_models', 'name'));
        DB::table('ar_models')->where('id', $duplicateId)->delete();
        $migration->up();
    }

    public static function arStatuses(): array
    {
        return [
            'model files available' => ['with_model', ['Siap AR', 'Model tanpa marker']],
            'no model file including marker-only records' => ['without_model', ['Belum ada model', 'Marker saja']],
            'model without marker' => ['without_marker', ['Model tanpa marker']],
        ];
    }

    #[DataProvider('arStatuses')]
    public function test_cultural_object_list_filters_3d_readiness(string $status, array $expectedNames): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ready = CulturalObject::factory()->create(['name' => ['id' => 'Siap AR', 'en' => 'Ready for AR']]);
        $withoutMarker = CulturalObject::factory()->create(['name' => ['id' => 'Model tanpa marker', 'en' => 'Model without marker']]);
        $markerOnly = CulturalObject::factory()->create(['name' => ['id' => 'Marker saja', 'en' => 'Marker only']]);
        CulturalObject::factory()->create(['name' => ['id' => 'Belum ada model', 'en' => 'No model']]);
        ArModel::factory()->create(['cultural_object_id' => $ready->id, 'ar_marker_id' => 'READY', 'model_3d_usdz_path' => 'models/ready.usdz']);
        ArModel::factory()->create(['cultural_object_id' => $withoutMarker->id, 'ar_marker_id' => null]);
        ArModel::factory()->create(['cultural_object_id' => $markerOnly->id, 'model_3d_path' => null]);
        $response = $this->actingAs($admin)->get(route('admin.cultural-objects', ['ar_status' => $status]))->assertOk();
        $names = $response->viewData('objects')->map(fn (CulturalObject $object) => $object->getTranslation('name', 'id'))->all();
        $this->assertEqualsCanonicalizing($expectedNames, $names);
    }

    public function test_3d_filter_combines_with_search_and_links_to_the_cultural_asset_editor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matching = CulturalObject::factory()->create(['name' => ['id' => 'Paon dengan model', 'en' => 'Model kitchen']]);
        CulturalObject::factory()->create(['name' => ['id' => 'Paon tanpa model', 'en' => 'Kitchen without model']]);
        ArModel::factory()->create(['cultural_object_id' => $matching->id]);
        ArModel::factory()->create();
        $response = $this->actingAs($admin)->get(route('admin.cultural-objects', ['ar_status' => 'with_model', 'search' => 'Paon']));
        $response->assertOk()->assertSee(route('admin.cultural-objects.edit', $matching->id).'#model-3d')
            ->assertSee('Kelola 3D/AR')->assertDontSee('Aset AR &amp; Penanda', false);
        $this->assertSame([$matching->id], $response->viewData('objects')->pluck('id')->all());
        $this->get(route('admin.cultural-objects.edit', $matching->id))->assertOk()
            ->assertSee('id="model-3d"', false)->assertSee('data-ar-assets', false);
    }
}
