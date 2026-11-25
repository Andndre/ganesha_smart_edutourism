<?php

namespace Tests\Feature;

use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Models\MapLocation;
use App\Models\TraditionalHouse;
use App\Models\VillageSettings;
use Database\Seeders\PekaranganCulturalObjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PekaranganCulturalObjectSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_one_shared_parent_without_creating_or_moving_physical_courtyards(): void
    {
        $houses = TraditionalHouse::factory()->count(2)->create();
        foreach ($houses as $house) {
            $house->syncMapLocation(['category' => 'cultural', 'latitude' => -8.42, 'longitude' => 115.36]);
        }
        $points = MapLocation::orderBy('id')->get()->toArray();

        $this->seed(PekaranganCulturalObjectSeeder::class);

        $root = CulturalObject::sole();
        $this->assertSame('Pekarangan Rumah Adat', $root->getTranslation('name', 'id'));
        $this->assertSame('Traditional Courtyard', $root->getTranslation('name', 'en'));
        $this->assertSame('pekarangan', $root->place_type);
        $this->assertNull($root->parent_id);
        $this->assertNull($root->mapLocation);
        $this->assertSame($root->id, VillageSettings::get()->house_cultural_object_id);
        $this->assertSame($root->id, VillageSettings::get()->featured_cultural_object_id);
        $this->assertSame($points, MapLocation::orderBy('id')->get()->toArray());
        $this->assertSame($houses->modelKeys(), TraditionalHouse::orderBy('id')->pluck('id')->all());
    }

    public function test_repeated_seed_preserves_edited_content_and_a_different_featured_choice(): void
    {
        $this->seed(PekaranganCulturalObjectSeeder::class);
        $root = CulturalObject::sole();
        $root->update([
            'name' => ['id' => 'Cerita Pekarangan Penglipuran', 'en' => 'Our Courtyard Story'],
            'historical_images' => ['photos/custom.jpg'], 'audio_narration_paths' => ['id' => 'audio/custom.mp3'],
        ]);
        $featured = CulturalObject::factory()->create();
        VillageSettings::get()->update(['featured_cultural_object_id' => $featured->id]);
        $original = $root->fresh()->getAttributes();

        $this->seed(PekaranganCulturalObjectSeeder::class);

        $this->assertDatabaseCount('cultural_objects', 2);
        $this->assertSame($original, $root->fresh()->getAttributes());
        $this->assertSame($root->id, VillageSettings::get()->house_cultural_object_id);
        $this->assertSame($featured->id, VillageSettings::get()->featured_cultural_object_id);

        VillageSettings::get()->update(['featured_cultural_object_id' => null]);
        $this->seed(PekaranganCulturalObjectSeeder::class);
        $this->assertNull(VillageSettings::get()->featured_cultural_object_id);
    }

    public function test_existing_courtyard_content_is_reused_and_child_objects_are_preserved(): void
    {
        $root = CulturalObject::factory()->create(['name' => ['id' => 'Pekarangan Rumah Adat', 'en' => 'Existing Courtyard']]);
        $room = CulturalObject::factory()->create(['parent_id' => $root->id]);
        $original = $root->fresh()->getAttributes();

        $this->seed(PekaranganCulturalObjectSeeder::class);

        $this->assertDatabaseCount('cultural_objects', 2);
        $this->assertSame($original, $root->fresh()->getAttributes());
        $this->assertSame($root->id, $room->fresh()->parent_id);
        $this->assertSame($root->id, VillageSettings::get()->house_cultural_object_id);
    }

    public function test_an_explicitly_configured_parent_is_reused_without_creating_another_parent(): void
    {
        $root = CulturalObject::factory()->create();
        VillageSettings::get()->update(['house_cultural_object_id' => $root->id]);

        $this->seed(PekaranganCulturalObjectSeeder::class);

        $this->assertDatabaseCount('cultural_objects', 1);
        $this->assertNull(VillageSettings::get()->featured_cultural_object_id);
    }

    public function test_ambiguous_existing_parents_are_reported_without_changing_settings_or_content(): void
    {
        CulturalObject::factory()->create(['name' => ['id' => 'Pekarangan Rumah Adat', 'en' => 'Courtyard One']]);
        CulturalObject::factory()->create(['name' => ['id' => 'Pekarangan Rumah', 'en' => 'Courtyard Two']]);
        $failure = null;

        try {
            $this->seed(PekaranganCulturalObjectSeeder::class);
        } catch (\RuntimeException $exception) {
            $failure = $exception;
        }

        $this->assertNotNull($failure);
        $this->assertStringContainsString('beberapa calon induk pekarangan', $failure->getMessage());
        $this->assertDatabaseCount('cultural_objects', 2);
        $this->assertNull(VillageSettings::get()->house_cultural_object_id);
    }

    public function test_existing_child_with_the_canonical_name_is_not_silently_promoted(): void
    {
        $parent = CulturalObject::factory()->create();
        $child = CulturalObject::factory()->create([
            'parent_id' => $parent->id, 'name' => ['id' => 'Pekarangan Rumah Adat', 'en' => 'Existing Courtyard'],
        ]);
        $failure = null;

        try {
            $this->seed(PekaranganCulturalObjectSeeder::class);
        } catch (\RuntimeException $exception) {
            $failure = $exception;
        }

        $this->assertNotNull($failure);
        $this->assertStringContainsString('masih menjadi objek anak', $failure->getMessage());
        $this->assertSame($parent->id, $child->fresh()->parent_id);
        $this->assertDatabaseCount('cultural_objects', 2);
    }

    public function test_seeder_reuses_existing_room_content_and_assets_without_moving_their_map_points(): void
    {
        $room = CulturalObject::factory()->create([
            'name' => ['id' => 'Dapur Rumah Tradisional (Paon)', 'en' => 'Traditional House Kitchen (Paon)'],
            'audio_narration_paths' => ['id' => 'audio/paon.mp3'], 'historical_images' => ['images/paon.jpg'],
        ]);
        $marker = ArModel::factory()->create(['cultural_object_id' => $room->id]);
        $location = $room->syncMapLocation(['category' => 'cultural', 'latitude' => -8.42, 'longitude' => 115.36]);
        $originalLocation = $location->fresh()->getAttributes();
        $gate = CulturalObject::factory()->create(['name' => ['id' => 'Gerbang Desa Penglipuran', 'en' => 'Village Gate']]);

        $this->seed(PekaranganCulturalObjectSeeder::class);
        $this->seed(PekaranganCulturalObjectSeeder::class);

        $root = VillageSettings::get()->houseCulturalObject;
        $this->assertSame($root->id, $room->fresh()->parent_id);
        $this->assertSame(['id' => 'audio/paon.mp3'], $room->fresh()->audio_narration_paths);
        $this->assertSame(['images/paon.jpg'], $room->fresh()->historical_images);
        $this->assertSame($room->id, $marker->fresh()->cultural_object_id);
        $this->assertSame($originalLocation, $location->fresh()->getAttributes());
        $this->assertNull($gate->fresh()->parent_id);
        $this->assertDatabaseCount('cultural_objects', 3);
    }

    public function test_duplicate_room_names_abort_without_creating_a_parent_or_changing_links(): void
    {
        CulturalObject::factory()->count(2)->create(['name' => ['id' => 'Paon', 'en' => 'Kitchen']]);
        $failure = null;

        try {
            $this->seed(PekaranganCulturalObjectSeeder::class);
        } catch (\RuntimeException $exception) {
            $failure = $exception;
        }

        $this->assertNotNull($failure);
        $this->assertStringContainsString('beberapa objek Paon', $failure->getMessage());
        $this->assertDatabaseCount('cultural_objects', 2);
        $this->assertSame(2, CulturalObject::roots()->count());
        $this->assertNull(VillageSettings::get()->house_cultural_object_id);
    }

    public function test_a_room_explicitly_selected_as_featured_is_kept_as_a_root(): void
    {
        $room = CulturalObject::factory()->create(['name' => ['id' => 'Paon', 'en' => 'Kitchen']]);
        VillageSettings::get()->update(['featured_cultural_object_id' => $room->id]);

        $this->seed(PekaranganCulturalObjectSeeder::class);

        $this->assertSame($room->id, VillageSettings::get()->featured_cultural_object_id);
        $this->assertNull($room->fresh()->parent_id);
        $this->assertDatabaseCount('cultural_objects', 2);
    }
}
