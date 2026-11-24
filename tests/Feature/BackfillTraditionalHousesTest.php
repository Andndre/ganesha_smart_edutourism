<?php

namespace Tests\Feature;

use App\Models\MapLocation;
use App\Models\TraditionalHouse;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use Database\Seeders\PenglipuranHouseUmkmSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class BackfillTraditionalHousesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_publishes_the_house_and_preserves_the_original_business_data(): void
    {
        $profile = $this->legacyProfile(['en' => 'House No. 13', 'id' => 'Rumah No. 13'], [
            'owner_name' => 'I Wayan Sujana',
            'image_path' => 'umkm/house-13.webp',
        ]);
        $location = $this->locate($profile);
        $product = UmkmProduct::factory()->create(['umkm_profile_id' => $profile->id]);
        $profileBefore = $profile->fresh()->getRawOriginal();
        $ownerBefore = $profile->user->fresh()->getRawOriginal();
        $productBefore = $product->fresh()->getRawOriginal();
        $locationBefore = $location->fresh()->getRawOriginal();
        foreach (['explore', 'umkm', 'cultural'] as $tag) {
            Cache::tags([$tag])->put('legacy-house-test', 'stale');
        }

        $this->migration()->up();

        $house = TraditionalHouse::where('number', 13)->sole();
        $this->assertDatabaseHas('traditional_houses', [
            'id' => $house->id,
            'head_name' => 'I Wayan Sujana',
            'cover_image_path' => 'umkm/house-13.webp',
            'is_published' => true,
            'is_open_to_visitors' => false,
            'male_count' => 0,
            'female_count' => 0,
        ]);
        $this->assertSame($house->id, $profile->fresh()->traditional_house_id);
        $this->assertSame(-8.42345678, $house->mapLocation->latitude);
        $this->assertSame(115.3654321, $house->mapLocation->longitude);
        $this->assertSame('cultural', $house->mapLocation->category);
        $this->assertSame('Pekarangan No. 13', $house->mapLocation->name);
        $this->assertFalse($house->mapLocation->is_accessible);
        $this->assertSame(['id' => 'Ada tangga', 'en' => 'Steps present'], $house->mapLocation->getTranslations('accessibility_notes'));
        $this->assertSame($locationBefore, $location->fresh()->getRawOriginal());
        $this->assertSame($ownerBefore, $profile->user->fresh()->getRawOriginal());
        $this->assertSame($productBefore, $product->fresh()->getRawOriginal());
        $profileAfter = $profile->fresh()->getRawOriginal();
        unset($profileBefore['traditional_house_id'], $profileBefore['updated_at'], $profileAfter['traditional_house_id'], $profileAfter['updated_at']);
        $this->assertSame($profileBefore, $profileAfter);
        foreach (['explore', 'umkm', 'cultural'] as $tag) {
            $this->assertNull(Cache::tags([$tag])->get('legacy-house-test'));
        }

        $this->get('/rumah-adat')->assertOk()->assertSee('Pekarangan #13');
        $this->get('/rumah-adat/13')->assertOk()->assertSee('I Wayan Sujana');
        $this->get(route('umkm.store', ['id' => $profile->id]))->assertOk()->assertSee('Bagian dari Pekarangan No.');
        $this->get(route('explore'))->assertOk()->assertSee('Pekarangan No. 13');
    }

    #[DataProvider('recognizedNames')]
    public function test_it_recognizes_complete_legacy_house_names(array|string $name, int $number): void
    {
        $profile = $this->legacyProfile($name);

        $this->migration()->up();

        $this->assertSame($number, $profile->fresh()->traditionalHouse->number);
    }

    public static function recognizedNames(): array
    {
        return [
            'english translation' => [['en' => 'House No. 13'], 13],
            'indonesian translation' => [['id' => 'Rumah No. 24'], 24],
            'plain legacy text' => ['House No. 31', 31],
            'case and surrounding whitespace' => ['  rUmAh No. 018  ', 18],
            'json string' => ['"House No. 72"', 72],
            'number beyond old seed count' => [['en' => 'House No. 9999'], 9999],
        ];
    }

    #[DataProvider('unrelatedNames')]
    public function test_it_leaves_unrelated_umkm_without_a_house(array|string $name): void
    {
        $profile = $this->legacyProfile($name);
        $this->locate($profile);

        $this->migration()->up();

        $this->assertDatabaseCount('traditional_houses', 0);
        $this->assertNull($profile->fresh()->traditional_house_id);
        $this->assertDatabaseCount('map_locations', 1);
    }

    public static function unrelatedNames(): array
    {
        return [
            'cafe' => [['id' => 'Kafe Bambu', 'en' => 'Bamboo Cafe']],
            'prefix' => ['Cafe House No. 13'],
            'suffix' => ['House No. 13 Cafe'],
            'zero' => ['House No. 0'],
            'out of range' => ['House No. 10000'],
            'negative' => ['House No. -13'],
            'fraction' => ['House No. 13.5'],
        ];
    }

    public function test_existing_houses_locations_and_manual_links_survive_reruns_and_rollback(): void
    {
        $existing = TraditionalHouse::factory()->create(['number' => 13, 'is_published' => false]);
        $existing->syncMapLocation(['category' => 'cultural', 'latitude' => -8.4, 'longitude' => 115.3]);
        $linkedElsewhere = $this->legacyProfile('House No. 99', ['traditional_house_id' => $existing->id]);
        $profile = $this->legacyProfile('House No. 13');
        $this->locate($profile);
        $houseBefore = $existing->fresh()->getRawOriginal();
        $locationBefore = $existing->mapLocation->fresh()->getRawOriginal();
        $linkedBefore = $linkedElsewhere->getRawOriginal();

        $this->migration()->up();
        $this->migration()->up();
        $this->migration()->down();

        $this->assertDatabaseCount('traditional_houses', 1);
        $this->assertDatabaseCount('map_locations', 2);
        $this->assertSame($houseBefore, $existing->fresh()->getRawOriginal());
        $this->assertSame($locationBefore, $existing->mapLocation->fresh()->getRawOriginal());
        $this->assertSame($linkedBefore, $linkedElsewhere->fresh()->getRawOriginal());
        $this->assertSame($existing->id, $profile->fresh()->traditional_house_id);
        $this->get('/rumah-adat/13')->assertNotFound();
    }

    public function test_it_adds_a_missing_location_without_publishing_an_existing_draft(): void
    {
        $house = TraditionalHouse::factory()->create(['number' => 13, 'is_published' => false]);
        $profile = $this->legacyProfile('House No. 13');
        $this->locate($profile);

        $this->migration()->up();

        $this->assertSame(-8.42345678, $house->fresh()->mapLocation->latitude);
        $this->assertFalse($house->fresh()->is_published);
        $this->assertSame($house->id, $profile->fresh()->traditional_house_id);
    }

    public function test_inactive_umkm_and_missing_or_invalid_coordinates_produce_linked_drafts(): void
    {
        $inactive = $this->legacyProfile('House No. 13', ['is_active' => false, 'owner_name' => '   ']);
        $this->locate($inactive);
        $missing = $this->legacyProfile('House No. 14');
        $invalid = $this->legacyProfile('House No. 15');
        $invalidLocation = $this->locate($invalid);
        DB::table('map_locations')->where('id', $invalidLocation->id)->update(['latitude' => 91]);

        $this->migration()->up();

        $this->assertDatabaseHas('traditional_houses', ['number' => 13, 'head_name' => 'Belum diisi', 'is_published' => false]);
        $this->assertNotNull($inactive->fresh()->traditionalHouse->mapLocation);
        $this->assertNull($missing->fresh()->traditionalHouse->mapLocation);
        $this->assertNull($invalid->fresh()->traditionalHouse->mapLocation);
        $this->assertSame(3, TraditionalHouse::count());
        $this->assertSame(0, TraditionalHouse::published()->count());
        $this->get('/rumah-adat/13')->assertNotFound();
    }

    public function test_conflicting_translations_stop_the_entire_backfill_before_writing(): void
    {
        $valid = $this->legacyProfile('House No. 12');
        $conflicting = $this->legacyProfile(['id' => 'Rumah No. 13', 'en' => 'House No. 14']);

        try {
            $this->migration()->up();
            $this->fail('Migrasi harus menolak nomor berbeda antarbahasa.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString("UMKM ID {$conflicting->id}", $exception->getMessage());
        }

        $this->assertDatabaseCount('traditional_houses', 0);
        $this->assertNull($valid->fresh()->traditional_house_id);
        $this->assertNull($conflicting->fresh()->traditional_house_id);
    }

    public function test_duplicate_candidates_report_both_ids_without_partial_changes(): void
    {
        $valid = $this->legacyProfile('House No. 12');
        $first = $this->legacyProfile('House No. 13');
        $second = $this->legacyProfile('Rumah No. 13');

        try {
            $this->migration()->up();
            $this->fail('Migrasi harus menolak kandidat ganda.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString("UMKM ID {$first->id} dan {$second->id}", $exception->getMessage());
        }

        $this->assertDatabaseCount('traditional_houses', 0);
        $this->assertDatabaseCount('map_locations', 0);
        $this->assertNull($valid->fresh()->traditional_house_id);
        $this->assertNull($first->fresh()->traditional_house_id);
        $this->assertNull($second->fresh()->traditional_house_id);
    }

    public function test_rerunning_does_not_duplicate_new_houses_or_reset_their_edits(): void
    {
        $profile = $this->legacyProfile('House No. 13');
        $this->locate($profile);
        $this->migration()->up();
        $house = $profile->fresh()->traditionalHouse;
        $house->update(['head_name' => 'Nama terverifikasi', 'is_published' => false]);
        $house->mapLocation()->update(['latitude' => -8.41]);

        $this->migration()->up();
        $this->migration()->down();

        $this->assertDatabaseCount('traditional_houses', 1);
        $this->assertDatabaseCount('map_locations', 2);
        $this->assertSame('Nama terverifikasi', $house->fresh()->head_name);
        $this->assertFalse($house->fresh()->is_published);
        $this->assertSame(-8.41, $house->mapLocation()->sole()->latitude);
    }

    public function test_it_connects_all_72_legacy_seeded_house_businesses_without_creating_more_umkm(): void
    {
        UmkmProductCategory::factory()->count(4)->create();
        $this->seed(PenglipuranHouseUmkmSeeder::class);
        $productCount = UmkmProduct::count();

        $this->migration()->up();
        $this->migration()->up();

        $this->assertDatabaseCount('umkm_profiles', 72);
        $this->assertDatabaseCount('traditional_houses', 72);
        $this->assertDatabaseCount('map_locations', 144);
        $this->assertSame(72, TraditionalHouse::published()->count());
        $this->assertSame(0, UmkmProfile::whereNull('traditional_house_id')->count());
        $this->assertDatabaseCount('umkm_products', $productCount);
        $this->get(route('houses.index'))->assertOk()
            ->assertViewHas('houses', fn ($houses) => $houses->count() === 72)
            ->assertSee('Pekarangan #72');
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_11_24_001133_backfill_traditional_houses_from_legacy_umkm_profiles.php');
    }

    /** @param array<string, mixed> $attributes */
    private function legacyProfile(array|string $name, array $attributes = []): UmkmProfile
    {
        $profile = UmkmProfile::factory()->create($attributes);
        DB::table('umkm_profiles')->where('id', $profile->id)->update([
            'business_name' => is_array($name) ? json_encode($name, JSON_THROW_ON_ERROR) : $name,
        ]);

        return $profile->fresh();
    }

    private function locate(UmkmProfile $profile): MapLocation
    {
        return $profile->syncMapLocation([
            'category' => 'umkm',
            'latitude' => -8.42345678,
            'longitude' => 115.36543210,
            'is_accessible' => false,
            'accessibility_notes' => ['id' => 'Ada tangga', 'en' => 'Steps present'],
        ]);
    }
}
