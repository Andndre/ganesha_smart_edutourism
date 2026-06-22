<?php

namespace Tests\Feature;

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

        // Create an admin user to authenticate
        $this->adminUser = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'role' => 'admin',
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

        // Test update validation error (critical <= warning)
        $responseUpdateInvalid = $this->actingAs($this->adminUser)
            ->put(route('admin.capacity.thresholds', $zone->id), [
                'max_capacity' => 100,
                'warning_threshold' => 90,
                'critical_threshold' => 80, // critical is smaller, should fail validation
            ]);
        $responseUpdateInvalid->assertSessionHasErrors(['critical_threshold']);

        // Test successful update
        $responseUpdateSuccess = $this->actingAs($this->adminUser)
            ->put(route('admin.capacity.thresholds', $zone->id), [
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
                'name' => 'Pura Luhur',
                'category' => 'temple',
                'short_description' => 'Jantung spiritual Pura Luhur',
                'description' => 'Tempat pemujaan suci.',
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

        $object = CulturalObject::where('name', 'Pura Luhur')->firstOrFail();
        $this->assertEquals('Jantung spiritual Pura Luhur', $object->short_description);
        $this->assertNotNull($object->model_3d_path);
        $this->assertNotNull($object->audio_narration_path);
        $this->assertCount(2, $object->historical_images);

        // Assert Cultural Stories were created
        $this->assertDatabaseHas('cultural_stories', [
            'cultural_object_id' => $object->id,
            'title' => 'Kisah Sejarah Pura',
            'content' => 'Ini adalah sejarah pura luhur.',
            'story_type' => 'history',
            'order' => 1,
        ]);
        $this->assertDatabaseHas('cultural_stories', [
            'cultural_object_id' => $object->id,
            'title' => 'Filosofi Bangunan',
            'content' => 'Filosofi arsitektur pura.',
            'story_type' => 'philosophy',
            'order' => 2,
        ]);

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

        // 3. Update object
        $newModelFile = UploadedFile::fake()->create('new_object.glb', 600);
        $newAudioFile = UploadedFile::fake()->create('new_narration.mp3', 300);
        $newImage = UploadedFile::fake()->image('new_image.png');

        $responseUpdate = $this->actingAs($this->adminUser)
            ->put(route('admin.cultural-objects.update', $object->id), [
                'name' => 'Pura Luhur Updated',
                'category' => 'house',
                'short_description' => 'Jantung spiritual Pura Luhur Updated',
                'description' => 'Tempat pemujaan suci terupdate.',
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
        $this->assertEquals('Jantung spiritual Pura Luhur Updated', $object->short_description);
        Storage::disk('public')->assertExists($object->model_3d_path);
        Storage::disk('public')->assertExists($object->audio_narration_path);
        $this->assertCount(1, $object->historical_images);
        Storage::disk('public')->assertExists($object->historical_images[0]);

        // Assert Cultural Stories were updated/deleted
        $this->assertDatabaseHas('cultural_stories', [
            'cultural_object_id' => $object->id,
            'title' => 'Kisah Sejarah Pura Updated',
            'content' => 'Sejarah yang telah diperbarui.',
            'story_type' => 'history',
            'order' => 1,
        ]);
        $this->assertDatabaseMissing('cultural_stories', [
            'cultural_object_id' => $object->id,
            'title' => 'Filosofi Bangunan',
        ]);

        // Assert MapLocation was updated and synchronized
        $this->assertNotNull($object->mapLocation);
        $this->assertEquals('Pura Luhur Updated', $object->mapLocation->name);
        $this->assertEquals(-8.555, $object->mapLocation->latitude);
        $this->assertEquals(115.666, $object->mapLocation->longitude);

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
                'name' => 'Toilet Utama',
                'type' => 'toilet',
                'description' => 'Toilet umum bersih',
                'is_active' => '1',
                'latitude' => -8.123,
                'longitude' => 115.123,
                'is_accessible' => '1',
                'accessibility_notes' => 'Ada ramp ramah kursi roda',
            ]);
        $responseCreateSuccess->assertRedirect();

        $facility = Facility::where('name', 'Toilet Utama')->firstOrFail();
        $this->assertEquals('toilet', $facility->type);

        // Assert MapLocation was created and synchronized
        $this->assertNotNull($facility->mapLocation);
        $this->assertEquals('Toilet Utama', $facility->mapLocation->name);
        $this->assertEquals(-8.123, $facility->mapLocation->latitude);
        $this->assertEquals(115.123, $facility->mapLocation->longitude);
        $this->assertTrue($facility->mapLocation->is_accessible);

        // 2. Update facility
        $responseUpdate = $this->actingAs($this->adminUser)
            ->put(route('admin.facilities.update', $facility->id), [
                'name' => 'Toilet Utama Updated',
                'type' => 'toilet',
                'description' => 'Toilet umum bersih sekali',
                'is_active' => '1',
                'latitude' => -8.456,
                'longitude' => 115.456,
                'is_accessible' => '1',
                'accessibility_notes' => 'Ramp diperbaiki',
            ]);
        $responseUpdate->assertRedirect();

        $facility->refresh();
        $this->assertEquals('Toilet Utama Updated', $facility->name);
        $this->assertEquals('Toilet umum bersih sekali', $facility->description);

        // Assert MapLocation was updated and synchronized
        $this->assertNotNull($facility->mapLocation);
        $this->assertEquals('Toilet Utama Updated', $facility->mapLocation->name);
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
                'business_name' => 'Warung Luwak',
                'owner_name' => 'Made Luwak',
                'category' => 'culinary',
                'description' => 'Kopi luwak asli',
                'rating' => 4.8,
                'is_active' => '1',
                'latitude' => -8.777,
                'longitude' => 115.777,
                'is_accessible' => '1',
                'accessibility_notes' => 'Datar',
            ]);
        $responseCreateSuccess->assertRedirect();

        $profile = UmkmProfile::where('business_name', 'Warung Luwak')->firstOrFail();
        $this->assertEquals('Made Luwak', $profile->owner_name);
        $this->assertEquals('culinary', $profile->category);

        // Assert MapLocation was created and synchronized
        $this->assertNotNull($profile->mapLocation);
        $this->assertEquals('Warung Luwak', $profile->mapLocation->name);
        $this->assertEquals(-8.777, $profile->mapLocation->latitude);
        $this->assertEquals(115.777, $profile->mapLocation->longitude);
        $this->assertTrue($profile->mapLocation->is_accessible);

        // 2. Update profile
        $responseUpdate = $this->actingAs($this->adminUser)
            ->put(route('admin.umkm.profile.update', $profile->id), [
                'business_name' => 'Warung Luwak Premium',
                'owner_name' => 'Made Luwak',
                'category' => 'culinary',
                'description' => 'Kopi luwak premium',
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
        $this->assertEquals('Kopi luwak premium', $profile->description);

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
            'business_name' => 'Kopi Wayan',
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
                'name' => 'Loloh Cemcem Spesial',
                'description' => 'Minuman herbal daun cemcem khas Penglipuran.',
                'price' => 5000,
                'stock' => 100,
                'unit' => 'botol',
                'is_active' => true,
                'ar_model_file' => $arModel,
                'images' => [$prodImg1, $prodImg2],
            ]);
        $responseCreateSuccess->assertRedirect();

        $product = UmkmProduct::where('name', 'Loloh Cemcem Spesial')->firstOrFail();
        $this->assertNotNull($product->ar_model_path);
        $this->assertCount(2, $product->images);

        Storage::disk('public')->assertExists($product->ar_model_path);
        foreach ($product->images as $path) {
            Storage::disk('public')->assertExists($path);
        }

        // 3. Update product
        $newArModel = UploadedFile::fake()->create('new_product_model.glb', 500);
        $newProdImg = UploadedFile::fake()->image('new_prod.png');

        $responseUpdate = $this->actingAs($this->adminUser)
            ->put(route('admin.umkm.update', $product->id), [
                'umkm_profile_id' => $profile->id,
                'name' => 'Loloh Cemcem Premium',
                'description' => 'Minuman herbal premium.',
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
        Storage::disk('public')->assertExists($product->ar_model_path);
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
                'name' => 'Festival Budaya',
                'category' => 'Budaya',
                'start_date' => '2026-06-10',
                'start_time' => '10:00',
                'end_date' => '2026-06-09',
                'end_time' => '12:00',
                'location_name' => 'Balai Banjar',
                'price' => 0,
            ]);
        $responseCreateInvalid->assertSessionHasErrors(['end_date']);

        // 2. Create valid
        $responseCreateSuccess = $this->actingAs($this->adminUser)
            ->post(route('admin.events.store'), [
                'name' => 'Festival Budaya Valid',
                'category' => 'Budaya',
                'start_date' => '2026-06-10',
                'start_time' => '10:00',
                'end_date' => '2026-06-12',
                'end_time' => '18:00',
                'location_name' => 'Balai Banjar',
                'is_free' => '1',
                'max_participants' => 200,
            ]);
        $responseCreateSuccess->assertRedirect();
        $this->assertDatabaseHas('events', [
            'name' => 'Festival Budaya Valid',
            'location_name' => 'Balai Banjar',
        ]);

        $event = Event::where('name', 'Festival Budaya Valid')->firstOrFail();

        // 3. Edit view renders
        $responseEdit = $this->actingAs($this->adminUser)
            ->get(route('admin.events.edit', $event->id));
        $responseEdit->assertStatus(200);

        // 4. Update event
        $responseUpdate = $this->actingAs($this->adminUser)
            ->put(route('admin.events.update', $event->id), [
                'name' => 'Festival Budaya Updated',
                'category' => 'Budaya',
                'start_date' => '2026-06-10',
                'start_time' => '10:00',
                'end_date' => '2026-06-13',
                'end_time' => '18:00',
                'location_name' => 'Halaman Pura',
                'is_free' => '1',
                'max_participants' => 300,
            ]);
        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Festival Budaya Updated',
            'location_name' => 'Halaman Pura',
        ]);

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
            'name' => 'Pura Luhur Test',
            'slug' => 'pura-luhur-test',
            'category' => 'temple',
            'description' => 'Tempat pemujaan.',
            'ar_marker_id' => 'MARKER_PURA_TEST',
        ]);

        MapLocation::create([
            'name' => $cultural->name,
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
                'name' => 'Rute Edukasi Alam',
                'description' => 'Mengeksplorasi hutan bambu dan persawahan.',
                'estimated_duration_minutes' => 60,
                'distance_meters' => 1500,
                'difficulty' => 'easy',
                'is_smart_route' => false,
                'points' => [
                    [
                        'locationable_type' => CulturalObject::class,
                        'locationable_id' => $cultural->id,
                        'estimated_visit_minutes' => 20,
                        'storytelling_content' => 'Ini adalah pura penataran agung.',
                    ],
                ],
            ]);
        $responseCreate->assertRedirect();
        $this->assertDatabaseHas('tour_routes', [
            'name' => 'Rute Edukasi Alam',
            'difficulty' => 'easy',
        ]);

        $route = TourRoute::where('name', 'Rute Edukasi Alam')->firstOrFail();
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
                'name' => 'Rute Edukasi Alam Mod',
                'description' => 'Jalur trekking hutan bambu.',
                'estimated_duration_minutes' => 90,
                'distance_meters' => 2000,
                'difficulty' => 'moderate',
                'is_smart_route' => true,
                'is_active' => true,
                'points' => [
                    [
                        'locationable_type' => CulturalObject::class,
                        'locationable_id' => $cultural->id,
                        'estimated_visit_minutes' => 30,
                        'storytelling_content' => 'Narasi terupdate.',
                    ],
                ],
            ]);
        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('tour_routes', [
            'id' => $route->id,
            'name' => 'Rute Edukasi Alam Mod',
            'difficulty' => 'moderate',
        ]);

        $route->refresh();
        $this->assertCount(1, $route->routePoints);
        $this->assertEquals(30, $route->routePoints->first()->estimated_visit_minutes);
        $this->assertEquals('Narasi terupdate.', $route->routePoints->first()->storytelling_content);

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
                'name' => 'Paket Budaya Bali Kuno',
                'description' => 'Belajar kerajinan tenun dan Loloh Cemcem.',
                'price' => 150000,
                'duration_hours' => 4.5,
                'max_capacity' => 15,
                'min_party_size' => 2,
                'inclusions' => ['Pemandu lokal', 'Welcome drink', 'Materi tenun'],
                'is_active' => true,
                'images' => [$pkgImg1, $pkgImg2],
            ]);
        $responseCreate->assertRedirect();

        $package = TourPackage::where('name', 'Paket Budaya Bali Kuno')->firstOrFail();
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
                'name' => 'Paket Budaya Bali Kuno Mod',
                'description' => 'Paket terupdate.',
                'price' => 180000,
                'duration_hours' => 5.0,
                'max_capacity' => 20,
                'min_party_size' => 1,
                'inclusions' => ['Pemandu lokal', 'Welcome drink'],
                'is_active' => true,
                'images' => [$newPkgImg],
            ]);
        $responseUpdate->assertRedirect();

        $package->refresh();
        $this->assertEquals('Paket Budaya Bali Kuno Mod', $package->name);
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
