<?php

namespace Tests\Feature;

use App\Models\ArModel;
use App\Models\CapacityZone;
use App\Models\CulturalObject;
use App\Models\Event;
use App\Models\Facility;
use App\Models\Feedback;
use App\Models\MapLocation;
use App\Models\TourPackage;
use App\Models\TourRoute;
use App\Models\UmkmProduct;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\RegistersDayOfWeekFunction;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;
    use RegistersDayOfWeekFunction;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerDayOfWeekFunction();

        app()->setLocale('en');

        // Create an admin user to authenticate
        $this->adminUser = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'preferred_language' => 'en',
        ]);
    }

    /**
     * Test admin dashboard page can be rendered.
     */
    public function test_admin_dashboard_can_be_rendered(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    /**
     * Test capacity zone view loading and threshold updates.
     */
    public function test_capacity_zones_rendering_and_threshold_update(): void
    {
        $zone = CapacityZone::create([
            'name' => 'Zona Test',
            'zone_identifier' => 'test_zone',
            'current_count' => 50,
            'max_capacity' => 100,
            'warning_threshold' => 60,
            'critical_threshold' => 80,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.capacity'));

        $response->assertStatus(200);
        $response->assertSee('Zona Test');

        $responseUpdateInvalid = $this->actingAs($this->adminUser)
            ->put(route('admin.capacity.thresholds', $zone->id), [
                'name' => 'Zona Test Updated',
                'max_capacity' => 100,
                'warning_threshold' => 90,
                'critical_threshold' => 80, // critical is smaller, should fail validation
            ]);
        $responseUpdateInvalid->assertSessionHasErrors(['critical_threshold']);

        $responseUpdateSuccess = $this->actingAs($this->adminUser)
            ->put(route('admin.capacity.thresholds', $zone->id), [
                'name' => 'Zona Test Updated',
                'max_capacity' => 120,
                'warning_threshold' => 50,
                'critical_threshold' => 85,
            ]);
        $responseUpdateSuccess->assertSessionHasNoErrors();
        $responseUpdateSuccess->assertRedirect();

        $this->assertDatabaseHas('capacity_zones', [
            'id' => $zone->id,
            'max_capacity' => 120,
            'warning_threshold' => 50,
            'critical_threshold' => 85,
        ]);
    }

    /**
     * Test Cultural Object CRUD workflows.
     */
    public function test_cultural_objects_crud(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.map-manager'));
        $response->assertStatus(200);

        // 1. Create with invalid inputs (empty name)
        $responseCreateInvalid = $this->actingAs($this->adminUser)
            ->post(route('admin.cultural-objects.store'), [
                'name' => '',
                'category' => 'invalid-category',
            ]);
        $responseCreateInvalid->assertSessionHasErrors(['name', 'category']);

        // 2. Create valid object
        $modelFile = UploadedFile::fake()->create('object.glb', 500);
        $audioFile = UploadedFile::fake()->create('narration.mp3', 200);
        $image1 = UploadedFile::fake()->image('image1.png');
        $image2 = UploadedFile::fake()->image('image2.png');

        $responseCreateSuccess = $this->actingAs($this->adminUser)
            ->post(route('admin.cultural-objects.store'), [
                'name' => ['en' => 'Pura Luhur', 'id' => 'Pura Luhur'],
                'category' => 'temple',
                'short_description' => ['en' => 'Spiritual heart of Pura Luhur', 'id' => 'Jantung spiritual Pura Luhur'],
                'description' => ['en' => 'A sacred temple.', 'id' => 'Tempat pemujaan suci.'],
                'latitude' => -8.234,
                'longitude' => 115.345,
                'ar_marker_id' => 'MARKER_PURA_LUHUR',
                'model_3d_file' => $modelFile,
                'audio_narration_file' => $audioFile,
                'historical_images' => [$image1, $image2],
                'has_story' => '1',
                'story_title' => ['Kisah Sejarah Pura', 'Filosofi Bangunan'],
                'story_content' => ['Ini adalah sejarah pura luhur.', 'Filosofi arsitektur pura.'],
                'story_type' => ['history', 'philosophy'],
            ]);
        $responseCreateSuccess->assertRedirect();

        $object = CulturalObject::where('slug', 'pura-luhur')->firstOrFail();
        $this->assertEquals('Spiritual heart of Pura Luhur', $object->short_description);
        $this->assertNotNull($object->model_3d_path);
        $this->assertNotNull($object->audio_narration_path);
        $this->assertCount(2, $object->historical_images);

        // Assert Cultural Stories were created
        $this->assertCount(2, $object->stories);
        $firstStory = $object->stories->where('story_type', 'history')->first();
        $this->assertNotNull($firstStory);
        $this->assertEquals('Kisah Sejarah Pura', $firstStory->title);
        $this->assertEquals(1, $firstStory->order);
        $secondStory = $object->stories->where('story_type', 'philosophy')->first();
        $this->assertNotNull($secondStory);
        $this->assertEquals('Filosofi Bangunan', $secondStory->title);
        $this->assertEquals(2, $secondStory->order);

        // Assert MapLocation was created and synchronized
        $this->assertNotNull($object->mapLocation);
        $this->assertEquals('Pura Luhur', $object->mapLocation->name);
        $this->assertEquals(-8.234, $object->mapLocation->latitude);
        $this->assertEquals(115.345, $object->mapLocation->longitude);

        Storage::disk('public')->assertExists($object->model_3d_path);
        Storage::disk('public')->assertExists($object->audio_narration_path);
        foreach ($object->historical_images as $path) {
            Storage::disk('public')->assertExists($path);
        }

        // 2b. Create with inline AR model editing (select existing model)
        $existingModel = ArModel::create([
            'name' => ['en' => 'Original Model', 'id' => 'Model Asli'],
            'description' => ['en' => 'Original description', 'id' => 'Deskripsi asli'],
            'model_3d_path' => 'models/original.glb',
            'audio_narration_path' => 'audio/original.mp3',
        ]);

        $modelFile2 = UploadedFile::fake()->create('updated_model.glb', 300);
        $responseInlineEdit = $this->actingAs($this->adminUser)
            ->post(route('admin.cultural-objects.store'), [
                'name' => ['en' => 'Pura Inline Edit', 'id' => 'Pura Inline Edit'],
                'category' => 'temple',
                'short_description' => ['en' => 'Inline edit test', 'id' => 'Tes edit inline'],
                'description' => ['en' => 'Testing inline edit.', 'id' => 'Menguji edit inline.'],
                'latitude' => -8.4,
                'longitude' => 115.4,
                'ar_model_id' => (string) $existingModel->id,
                'new_model_name' => ['en' => 'Updated Model EN', 'id' => 'Model Diperbarui ID'],
                'new_model_description' => ['en' => 'Updated desc EN', 'id' => 'Deskripsi diperbarui ID'],
                'model_3d_file' => $modelFile2,
            ]);
        $responseInlineEdit->assertRedirect();

        $existingModel->refresh();
        $this->assertEquals('Updated Model EN', $existingModel->getTranslation('name', 'en'));
        $this->assertEquals('Model Diperbarui ID', $existingModel->getTranslation('name', 'id'));
        $this->assertEquals('Updated desc EN', $existingModel->getTranslation('description', 'en'));
        $this->assertEquals('Deskripsi diperbarui ID', $existingModel->getTranslation('description', 'id'));
        $this->assertNotNull($existingModel->model_3d_path);
        $this->assertNotEquals('models/original.glb', $existingModel->model_3d_path);
        Storage::disk('public')->assertMissing('models/original.glb');
        $this->assertNotNull($existingModel->map_location_id);

        // 3. Update object
        $newModelFile = UploadedFile::fake()->create('new_object.glb', 600);
        $newAudioFile = UploadedFile::fake()->create('new_narration.mp3', 300);
        $newImage = UploadedFile::fake()->image('new_image.png');

        $responseUpdate = $this->actingAs($this->adminUser)
            ->put(route('admin.cultural-objects.update', $object->id), [
                'name' => ['en' => 'Pura Luhur Updated', 'id' => 'Pura Luhur Updated'],
                'category' => 'house',
                'short_description' => ['en' => 'Spiritual heart Updated', 'id' => 'Jantung spiritual Pura Luhur Updated'],
                'description' => ['en' => 'Sacred temple updated.', 'id' => 'Tempat pemujaan suci terupdate.'],
                'latitude' => -8.555,
                'longitude' => 115.666,
                'ar_marker_id' => 'MARKER_PURA_LUHUR_UPDATED',
                'model_3d_file' => $newModelFile,
                'audio_narration_file' => $newAudioFile,
                'historical_images' => [$newImage],
                'has_story' => '1',
                'story_title' => ['Kisah Sejarah Pura Updated'],
                'story_content' => ['Sejarah yang telah diperbarui.'],
                'story_type' => ['history'],
            ]);
        $responseUpdate->assertRedirect();

        $object->refresh();
        $this->assertEquals('Pura Luhur Updated', $object->name);
        $this->assertEquals('Spiritual heart Updated', $object->short_description);
        Storage::disk('public')->assertExists($object->model_3d_path);
        Storage::disk('public')->assertExists($object->audio_narration_path);
        $this->assertCount(1, $object->historical_images);
        Storage::disk('public')->assertExists($object->historical_images[0]);

        // Assert Cultural Stories were updated/deleted
        $this->assertCount(1, $object->stories);
        $updatedStory = $object->stories->first();
        $this->assertEquals('Kisah Sejarah Pura Updated', $updatedStory->title);
        $this->assertEquals('history', $updatedStory->story_type);
        $this->assertEquals(1, $updatedStory->order);

        // Assert MapLocation was updated and synchronized
        $this->assertNotNull($object->mapLocation);
        $this->assertEquals('Pura Luhur Updated', $object->mapLocation->name);
        $this->assertEquals(-8.555, $object->mapLocation->latitude);
        $this->assertEquals(115.666, $object->mapLocation->longitude);

        // 4. Create with inline AR model name-only edit (no file upload)
        $nameOnlyModel = ArModel::create([
            'name' => ['en' => 'Name Only Model', 'id' => 'Model Nama Saja'],
            'description' => ['en' => 'Old desc', 'id' => 'Deskripsi lama'],
            'model_3d_path' => 'models/name_only.glb',
        ]);

        $responseNameOnly = $this->actingAs($this->adminUser)
            ->post(route('admin.cultural-objects.store'), [
                'name' => ['en' => 'Name Only Test', 'id' => 'Tes Nama Saja'],
                'category' => 'temple',
                'short_description' => ['en' => 'Name only', 'id' => 'Nama saja'],
                'description' => ['en' => 'Testing name only.', 'id' => 'Menguji nama saja.'],
                'latitude' => -8.5,
                'longitude' => 115.5,
                'ar_model_id' => (string) $nameOnlyModel->id,
                'new_model_name' => ['en' => 'Updated Name Only'],
                // No new_model_name[id] — tests partial locale preservation
                // No file upload — tests text-only update
            ]);
        $responseNameOnly->assertRedirect();

        $nameOnlyModel->refresh();
        $this->assertEquals('Updated Name Only', $nameOnlyModel->getTranslation('name', 'en'));
        // id locale should be preserved (not cleared)
        $this->assertEquals('Model Nama Saja', $nameOnlyModel->getTranslation('name', 'id'));
        // model_3d_path should NOT be changed (no file uploaded)
        $this->assertEquals('models/name_only.glb', $nameOnlyModel->model_3d_path);

        // 5. Create with AR model + audio file replacement
        $audioModel = ArModel::create([
            'name' => ['en' => 'Audio Model', 'id' => 'Model Audio'],
            'description' => ['en' => 'Audio desc', 'id' => 'Deskripsi audio'],
            'model_3d_path' => 'models/audio_model.glb',
            'audio_narration_path' => 'audio/old_audio.mp3',
        ]);

        $newAudio = UploadedFile::fake()->create('new_audio.mp3', 150);
        $responseAudioEdit = $this->actingAs($this->adminUser)
            ->post(route('admin.cultural-objects.store'), [
                'name' => ['en' => 'Audio Replace Test', 'id' => 'Tes Ganti Audio'],
                'category' => 'temple',
                'short_description' => ['en' => 'Audio replace', 'id' => 'Ganti audio'],
                'description' => ['en' => 'Testing audio replacement.', 'id' => 'Menguji penggantian audio.'],
                'latitude' => -8.6,
                'longitude' => 115.6,
                'ar_model_id' => (string) $audioModel->id,
                'new_model_name' => ['en' => 'Audio Model Updated'],
                'audio_narration_file' => $newAudio,
            ]);
        $responseAudioEdit->assertRedirect();

        $audioModel->refresh();
        $this->assertEquals('Audio Model Updated', $audioModel->getTranslation('name', 'en'));
        $this->assertNotNull($audioModel->audio_narration_path);
        $this->assertNotEquals('audio/old_audio.mp3', $audioModel->audio_narration_path);
        Storage::disk('public')->assertMissing('audio/old_audio.mp3');
        // model_3d_path should be unchanged (no new GLB uploaded)
        $this->assertEquals('models/audio_model.glb', $audioModel->model_3d_path);

        // Save ID before delete to assert missing later
        $objectId = $object->id;

        // 4. Delete object
        $responseDelete = $this->actingAs($this->adminUser)
            ->delete(route('admin.cultural-objects.destroy', $object->id));
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('cultural_objects', [
            'id' => $objectId,
        ]);

        // Assert Cultural Stories were deleted on cascade
        $this->assertDatabaseMissing('cultural_stories', [
            'cultural_object_id' => $objectId,
        ]);

        // Assert MapLocation was deleted
        $this->assertDatabaseMissing('map_locations', [
            'locationable_type' => CulturalObject::class,
            'locationable_id' => $objectId,
        ]);
    }

    /**
     * Test Facility CRUD workflows.
     */
    public function test_facilities_crud(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.map-manager'));
        $response->assertStatus(200);

        // 1. Create valid facility
        $responseCreateSuccess = $this->actingAs($this->adminUser)
            ->post(route('admin.facilities.store'), [
                'name' => ['en' => 'Main Toilet', 'id' => 'Toilet Utama'],
                'type' => 'toilet',
                'description' => ['en' => 'Clean public toilet', 'id' => 'Toilet umum bersih'],
                'is_active' => '1',
                'latitude' => -8.123,
                'longitude' => 115.123,
                'is_accessible' => '1',
                'accessibility_notes' => 'Ada ramp ramah kursi roda',
            ]);
        $responseCreateSuccess->assertRedirect();

        $facility = Facility::where('name->en', 'Main Toilet')->firstOrFail();
        $this->assertEquals('toilet', $facility->type);

        // Assert MapLocation was created and synchronized
        $this->assertNotNull($facility->mapLocation);
        $this->assertEquals('Main Toilet', $facility->mapLocation->name);
        $this->assertEquals(-8.123, $facility->mapLocation->latitude);
        $this->assertEquals(115.123, $facility->mapLocation->longitude);
        $this->assertTrue($facility->mapLocation->is_accessible);

        // 2. Update facility
        $responseUpdate = $this->actingAs($this->adminUser)
            ->put(route('admin.facilities.update', $facility->id), [
                'name' => ['en' => 'Main Toilet Updated', 'id' => 'Toilet Utama Updated'],
                'type' => 'toilet',
                'description' => ['en' => 'Very clean public toilet', 'id' => 'Toilet umum bersih sekali'],
                'is_active' => '1',
                'latitude' => -8.456,
                'longitude' => 115.456,
                'is_accessible' => '1',
                'accessibility_notes' => 'Ramp diperbaiki',
            ]);
        $responseUpdate->assertRedirect();

        $facility->refresh();
        $this->assertEquals('Main Toilet Updated', $facility->name);
        $this->assertEquals('Very clean public toilet', $facility->description);

        // Assert MapLocation was updated and synchronized
        $this->assertNotNull($facility->mapLocation);
        $this->assertEquals('Main Toilet Updated', $facility->mapLocation->name);
        $this->assertEquals(-8.456, $facility->mapLocation->latitude);
        $this->assertEquals(115.456, $facility->mapLocation->longitude);

        // Save ID before delete to assert missing later
        $facilityId = $facility->id;

        // 3. Delete facility
        $responseDelete = $this->actingAs($this->adminUser)
            ->delete(route('admin.facilities.destroy', $facility->id));
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('facilities', [
            'id' => $facilityId,
        ]);

        // Assert MapLocation was deleted
        $this->assertDatabaseMissing('map_locations', [
            'locationable_type' => Facility::class,
            'locationable_id' => $facilityId,
        ]);
    }

    /**
     * Test UMKM Profile CRUD workflows.
     */
    public function test_umkm_profiles_crud(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.map-manager'));
        $response->assertStatus(200);

        // 1. Create valid profile
        $responseCreateSuccess = $this->actingAs($this->adminUser)
            ->post(route('admin.umkm.profile.store'), [
                'business_name' => ['en' => 'Warung Luwak', 'id' => 'Warung Luwak'],
                'owner_name' => 'Made Luwak',
                'category' => 'culinary',
                'description' => ['en' => 'Original civet coffee', 'id' => 'Kopi luwak asli'],
                'rating' => 4.8,
                'is_active' => '1',
                'latitude' => -8.777,
                'longitude' => 115.777,
                'is_accessible' => '1',
                'accessibility_notes' => 'Datar',
            ]);
        $responseCreateSuccess->assertRedirect();

        $profile = UmkmProfile::where('business_name->en', 'Warung Luwak')->firstOrFail();
        $this->assertEquals('Made Luwak', $profile->owner_name);

        // Assert MapLocation was created and synchronized
        $this->assertNotNull($profile->mapLocation);
        $this->assertEquals('Warung Luwak', $profile->mapLocation->name);
        $this->assertEquals(-8.777, $profile->mapLocation->latitude);
        $this->assertEquals(115.777, $profile->mapLocation->longitude);
        $this->assertTrue($profile->mapLocation->is_accessible);

        // 2. Update profile
        $responseUpdate = $this->actingAs($this->adminUser)
            ->put(route('admin.umkm.profile.update', $profile->id), [
                'business_name' => ['en' => 'Warung Luwak Premium', 'id' => 'Warung Luwak Premium'],
                'owner_name' => 'Made Luwak',
                'category' => 'culinary',
                'description' => ['en' => 'Premium civet coffee', 'id' => 'Kopi luwak premium'],
                'rating' => 4.9,
                'is_active' => '1',
                'latitude' => -8.888,
                'longitude' => 115.888,
                'is_accessible' => '1',
                'accessibility_notes' => 'Datar ramah',
            ]);
        $responseUpdate->assertRedirect();

        $profile->refresh();
        $this->assertEquals('Warung Luwak Premium', $profile->business_name);
        $this->assertEquals('Premium civet coffee', $profile->description);

        // Assert MapLocation was updated and synchronized
        $this->assertNotNull($profile->mapLocation);
        $this->assertEquals('Warung Luwak Premium', $profile->mapLocation->name);
        $this->assertEquals(-8.888, $profile->mapLocation->latitude);
        $this->assertEquals(115.888, $profile->mapLocation->longitude);

        // Save ID before delete to assert missing later
        $profileId = $profile->id;

        // 3. Delete profile
        $responseDelete = $this->actingAs($this->adminUser)
            ->delete(route('admin.umkm.profile.destroy', $profile->id));
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('umkm_profiles', [
            'id' => $profileId,
        ]);

        // Assert MapLocation was deleted
        $this->assertDatabaseMissing('map_locations', [
            'locationable_type' => UmkmProfile::class,
            'locationable_id' => $profileId,
        ]);
    }

    /**
     * Test UMKM Products CRUD workflows.
     */
    public function test_umkm_products_crud(): void
    {
        Storage::fake('public');

        $profile = UmkmProfile::create([
            'user_id' => $this->adminUser->id,
            'owner_name' => 'Wayan',
            'business_name' => ['en' => 'Kopi Wayan', 'id' => 'Kopi Wayan'],
            'slug' => 'kopi-wayan',
            'category' => 'culinary',
            'ar_marker_id' => 'UMKM_TEST_MARKER',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.umkm'));
        $response->assertStatus(200);

        // 1. Create with invalid inputs
        $responseCreateInvalid = $this->actingAs($this->adminUser)
            ->post(route('admin.umkm.store'), [
                'name' => '',
                'price' => -100,
            ]);
        $responseCreateInvalid->assertSessionHasErrors(['name', 'price']);

        // 2. Create valid product
        $arModel = UploadedFile::fake()->create('product_model.glb', 400);
        $prodImg1 = UploadedFile::fake()->image('prod1.png');
        $prodImg2 = UploadedFile::fake()->image('prod2.png');

        $responseCreateSuccess = $this->actingAs($this->adminUser)
            ->post(route('admin.umkm.store'), [
                'umkm_profile_id' => $profile->id,
                'name' => ['en' => 'Loloh Cemcem Spesial', 'id' => 'Loloh Cemcem Spesial'],
                'description' => ['en' => 'Penglipuran herbal cemcem drink.', 'id' => 'Minuman herbal daun cemcem khas Penglipuran.'],
                'price' => 5000,
                'stock' => 100,
                'unit' => 'botol',
                'is_active' => true,
                'ar_model_file' => $arModel,
                'images' => [$prodImg1, $prodImg2],
            ]);
        $responseCreateSuccess->assertRedirect();

        $product = UmkmProduct::where('name->en', 'Loloh Cemcem Spesial')->firstOrFail();
        $this->assertCount(2, $product->images);
        foreach ($product->images as $path) {
            Storage::disk('public')->assertExists($path);
        }

        // 3. Update product
        $newArModel = UploadedFile::fake()->create('new_product_model.glb', 500);
        $newProdImg = UploadedFile::fake()->image('new_prod.png');

        $responseUpdate = $this->actingAs($this->adminUser)
            ->put(route('admin.umkm.update', $product->id), [
                'umkm_profile_id' => $profile->id,
                'name' => ['en' => 'Loloh Cemcem Premium', 'id' => 'Loloh Cemcem Premium'],
                'description' => ['en' => 'Premium herbal drink.', 'id' => 'Minuman herbal premium.'],
                'price' => 7500,
                'stock' => 50,
                'unit' => 'botol',
                'is_active' => true,
                'ar_model_file' => $newArModel,
                'images' => [$newProdImg],
            ]);
        $responseUpdate->assertRedirect();

        $product->refresh();
        $this->assertEquals('Loloh Cemcem Premium', $product->name);
        $this->assertCount(1, $product->images);
        Storage::disk('public')->assertExists($product->images[0]);

        // 4. Delete product
        $responseDelete = $this->actingAs($this->adminUser)
            ->delete(route('admin.umkm.destroy', $product->id));
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('umkm_products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Test Events CRUD and duration validation workflows.
     */
    public function test_events_crud_and_duration_validation(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.events'));
        $response->assertStatus(200);

        // 1. Create with invalid duration (end date before start date)
        $responseCreateInvalid = $this->actingAs($this->adminUser)
            ->post(route('admin.events.store'), [
                'name' => ['en' => 'Cultural Festival', 'id' => 'Festival Budaya'],
                'category' => 'Budaya',
                'start_date' => '2026-06-10',
                'start_time' => '10:00',
                'end_date' => '2026-06-09',
                'end_time' => '12:00',
                'location_name' => ['en' => 'Banjar Hall', 'id' => 'Balai Banjar'],
                'price' => 0,
            ]);
        $responseCreateInvalid->assertSessionHasErrors(['end_date']);

        // 2. Create valid
        $responseCreateSuccess = $this->actingAs($this->adminUser)
            ->post(route('admin.events.store'), [
                'name' => ['en' => 'Valid Cultural Festival', 'id' => 'Festival Budaya Valid'],
                'category' => 'Budaya',
                'start_date' => '2026-06-10',
                'start_time' => '10:00',
                'end_date' => '2026-06-12',
                'end_time' => '18:00',
                'location_name' => ['en' => 'Banjar Hall', 'id' => 'Balai Banjar'],
                'is_free' => '1',
                'max_participants' => 200,
            ]);
        $responseCreateSuccess->assertRedirect();

        $event = Event::where('name->en', 'Valid Cultural Festival')->firstOrFail();
        $this->assertEquals('Banjar Hall', $event->location_name);

        // 3. Edit view renders
        $responseEdit = $this->actingAs($this->adminUser)
            ->get(route('admin.events.edit', $event->id));
        $responseEdit->assertStatus(200);

        // 4. Update event
        $responseUpdate = $this->actingAs($this->adminUser)
            ->put(route('admin.events.update', $event->id), [
                'name' => ['en' => 'Cultural Festival Updated', 'id' => 'Festival Budaya Updated'],
                'category' => 'Budaya',
                'start_date' => '2026-06-10',
                'start_time' => '10:00',
                'end_date' => '2026-06-13',
                'end_time' => '18:00',
                'location_name' => ['en' => 'Temple Grounds', 'id' => 'Halaman Pura'],
                'is_free' => '1',
                'max_participants' => 300,
            ]);
        $responseUpdate->assertRedirect();
        $event->refresh();
        $this->assertEquals('Cultural Festival Updated', $event->name);
        $this->assertEquals('Temple Grounds', $event->location_name);

        // 5. Delete event
        $responseDelete = $this->actingAs($this->adminUser)
            ->delete(route('admin.events.destroy', $event->id));
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);
    }

    /**
     * Test Tour Routes CRUD and active toggle workflows.
     */
    public function test_tour_routes_crud_and_active_toggle(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.tour-routes'));
        $response->assertStatus(200);

        // Test create page renders
        $responseCreatePage = $this->actingAs($this->adminUser)
            ->get(route('admin.tour-routes.create'));
        $responseCreatePage->assertStatus(200);
        $responseCreatePage->assertSee('Tambah Rute Wisata Baru');

        // Create dummy attraction points
        $cultural = CulturalObject::create([
            'name' => ['en' => 'Pura Luhur Test', 'id' => 'Pura Luhur Test'],
            'slug' => 'pura-luhur-test',
            'category' => 'temple',
            'description' => ['en' => 'A place of worship.', 'id' => 'Tempat pemujaan.'],
            'ar_marker_id' => 'MARKER_PURA_TEST',
        ]);

        MapLocation::create([
            'name' => 'Pura Luhur Test',
            'category' => 'cultural',
            'locationable_type' => CulturalObject::class,
            'locationable_id' => $cultural->id,
            'latitude' => -8.234,
            'longitude' => 115.345,
            'is_accessible' => true,
        ]);

        // 1. Create valid route with points
        $responseCreate = $this->actingAs($this->adminUser)
            ->post(route('admin.tour-routes.store'), [
                'name' => ['en' => 'Nature Education Route', 'id' => 'Rute Edukasi Alam'],
                'description' => ['en' => 'Exploring bamboo forest and rice fields.', 'id' => 'Mengeksplorasi hutan bambu dan persawahan.'],
                'estimated_duration_minutes' => 60,
                'distance_meters' => 1500,
                'difficulty' => 'easy',
                'points' => [
                    [
                        'locationable_type' => CulturalObject::class,
                        'locationable_id' => $cultural->id,
                        'estimated_visit_minutes' => 20,
                        'storytelling_content' => ['en' => 'This is the great temple.', 'id' => 'Ini adalah pura penataran agung.'],
                    ],
                ],
            ]);
        $responseCreate->assertRedirect();

        $route = TourRoute::where('name->en', 'Nature Education Route')->firstOrFail();
        $this->assertCount(1, $route->routePoints);
        $this->assertEquals(20, $route->routePoints->first()->estimated_visit_minutes);

        // Test edit page renders
        $responseEditPage = $this->actingAs($this->adminUser)
            ->get(route('admin.tour-routes.edit', $route->id));
        $responseEditPage->assertStatus(200);
        $responseEditPage->assertSee('Edit Rute Wisata');

        // 2. Update route with new details and points
        $responseUpdate = $this->actingAs($this->adminUser)
            ->put(route('admin.tour-routes.update', $route->id), [
                'name' => ['en' => 'Nature Education Route Mod', 'id' => 'Rute Edukasi Alam Mod'],
                'description' => ['en' => 'Bamboo forest trekking path.', 'id' => 'Jalur trekking hutan bambu.'],
                'estimated_duration_minutes' => 90,
                'distance_meters' => 2000,
                'difficulty' => 'moderate',
                'is_active' => true,
                'points' => [
                    [
                        'locationable_type' => CulturalObject::class,
                        'locationable_id' => $cultural->id,
                        'estimated_visit_minutes' => 30,
                        'storytelling_content' => ['en' => 'Updated narrative.', 'id' => 'Narasi terupdate.'],
                    ],
                ],
            ]);
        $responseUpdate->assertRedirect();

        $route->refresh();
        $this->assertEquals('Nature Education Route Mod', $route->name);
        $this->assertCount(1, $route->routePoints);
        $this->assertEquals(30, $route->routePoints->first()->estimated_visit_minutes);
        $this->assertEquals('Updated narrative.', $route->routePoints->first()->storytelling_content);

        // 3. Toggle active
        $this->assertTrue($route->is_active);
        $responseToggle = $this->actingAs($this->adminUser)
            ->patch(route('admin.tour-routes.toggle', $route->id));
        $responseToggle->assertRedirect();
        $this->assertFalse($route->fresh()->is_active);

        // 4. Delete route
        $responseDelete = $this->actingAs($this->adminUser)
            ->delete(route('admin.tour-routes.destroy', $route->id));
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('tour_routes', [
            'id' => $route->id,
        ]);
        $this->assertDatabaseMissing('tour_route_points', [
            'tour_route_id' => $route->id,
        ]);
    }

    /**
     * Test Tour Packages CRUD workflows.
     */
    public function test_tour_packages_crud(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.packages'));
        $response->assertStatus(200);

        // 1. Create valid package
        $pkgImg1 = UploadedFile::fake()->image('pkg1.png');
        $pkgImg2 = UploadedFile::fake()->image('pkg2.png');

        $responseCreate = $this->actingAs($this->adminUser)
            ->post(route('admin.packages.store'), [
                'name' => ['en' => 'Ancient Balinese Culture Package', 'id' => 'Paket Budaya Bali Kuno'],
                'description' => ['en' => 'Learn weaving and Loloh Cemcem.', 'id' => 'Belajar kerajinan tenun dan Loloh Cemcem.'],
                'price' => 150000,
                'duration_hours' => 4.5,
                'max_capacity' => 15,
                'min_party_size' => 2,
                'inclusions' => ['en' => "Local guide\nWelcome drink\nWeaving materials", 'id' => "Pemandu lokal\nWelcome drink\nMateri tenun"],
                'is_active' => true,
                'images' => [$pkgImg1, $pkgImg2],
            ]);
        $responseCreate->assertRedirect();

        $package = TourPackage::where('name->en', 'Ancient Balinese Culture Package')->firstOrFail();
        $this->assertCount(2, $package->images);
        foreach ($package->images as $path) {
            Storage::disk('public')->assertExists($path);
        }

        // 2. Edit route renders
        $responseEdit = $this->actingAs($this->adminUser)
            ->get(route('admin.packages.edit', $package->id));
        $responseEdit->assertStatus(200);

        // 3. Update package
        $newPkgImg = UploadedFile::fake()->image('new_pkg.png');

        $responseUpdate = $this->actingAs($this->adminUser)
            ->put(route('admin.packages.update', $package->id), [
                'name' => ['en' => 'Balinese Culture Package Mod', 'id' => 'Paket Budaya Bali Kuno Mod'],
                'description' => ['en' => 'Updated package.', 'id' => 'Paket terupdate.'],
                'price' => 180000,
                'duration_hours' => 5.0,
                'max_capacity' => 20,
                'min_party_size' => 1,
                'inclusions' => ['en' => "Local guide\nWelcome drink", 'id' => "Pemandu lokal\nWelcome drink"],
                'is_active' => true,
                'images' => [$newPkgImg],
            ]);
        $responseUpdate->assertRedirect();

        $package->refresh();
        $this->assertEquals('Balinese Culture Package Mod', $package->name);
        $this->assertCount(1, $package->images);
        Storage::disk('public')->assertExists($package->images[0]);

        // 4. Delete package
        $responseDelete = $this->actingAs($this->adminUser)
            ->delete(route('admin.packages.destroy', $package->id));
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('tour_packages', [
            'id' => $package->id,
        ]);
    }

    /**
     * Test Feedback / Review admin replies and public visibility.
     */
    public function test_feedback_index_reply_and_toggle_public(): void
    {
        $feedback = Feedback::create([
            'user_id' => $this->adminUser->id,
            'feedback_type' => 'general',
            'rating' => 5,
            'comment' => 'Kunjungan yang sangat luar biasa indah.',
            'is_public' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.feedback'));
        $response->assertStatus(200);
        $response->assertSee('Kunjungan yang sangat luar biasa indah.');

        // Reply to feedback
        $responseReply = $this->actingAs($this->adminUser)
            ->post(route('admin.feedback.reply', $feedback->id), [
                'admin_response' => 'Terima kasih atas ulasannya!',
            ]);
        $responseReply->assertRedirect();
        $this->assertEquals('Terima kasih atas ulasannya!', $feedback->fresh()->admin_response);

        // Toggle public status
        $this->assertFalse($feedback->fresh()->is_public);
        $responseToggle = $this->actingAs($this->adminUser)
            ->patch(route('admin.feedback.toggle', $feedback->id));
        $responseToggle->assertRedirect();
        $this->assertTrue($feedback->fresh()->is_public);

        // Delete feedback
        $responseDelete = $this->actingAs($this->adminUser)
            ->delete(route('admin.feedback.destroy', $feedback->id));
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('feedbacks', [
            'id' => $feedback->id,
        ]);
    }

    /**
     * Test Report Period filter and Simulated PDF downloads.
     */
    public function test_reports_index_and_pdf_download(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports', ['period' => 'Mei 2026']));
        $response->assertStatus(200);
        $response->assertSee('Laporan & Analitik');

        // Test download renders printable view
        $responseDownload = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.download', ['period' => 'Mei 2026']));
        $responseDownload->assertStatus(200);
        $responseDownload->assertSee('Laporan Mei 2026');
    }

    /**
     * Test non-admin users cannot access the admin dashboard.
     */
    public function test_non_admin_users_cannot_access_admin_dashboard(): void
    {
        $nonAdmin = User::factory()->create([
            'role' => 'tourist',
        ]);

        $response = $this->actingAs($nonAdmin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    /**
     * Test admin users cannot access user pages and are redirected to admin dashboard.
     */
    public function test_admin_users_cannot_access_user_pages(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('home'));

        $response->assertRedirect(route('admin.dashboard'));
    }
}
