<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\Facility;
use App\Models\MapLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapManagerPointsTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_add_a_second_point_to_a_cultural_object(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Kulkul', 'id' => 'Kulkul'],
            'slug' => 'kulkul',
            'description' => ['en' => 'a', 'id' => 'b'],
            'short_description' => ['en' => 'a', 'id' => 'b'],
            'category' => 'pawongan',
        ]);
        $object->mapLocations()->create([
            'name' => 'Kulkul A', 'category' => 'cultural', 'latitude' => -8.1, 'longitude' => 115.1,
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('admin.map-manager.points.store'), [
            'owner_type' => 'cultural_object',
            'owner_id' => $object->id,
            'latitude' => -8.2,
            'longitude' => 115.2,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(2, $object->mapLocations()->count());
    }

    public function test_admin_can_add_a_second_point_to_a_facility(): void
    {
        $facility = Facility::create(['name' => ['en' => 'Entrance', 'id' => 'Pintu Masuk'], 'type' => 'entrance', 'is_active' => true]);
        $facility->mapLocations()->create([
            'name' => 'Entrance A', 'category' => 'facility', 'latitude' => -8.1, 'longitude' => 115.1,
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('admin.map-manager.points.store'), [
            'owner_type' => 'facility',
            'owner_id' => $facility->id,
            'latitude' => -8.3, 'longitude' => 115.3,
        ]);

        $response->assertOk();
        $this->assertEquals(2, $facility->mapLocations()->count());
    }

    public function test_store_point_rejects_invalid_owner_type(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('admin.map-manager.points.store'), [
            'owner_type' => 'umkm_profile',
            'owner_id' => 1,
            'latitude' => -8.1, 'longitude' => 115.1,
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_reposition_a_single_point_without_touching_the_owner(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Padmasana', 'id' => 'Padmasana'],
            'slug' => 'padmasana',
            'description' => ['en' => 'a', 'id' => 'b'],
            'short_description' => ['en' => 'a', 'id' => 'b'],
            'category' => 'parahyangan',
        ]);
        $point = $object->mapLocations()->create([
            'name' => 'Padmasana A', 'category' => 'cultural', 'latitude' => -8.1, 'longitude' => 115.1,
        ]);

        $response = $this->actingAs($this->adminUser)->putJson(route('admin.map-manager.points.update', $point), [
            'latitude' => -8.9, 'longitude' => 115.9,
        ]);

        $response->assertOk();
        $this->assertEquals(-8.9, $point->fresh()->latitude);
        $this->assertNotNull($object->fresh());
    }

    public function test_admin_can_delete_a_single_point_without_deleting_the_owner(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Kulkul', 'id' => 'Kulkul'],
            'slug' => 'kulkul-2',
            'description' => ['en' => 'a', 'id' => 'b'],
            'short_description' => ['en' => 'a', 'id' => 'b'],
            'category' => 'pawongan',
        ]);
        $point1 = $object->mapLocations()->create(['name' => 'A', 'category' => 'cultural', 'latitude' => -8.1, 'longitude' => 115.1]);
        $object->mapLocations()->create(['name' => 'B', 'category' => 'cultural', 'latitude' => -8.2, 'longitude' => 115.2]);

        $response = $this->actingAs($this->adminUser)->deleteJson(route('admin.map-manager.points.destroy', $point1));

        $response->assertOk();
        $this->assertNull(MapLocation::find($point1->id));
        $this->assertEquals(1, $object->mapLocations()->count());
        $this->assertNotNull(CulturalObject::find($object->id));
    }
}
