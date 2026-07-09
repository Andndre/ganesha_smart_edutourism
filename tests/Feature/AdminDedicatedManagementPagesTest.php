<?php

namespace Tests\Feature;

use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDedicatedManagementPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_cultural_objects_index(): void
    {
        CulturalObject::create([
            'name' => ['en' => 'Kulkul', 'id' => 'Kulkul'],
            'slug' => 'kulkul',
            'description' => ['en' => 'a', 'id' => 'b'],
            'short_description' => ['en' => 'a', 'id' => 'b'],
            'category' => 'pawongan',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.cultural-objects'));

        $response->assertOk()->assertSee('Kulkul');
    }

    public function test_admin_can_view_cultural_object_create_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.cultural-objects.create'));

        $response->assertOk()->assertSee('Tambah Objek Budaya');
    }

    public function test_admin_can_view_cultural_object_edit_page_with_prefilled_data(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Padmasana', 'id' => 'Padmasana'],
            'slug' => 'padmasana',
            'description' => ['en' => 'desc en', 'id' => 'desc id'],
            'short_description' => ['en' => 'short en', 'id' => 'short id'],
            'category' => 'parahyangan',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.cultural-objects.edit', $object->id));

        $response->assertOk()->assertSee('Padmasana')->assertSee('short en');
    }

    public function test_editing_a_cultural_object_via_dedicated_page_does_not_move_its_existing_point(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Kulkul', 'id' => 'Kulkul'],
            'slug' => 'kulkul-edit-test',
            'description' => ['en' => 'a', 'id' => 'b'],
            'short_description' => ['en' => 'a', 'id' => 'b'],
            'category' => 'pawongan',
        ]);
        $point = $object->mapLocations()->create([
            'name' => 'Kulkul A', 'category' => 'cultural', 'latitude' => -8.111, 'longitude' => 115.111,
        ]);

        // Simulate the dedicated edit page's submission: no latitude/longitude fields at all.
        $response = $this->actingAs($this->adminUser)->put(route('admin.cultural-objects.update', $object->id), [
            'name' => ['en' => 'Kulkul Updated', 'id' => 'Kulkul Updated'],
            'category' => 'pawongan',
            'short_description' => ['en' => 'x', 'id' => 'y'],
            'description' => ['en' => 'a', 'id' => 'b'],
            'redirect_to' => 'cultural-objects',
        ]);

        $response->assertRedirect(route('admin.cultural-objects'));
        $this->assertEquals(-8.111, $point->fresh()->latitude);
        $this->assertEquals(115.111, $point->fresh()->longitude);
        $this->assertEquals('Kulkul Updated', $object->fresh()->getTranslation('name', 'en'));
    }

    public function test_editing_a_cultural_object_from_map_manager_still_moves_its_point(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Kulkul', 'id' => 'Kulkul'],
            'slug' => 'kulkul-map-manager-test',
            'description' => ['en' => 'a', 'id' => 'b'],
            'short_description' => ['en' => 'a', 'id' => 'b'],
            'category' => 'pawongan',
        ]);
        $point = $object->mapLocations()->create([
            'name' => 'Kulkul A', 'category' => 'cultural', 'latitude' => -8.111, 'longitude' => 115.111,
        ]);

        // Simulate map-manager's form, which always includes latitude/longitude.
        $response = $this->actingAs($this->adminUser)->put(route('admin.cultural-objects.update', $object->id), [
            'name' => ['en' => 'Kulkul Updated', 'id' => 'Kulkul Updated'],
            'category' => 'pawongan',
            'short_description' => ['en' => 'x', 'id' => 'y'],
            'description' => ['en' => 'a', 'id' => 'b'],
            'latitude' => -8.999,
            'longitude' => 115.999,
        ]);

        $response->assertRedirect(route('admin.map-manager'));
        $this->assertEquals(-8.999, $point->fresh()->latitude);
    }

    public function test_admin_can_view_facilities_index(): void
    {
        Facility::create(['name' => ['en' => 'Main Gate', 'id' => 'Pintu Masuk'], 'type' => 'information', 'is_active' => true]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.facilities'));

        $response->assertOk()->assertSee('Pintu Masuk');
    }

    public function test_admin_can_view_facility_create_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.facilities.create'));

        $response->assertOk()->assertSee('Tambah Fasilitas');
    }

    public function test_admin_can_view_facility_edit_page_with_prefilled_coordinates(): void
    {
        $facility = Facility::create(['name' => ['en' => 'Main Gate', 'id' => 'Pintu Masuk'], 'type' => 'information', 'is_active' => true]);
        $facility->syncMapLocation(['category' => 'facility', 'latitude' => -8.4321, 'longitude' => 115.1234]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.facilities.edit', $facility->id));

        $response->assertOk()->assertSee('-8.4321');
    }

    public function test_redirect_after_save_targets_dedicated_page_when_requested(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.cultural-objects.store'), [
            'name' => ['en' => 'New Object', 'id' => 'Objek Baru'],
            'category' => 'palemahan',
            'short_description' => ['en' => 'a', 'id' => 'b'],
            'description' => ['en' => 'a', 'id' => 'b'],
            'redirect_to' => 'cultural-objects',
        ]);

        $response->assertRedirect(route('admin.cultural-objects'));
    }

    public function test_dedicated_cultural_object_create_page_lists_only_unassigned_ar_models(): void
    {
        $assignedObject = CulturalObject::create([
            'name' => ['en' => 'Assigned', 'id' => 'Assigned'],
            'slug' => 'assigned-object',
            'description' => ['en' => 'a', 'id' => 'b'],
            'short_description' => ['en' => 'a', 'id' => 'b'],
            'category' => 'pawongan',
        ]);
        ArModel::create(['name' => ['en' => 'Taken Model'], 'cultural_object_id' => $assignedObject->id]);
        ArModel::create(['name' => ['en' => 'Free Model']]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.cultural-objects.create'));

        $response->assertOk();
        $response->assertDontSee('Taken Model');
        $response->assertSee('Free Model');
    }
}
