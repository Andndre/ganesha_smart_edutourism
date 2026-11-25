<?php

namespace Tests\Feature;

use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Models\TraditionalHouse;
use App\Models\UmkmProfile;
use App\Models\User;
use App\Models\VillageSettings;
use App\Models\WeatherReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SharedHouseCultureTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_opens_and_publishes_all_existing_houses(): void
    {
        $closed = TraditionalHouse::factory()->create(['is_open_to_visitors' => false, 'is_published' => false]);
        $open = TraditionalHouse::factory()->create(['is_open_to_visitors' => true, 'is_published' => true]);
        Cache::tags(['cultural'])->put('house-publication-test', 'stale');
        Cache::tags(['explore'])->put('house-publication-test', 'stale');
        $migration = require database_path('migrations/2026_11_25_120055_publish_and_open_existing_traditional_houses.php');

        $migration->up();

        foreach ([$closed, $open] as $house) {
            $this->assertDatabaseHas('traditional_houses', [
                'id' => $house->id,
                'is_open_to_visitors' => true,
                'is_published' => true,
            ]);
        }
        $this->assertNull(Cache::tags(['cultural'])->get('house-publication-test'));
        $this->assertNull(Cache::tags(['explore'])->get('house-publication-test'));
    }

    public function test_every_house_automatically_shares_the_selected_courtyard_rooms_and_models(): void
    {
        $root = CulturalObject::factory()->create(['name' => ['id' => 'Pekarangan Rumah Adat', 'en' => 'Traditional Courtyard']]);
        $room = CulturalObject::factory()->create(['parent_id' => $root->id]);
        $unrelated = CulturalObject::factory()->create();
        $model = ArModel::factory()->create(['cultural_object_id' => $room->id]);
        VillageSettings::get()->update(['house_cultural_object_id' => $root->id]);

        foreach ([18, 24] as $number) {
            $house = $this->publishedHouse($number);
            $this->get(route('houses.show', $number))->assertOk()
                ->assertSee('Pekarangan Rumah Adat')
                ->assertSee(route('cultural-object', ['slug' => $room->slug]), false)
                ->assertSee(route('ar-scan', ['model_id' => $model->id]), false)
                ->assertDontSee(route('cultural-object', ['slug' => $unrelated->slug]), false);
            $this->get(route('ar-scan-redirect', ['arMarkerId' => $house->ar_marker_id]))
                ->assertRedirect(route('houses.show', $number));
        }
    }

    public function test_changing_the_shared_parent_updates_existing_house_pages(): void
    {
        $this->publishedHouse(18);
        $oldRoot = CulturalObject::factory()->create();
        $oldRoom = CulturalObject::factory()->create(['parent_id' => $oldRoot->id]);
        $newRoot = CulturalObject::factory()->create();
        $newRoom = CulturalObject::factory()->create(['parent_id' => $newRoot->id]);
        $settings = VillageSettings::get();
        $settings->update(['house_cultural_object_id' => $oldRoot->id]);
        $this->get(route('houses.show', 18))->assertOk()->assertSee(route('cultural-object', ['slug' => $oldRoom->slug]), false);

        $settings->update(['house_cultural_object_id' => $newRoot->id]);

        $this->get(route('houses.show', 18))->assertOk()
            ->assertSee(route('cultural-object', ['slug' => $newRoom->slug]), false)
            ->assertDontSee(route('cultural-object', ['slug' => $oldRoom->slug]), false);
    }

    public function test_an_unconfigured_house_shows_an_empty_state_without_unrelated_cultural_objects(): void
    {
        $this->publishedHouse(18);
        $unrelated = CulturalObject::factory()->create();

        $this->get(route('houses.show', 18))->assertOk()
            ->assertSee('Informasi ruang budaya sedang dilengkapi.')
            ->assertDontSee(route('cultural-object', ['slug' => $unrelated->slug]), false);
    }

    public function test_admin_and_owner_forms_show_automatic_rooms_without_per_house_selection(): void
    {
        $root = CulturalObject::factory()->create();
        $room = CulturalObject::factory()->create(['parent_id' => $root->id]);
        VillageSettings::get()->update(['house_cultural_object_id' => $root->id]);
        $profile = UmkmProfile::factory()->house()->create();

        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.houses.create'))
            ->assertOk()->assertDontSee('name="cultural_object_ids[]"', false);
        $this->get(route('admin.houses.edit', $profile->traditional_house_id))->assertOk()
            ->assertSee('Ruang Budaya Otomatis')->assertSee($room->getTranslation('name', 'id'))
            ->assertDontSee('name="cultural_object_ids[]"', false);
        $this->actingAs($profile->user)->get(route('owner.house'))->assertOk()
            ->assertSee('Ruang Budaya Otomatis')->assertSee($room->getTranslation('name', 'id'))
            ->assertDontSee('name="cultural_object_ids[]"', false);
    }

    public function test_owners_cannot_override_the_shared_cultural_content(): void
    {
        $profile = UmkmProfile::factory()->house()->create();
        $room = CulturalObject::factory()->create();

        $this->actingAs($profile->user)->put(route('owner.house.update'), [
            'number' => $profile->traditionalHouse->number,
            'head_name' => 'Nama pengganti', 'male_count' => 2, 'female_count' => 3,
            'cultural_object_ids' => [$room->id],
        ])->assertSessionHasErrors('cultural_object_ids')
            ->assertSessionHasErrors(['cultural_object_ids' => 'Ruang budaya mengikuti pengaturan desa dan tidak perlu dipilih per rumah.']);

        $this->assertNotSame('Nama pengganti', $profile->traditionalHouse->fresh()->head_name);
        $this->assertNull(VillageSettings::get()->house_cultural_object_id);
    }

    public function test_home_counts_all_houses_including_unpublished_and_incomplete_profiles(): void
    {
        WeatherReport::create(['temperature' => 25, 'condition' => 'Cerah', 'weather_code' => 0, 'humidity' => 80, 'wind_speed' => 2]);
        $this->publishedHouse(1);
        TraditionalHouse::factory()->create(['number' => 2, 'is_published' => true]);
        TraditionalHouse::factory()->create(['number' => 3, 'is_published' => false]);

        $this->get(route('home'))->assertOk()
            ->assertViewHas('houseCount', 3)
            ->assertSee('Direktori 3 Pekarangan');
    }

    public function test_featured_selection_controls_home_and_is_first_on_the_cultural_index(): void
    {
        WeatherReport::create(['temperature' => 25, 'condition' => 'Cerah', 'weather_code' => 0, 'humidity' => 80, 'wind_speed' => 2]);
        $other = CulturalObject::factory()->create(['name' => ['id' => 'AAA Pura Desa', 'en' => 'AAA Temple']]);
        $featured = CulturalObject::factory()->create(['name' => ['id' => 'Pekarangan Rumah Adat', 'en' => 'Traditional Courtyard']]);
        CulturalObject::factory()->create(['parent_id' => $other->id]);
        VillageSettings::get()->update(['featured_cultural_object_id' => $featured->id]);

        $this->get(route('home'))->assertOk()->assertViewHas('featuredCultural', fn ($object) => $object->is($featured));
        $response = $this->get(route('cultural-objects'))->assertOk()->assertSee('Unggulan');

        $this->assertSame([$featured->id, $other->id], array_column($response->viewData('objects'), 'id'));
        $this->assertTrue($response->viewData('objects')[0]['is_featured']);
        $this->assertFalse($response->viewData('objects')[1]['is_featured']);
    }

    public function test_changing_or_clearing_the_featured_selection_refreshes_the_cached_listing(): void
    {
        WeatherReport::create(['temperature' => 25, 'condition' => 'Cerah', 'weather_code' => 0, 'humidity' => 80, 'wind_speed' => 2]);
        $first = CulturalObject::factory()->create();
        $second = CulturalObject::factory()->create();
        $settings = VillageSettings::get();
        $settings->update(['house_cultural_object_id' => $first->id, 'featured_cultural_object_id' => $first->id]);
        $this->get(route('cultural-objects'))->assertOk();
        Cache::tags(['cultural'])->put('settings-cache-probe', 'old');

        $settings->update(['featured_cultural_object_id' => $second->id]);

        $this->assertNull(Cache::tags(['cultural'])->get('settings-cache-probe'));
        $response = $this->get(route('cultural-objects'))->assertOk();
        $this->assertSame($second->id, $response->viewData('objects')[0]['id']);
        $this->assertSame($first->id, $settings->fresh()->house_cultural_object_id);
        $settings->update(['featured_cultural_object_id' => null]);
        $this->get(route('home'))->assertOk()->assertViewHas('featuredCultural', null)
            ->assertDontSee('id="featured-culture-heading"', false);
        $response = $this->get(route('cultural-objects'))->assertOk();
        $this->assertSame([], array_filter(array_column($response->viewData('objects'), 'is_featured')));
    }

    #[DataProvider('globalParentFields')]
    public function test_configured_parents_cannot_be_moved_under_another_object_or_deleted(string $field): void
    {
        $root = CulturalObject::factory()->create();
        $other = CulturalObject::factory()->create();
        VillageSettings::get()->update([$field => $root->id]);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->put(route('admin.cultural-objects.update', $root->id), [
            'name' => $root->getTranslations('name'), 'category' => $root->category, 'parent_id' => $other->id,
        ])->assertSessionHasErrors('parent_id');
        $this->delete(route('admin.cultural-objects.destroy', $root->id))->assertSessionHasErrors('destroy');

        $this->assertNull($root->fresh()->parent_id);
        $this->assertModelExists($root);
    }

    public static function globalParentFields(): array
    {
        return [
            'shared house parent' => ['house_cultural_object_id'],
            'featured parent' => ['featured_cultural_object_id'],
        ];
    }

    #[DataProvider('legacyCourtyardSources')]
    public function test_migration_reuses_an_existing_courtyard_and_preserves_content_assets_and_markers(?string $placeType): void
    {
        $migration = require database_path('migrations/2026_11_25_103533_use_shared_house_culture_and_featured_cultural_object.php');
        $migration->down();
        $root = CulturalObject::factory()->create(['place_type' => $placeType]);
        $room = CulturalObject::factory()->create(['parent_id' => $root->id, 'audio_narration_paths' => ['id' => 'audio/paon.mp3']]);
        $model = ArModel::factory()->create(['cultural_object_id' => $room->id, 'ar_marker_id' => 'PAON_EXISTING']);
        $house = TraditionalHouse::factory()->create(['ar_marker_id' => 'HOUSE_EXISTING']);
        DB::table('cultural_object_traditional_house')->insert(['traditional_house_id' => $house->id, 'cultural_object_id' => $room->id]);

        $migration->up();

        $this->assertFalse(Schema::hasTable('cultural_object_traditional_house'));
        $this->assertSame($root->id, VillageSettings::get()->house_cultural_object_id);
        $this->assertSame($root->id, VillageSettings::get()->featured_cultural_object_id);
        $this->assertSame('HOUSE_EXISTING', $house->fresh()->ar_marker_id);
        $this->assertSame('PAON_EXISTING', $model->fresh()->ar_marker_id);
        $this->assertSame($root->id, $room->fresh()->parent_id);
        $this->assertSame(['id' => 'audio/paon.mp3'], $room->fresh()->audio_narration_paths);
    }

    public static function legacyCourtyardSources(): array
    {
        return [
            'explicit courtyard type' => ['pekarangan'],
            'common parent of existing house selections' => [null],
        ];
    }

    public function test_migration_does_not_guess_when_multiple_courtyards_exist(): void
    {
        $migration = require database_path('migrations/2026_11_25_103533_use_shared_house_culture_and_featured_cultural_object.php');
        $migration->down();
        $roots = CulturalObject::factory()->count(2)->create(['place_type' => 'pekarangan']);
        foreach ($roots as $root) {
            CulturalObject::factory()->create(['parent_id' => $root->id]);
        }

        $migration->up();

        $this->assertNull(VillageSettings::get()->house_cultural_object_id);
        $this->assertNull(VillageSettings::get()->featured_cultural_object_id);
        $this->assertDatabaseCount('cultural_objects', 4);
    }

    private function publishedHouse(int $number): TraditionalHouse
    {
        $house = TraditionalHouse::factory()->create(['number' => $number, 'is_published' => true, 'ar_marker_id' => 'HOUSE_'.$number]);
        $house->syncMapLocation(['category' => 'cultural', 'latitude' => -8.42, 'longitude' => 115.36]);
        UmkmProfile::factory()->create(['traditional_house_id' => $house->id, 'is_active' => true]);

        return $house;
    }
}
