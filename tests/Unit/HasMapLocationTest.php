<?php

namespace Tests\Unit;

use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies the trait's boot-time cascade delete (moved out of per-model booted() methods).
 */
class HasMapLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_location_is_deleted_when_owning_model_is_deleted(): void
    {
        $facility = Facility::create([
            'name' => ['en' => 'Toilet', 'id' => 'Toilet'],
            'type' => 'toilet',
            'is_active' => true,
        ]);

        $facility->syncMapLocation(['category' => 'facility', 'latitude' => -8.4, 'longitude' => 115.2]);
        $mapLocationId = $facility->mapLocation->id;

        $facility->delete();

        $this->assertDatabaseMissing('map_locations', ['id' => $mapLocationId]);
    }
}
