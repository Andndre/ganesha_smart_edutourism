<?php

namespace Tests\Feature;

use App\Models\TraditionalHouse;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HouseUmkmCardinalityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_second_profile_cannot_attach_to_an_occupied_house(): void
    {
        $primary = UmkmProfile::factory()->house()->create();
        $other = UmkmProfile::factory()->create();

        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))->put(route('admin.umkm.profile.update', $other->id), [
            'business_name' => $other->getTranslations('business_name'), 'owner_name' => $other->owner_name,
            'latitude' => -8.42, 'longitude' => 115.36, 'traditional_house_id' => $primary->traditional_house_id,
        ]);

        $response->assertSessionHasErrors(['traditional_house_id' => 'Rumah ini sudah memiliki profil UMKM. Satu rumah hanya dapat terhubung ke satu profil.']);
        $this->assertNull($other->fresh()->traditional_house_id);
        $this->assertSame($primary->id, $primary->traditionalHouse->fresh()->umkmProfile->id);
    }

    public function test_house_form_rejects_legacy_multiple_profile_input_and_keeps_the_original_link(): void
    {
        $primary = UmkmProfile::factory()->house()->create();
        $house = $primary->traditionalHouse;
        $other = UmkmProfile::factory()->create();

        $this->actingAs(User::factory()->create(['role' => 'admin']))->put(route('admin.houses.update', $house), [
            'number' => $house->number, 'head_name' => $house->head_name, 'male_count' => 2, 'female_count' => 3,
            'umkm_profile_ids' => [$primary->id, $other->id],
        ])->assertSessionHasErrors('umkm_profile_ids');

        $this->assertSame($primary->id, $house->fresh()->umkmProfile->id);
        $this->assertNull($other->fresh()->traditional_house_id);
    }

    public function test_unique_constraint_also_blocks_links_written_without_model_events(): void
    {
        $primary = UmkmProfile::factory()->house()->create();
        $other = UmkmProfile::factory()->create();

        $this->expectException(QueryException::class);
        DB::table('umkm_profiles')->where('id', $other->id)->update(['traditional_house_id' => $primary->traditional_house_id]);
    }

    public function test_model_guard_rejects_a_second_profile_before_the_database_constraint(): void
    {
        $primary = UmkmProfile::factory()->house()->create();
        $other = UmkmProfile::factory()->create();

        $this->expectException(ValidationException::class);
        $other->update(['traditional_house_id' => $primary->traditional_house_id]);
    }

    public function test_migration_keeps_the_house_profile_and_detaches_additional_businesses_without_deleting_data(): void
    {
        $migration = require database_path('migrations/2026_11_25_105755_enforce_one_umkm_profile_per_traditional_house.php');
        $migration->down();
        $primary = UmkmProfile::factory()->house()->create();
        $extra = UmkmProfile::factory()->create();
        DB::table('umkm_profiles')->where('id', $extra->id)->update(['traditional_house_id' => $primary->traditional_house_id]);
        $original = $extra->fresh()->getAttributes();

        $migration->up();

        $this->assertSame($primary->traditional_house_id, $primary->fresh()->traditional_house_id);
        $this->assertNull($extra->fresh()->traditional_house_id);
        $remaining = $extra->fresh()->getAttributes();
        unset($original['traditional_house_id'], $remaining['traditional_house_id']);
        $this->assertSame($original, $remaining);
        $this->assertDatabaseCount('umkm_profiles', 2);
    }

    public function test_ambiguous_legacy_duplicates_abort_before_any_links_are_removed(): void
    {
        $migration = require database_path('migrations/2026_11_25_105755_enforce_one_umkm_profile_per_traditional_house.php');
        $migration->down();
        $house = TraditionalHouse::factory()->create();
        $first = UmkmProfile::factory()->create(['traditional_house_id' => $house->id]);
        $second = UmkmProfile::factory()->create();
        DB::table('umkm_profiles')->where('id', $second->id)->update(['traditional_house_id' => $house->id]);
        $failure = null;

        try {
            $migration->up();
        } catch (\RuntimeException $exception) {
            $failure = $exception;
        }

        $this->assertNotNull($failure);
        $this->assertStringContainsString((string) $house->id, $failure->getMessage());
        $this->assertSame($house->id, $first->fresh()->traditional_house_id);
        $this->assertSame($house->id, $second->fresh()->traditional_house_id);
    }

    public function test_duplicate_primary_house_profiles_abort_migration_without_choosing_an_owner(): void
    {
        $migration = require database_path('migrations/2026_11_25_105755_enforce_one_umkm_profile_per_traditional_house.php');
        $migration->down();
        $primary = UmkmProfile::factory()->house()->create();
        $other = UmkmProfile::factory()->create();
        DB::table('umkm_profiles')->where('id', $other->id)->update([
            'traditional_house_id' => $primary->traditional_house_id, 'profile_type' => 'house',
        ]);
        $failure = null;

        try {
            $migration->up();
        } catch (\RuntimeException $exception) {
            $failure = $exception;
        }

        $this->assertNotNull($failure);
        $this->assertStringContainsString((string) $primary->traditional_house_id, $failure->getMessage());
        $this->assertSame($primary->user_id, $primary->traditionalHouse->fresh()->owner_user_id);
        $this->assertSame($primary->traditional_house_id, $other->fresh()->traditional_house_id);
    }
}
