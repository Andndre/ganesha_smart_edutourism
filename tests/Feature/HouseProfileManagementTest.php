<?php

namespace Tests\Feature;

use App\Enums\UmkmProfileType;
use App\Models\CulturalObject;
use App\Models\TraditionalHouse;
use App\Models\UmkmProfile;
use App\Models\User;
use App\Models\VillageSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HouseProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_house_account_has_a_house_dashboard_and_no_manual_business_name(): void
    {
        $profile = UmkmProfile::factory()->house()->create();
        $this->actingAs($profile->user)->get(route('owner.dashboard'))->assertOk()->assertSee('Rumah Saya')->assertSee('Rumah + UMKM')->assertDontSee('Kustomisasi Lokasi');
        $this->get(route('owner.house'))->assertOk()->assertSee('Jumlah laki-laki')->assertDontSee('name="owner_user_id"', false);
        $this->get(route('owner.profile'))->assertOk()->assertSee('Nama UMKM Rumah')->assertDontSee('name="business_name[id]"', false);
        $this->put(route('owner.profile.update'), ['description' => ['id' => 'Produk keluarga', 'en' => 'Family products']])->assertRedirect();
        $this->assertSame('Produk keluarga', $profile->fresh()->description);
        $this->put(route('owner.profile.update'), ['business_name' => ['id' => 'Kafe', 'en' => 'Cafe']])->assertSessionHasErrors('business_name');
        $this->assertSame(UmkmProfileType::House, $profile->fresh()->profile_type);
        $this->get(route('owner.location'))->assertRedirect(route('owner.house'));
    }

    public function test_standalone_cafe_cannot_manage_another_house_or_use_an_admin_preview_parameter(): void
    {
        $houseProfile = UmkmProfile::factory()->house()->create();
        $cafe = UmkmProfile::factory()->create();
        $this->actingAs($cafe->user)->get(route('owner.house', ['umkm_profile_id' => $houseProfile->id]))->assertForbidden();
        $this->get(route('owner.dashboard'))->assertOk()->assertDontSee('Kelola Rumah Saya')->assertSee('Kustomisasi Lokasi');
        $this->put(route('owner.house.update'), $this->houseData(100))->assertForbidden();
        $this->assertNotSame(100, $houseProfile->traditionalHouse->number);
    }

    public function test_owner_changes_only_their_house_and_shared_cultural_content_follows(): void
    {
        $profile = UmkmProfile::factory()->house()->create();
        $other = UmkmProfile::factory()->house()->create();
        $slug = $profile->slug;
        $object = CulturalObject::factory()->create();
        $root = CulturalObject::factory()->create();
        $object->update(['parent_id' => $root->id]);
        VillageSettings::get()->update(['house_cultural_object_id' => $root->id]);
        $this->actingAs($profile->user)->put(route('owner.house.update', ['house' => $other->traditional_house_id]), $this->houseData(993))
            ->assertRedirect(route('owner.house'));

        $profile->refresh();
        $this->assertEquals(['id' => 'Rumah No. 993', 'en' => 'House No. 993'], $profile->getTranslations('business_name'));
        $this->assertSame($slug, $profile->slug);
        $house = $profile->traditionalHouse;
        $this->assertSame(993, $house->number);
        $this->assertTrue($house->is_published);
        $this->assertSame($house->mapLocation->latitude, $profile->mapLocation->latitude);
        $this->assertSame($house->mapLocation->longitude, $profile->mapLocation->longitude);
        $this->assertNotSame(993, $other->traditionalHouse->number);
        auth()->logout();
        $this->get(route('houses.show', 993))->assertOk()->assertSee(route('cultural-object', ['slug' => $object->slug]), false);
    }

    public function test_owner_cannot_set_management_fields_or_link_other_umkm(): void
    {
        $profile = UmkmProfile::factory()->house()->create();
        $other = UmkmProfile::factory()->create();
        $this->actingAs($profile->user)->put(route('owner.house.update'), [
            ...$this->houseData(994), 'owner_user_id' => $other->user_id,
            'umkm_profile_ids' => [$other->id], 'profile_type' => 'business',
        ])->assertSessionHasErrors(['owner_user_id', 'umkm_profile_ids', 'profile_type']);
        $this->assertSame($profile->user_id, $profile->traditionalHouse->owner_user_id);
        $this->assertNull($other->fresh()->traditional_house_id);
        $this->delete('/owner/house')->assertMethodNotAllowed();
    }

    #[DataProvider('nonOwnerRoles')]
    public function test_other_roles_cannot_edit_owner_house(string $role): void
    {
        $this->actingAs(User::factory()->create(['role' => $role]))->get(route('owner.house'))->assertForbidden();
    }

    public static function nonOwnerRoles(): array
    {
        return [['tourist'], ['ticket_officer'], ['admin_viewer']];
    }

    public function test_admin_creates_a_house_and_its_automatic_umkm_for_a_new_owner(): void
    {
        $owner = User::factory()->create(['role' => 'umkm_owner']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->post(route('admin.houses.store'), [
            ...$this->houseData(89), 'owner_user_id' => $owner->id,
        ])->assertRedirect();
        $house = TraditionalHouse::where('number', 89)->sole();
        $profile = $owner->umkmProfile()->sole();
        $this->assertSame($owner->id, $house->owner_user_id);
        $this->assertTrue($profile->isHouse());
        $this->assertSame($house->id, $profile->traditional_house_id);
        $this->assertSame('House No. 89', $profile->getTranslation('business_name', 'en'));
        $this->assertNotNull($profile->mapLocation);
    }

    public function test_admin_transfers_management_and_old_owner_loses_access(): void
    {
        $profile = UmkmProfile::factory()->house()->create();
        $oldOwner = $profile->user;
        $newOwner = User::factory()->create(['role' => 'umkm_owner']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.houses.update', $profile->traditional_house_id), [
            ...$this->houseData(95), 'owner_user_id' => $newOwner->id,
        ])->assertRedirect();
        $this->assertSame($newOwner->id, $profile->fresh()->user_id);
        $this->actingAs($oldOwner)->get(route('owner.house'))->assertForbidden();
        $this->actingAs($newOwner)->get(route('owner.house'))->assertOk();
        $this->actingAs($admin)->get(route('owner.house', ['umkm_profile_id' => $profile->id]))->assertOk()->assertSee('Rumah Saya')->assertDontSee('Kustomisasi Lokasi');
    }

    public function test_admin_cannot_assign_the_same_account_to_two_houses_and_transaction_is_rolled_back(): void
    {
        $profile = UmkmProfile::factory()->house()->create();
        $house = TraditionalHouse::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->put(route('admin.houses.update', $house), [
            ...$this->houseData(98), 'owner_user_id' => $profile->user_id,
        ])->assertSessionHasErrors('owner_user_id');
        $this->assertNotSame(98, $house->fresh()->number);
    }

    public function test_house_publication_requires_active_umkm_and_coordinates_and_number_is_unique(): void
    {
        $profile = UmkmProfile::factory()->house()->create(['is_active' => false]);
        $other = TraditionalHouse::factory()->create();
        $this->actingAs($profile->user)->put(route('owner.house.update'), [
            ...$this->houseData($other->number), 'latitude' => null, 'longitude' => null,
        ])->assertSessionHasErrors(['number', 'latitude', 'longitude', 'is_published']);
    }

    public function test_house_and_umkm_have_one_public_marker_and_cafe_retains_its_marker(): void
    {
        $house = TraditionalHouse::factory()->create(['is_published' => true]);
        $profile = UmkmProfile::factory()->house($house)->create();
        $profile->syncMapLocation(['category' => 'umkm', 'latitude' => -8.42, 'longitude' => 115.36]);
        $cafe = UmkmProfile::factory()->create();
        $cafe->syncMapLocation(['category' => 'umkm', 'latitude' => -8.42, 'longitude' => 115.36]);
        $locations = $this->get(route('explore'))->assertOk()->viewData('locations');
        $this->assertCount(2, $locations);
        $marker = collect($locations)->firstWhere('place_type', 'rumah');
        $this->assertSame(['cultural', 'umkm'], $marker['categories']);
        $this->assertEqualsCanonicalizing([$house->mapLocation->id, $profile->mapLocation->id], $marker['map_location_ids']);
        $this->assertSame(route('umkm.store', $profile->id), $marker['umkm_url']);
        $this->assertSame(route('houses.show', $house->number), $marker['detail_url']);
        $adminLocations = $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.map-manager'))->assertOk()->viewData('locations');
        $this->assertCount(2, $adminLocations);
    }

    public function test_draft_house_exposes_only_its_active_umkm_marker_and_profile(): void
    {
        $profile = UmkmProfile::factory()->house()->create();
        $profile->syncMapLocation(['category' => 'umkm', 'latitude' => -8.42, 'longitude' => 115.36]);
        $locations = $this->get(route('explore'))->assertOk()->viewData('locations');
        $this->assertCount(1, $locations);
        $this->assertSame('rumah', $locations[0]['place_type']);
        $this->assertSame(['umkm'], $locations[0]['categories']);
        $this->assertSame(route('umkm.store', $profile->id), $locations[0]['detail_url']);
        $this->get(route('houses.show', $profile->traditionalHouse->number))->assertNotFound();
    }

    public function test_map_manager_moves_synchronize_both_points_and_house_marker_has_a_house_glyph(): void
    {
        $profile = UmkmProfile::factory()->house()->create();
        $profile->syncMapLocation(['category' => 'umkm', 'latitude' => -8.42, 'longitude' => 115.36]);
        $point = $profile->mapLocation;
        $point->update(['latitude' => -8.43]);
        $this->assertSame(-8.43, $profile->traditionalHouse->mapLocation->latitude);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.map-manager'))
            ->assertOk()->assertSee("details?.profile_type === 'house' ? 'rumah'", false);
    }

    public function test_deleting_house_profile_revokes_access_and_unpublishes_house_but_does_not_delete_account(): void
    {
        $profile = UmkmProfile::factory()->house()->create();
        $house = $profile->traditionalHouse;
        $owner = $profile->user;
        $house->update(['is_published' => true]);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->delete(route('admin.houses.destroy', $house))->assertSessionHasErrors('house');
        $this->delete(route('admin.umkm.profile.destroy', $profile))->assertRedirect();
        $this->assertModelExists($owner);
        $this->assertNull($house->fresh()->owner_user_id);
        $this->assertFalse($house->fresh()->is_published);
        $this->actingAs($owner)->get(route('owner.house'))->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.houses.destroy', $house))->assertRedirect();
    }

    public function test_legacy_classification_grants_matching_house_accounts_preserves_cafes_and_is_repeatable(): void
    {
        $cardinality = require database_path('migrations/2026_11_25_105755_enforce_one_umkm_profile_per_traditional_house.php');
        $cardinality->down();
        $house = TraditionalHouse::factory()->create(['number' => 13]);
        $profile = UmkmProfile::factory()->create([
            'traditional_house_id' => $house->id,
            'business_name' => ['en' => 'House No. 13', 'id' => 'Rumah No.13'],
        ]);
        $cafe = UmkmProfile::withoutEvents(fn () => UmkmProfile::factory()->create(['traditional_house_id' => $house->id]));
        $migration = require database_path('migrations/2026_11_24_010220_classify_legacy_house_umkm_profiles.php');
        $migration->up();
        $migration->up();
        $this->assertTrue($profile->fresh()->isHouse());
        $this->assertSame('Rumah No. 13', $profile->fresh()->getTranslation('business_name', 'id'));
        $this->assertSame($profile->user_id, $house->fresh()->owner_user_id);
        $this->assertFalse($cafe->fresh()->isHouse());
    }

    public function test_legacy_conflicts_abort_before_any_access_is_granted(): void
    {
        $cardinality = require database_path('migrations/2026_11_25_105755_enforce_one_umkm_profile_per_traditional_house.php');
        $cardinality->down();
        $house = TraditionalHouse::factory()->create(['number' => 3]);
        $first = UmkmProfile::factory()->create(['traditional_house_id' => $house->id, 'business_name' => ['en' => 'House No. 3']]);
        UmkmProfile::withoutEvents(fn () => UmkmProfile::factory()->create(['traditional_house_id' => $house->id, 'business_name' => ['id' => 'Rumah No.3']]));
        $migration = require database_path('migrations/2026_11_24_010220_classify_legacy_house_umkm_profiles.php');
        try {
            $migration->up();
            $this->fail('Konflik harus menghentikan migrasi.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('ganda', $exception->getMessage());
        }
        $this->assertFalse($first->fresh()->isHouse());
        $this->assertNull($house->fresh()->owner_user_id);
    }

    public function test_classification_migrates_all_72_legacy_accounts_without_changing_slugs(): void
    {
        for ($number = 1; $number <= 72; $number++) {
            $house = TraditionalHouse::factory()->create(['number' => $number]);
            UmkmProfile::factory()->create([
                'traditional_house_id' => $house->id,
                'business_name' => ['id' => 'Rumah No.'.$number, 'en' => 'House No. '.$number],
            ]);
        }
        $slugs = UmkmProfile::orderBy('id')->pluck('slug')->all();
        $migration = require database_path('migrations/2026_11_24_010220_classify_legacy_house_umkm_profiles.php');
        $migration->up();
        $this->assertSame(72, UmkmProfile::where('profile_type', 'house')->count());
        $this->assertSame(72, TraditionalHouse::whereNotNull('owner_user_id')->count());
        $this->assertSame($slugs, UmkmProfile::orderBy('id')->pluck('slug')->all());
    }

    public function test_owner_can_remove_their_gallery_photo_and_update_shared_accessibility(): void
    {
        Storage::fake('public');
        $profile = UmkmProfile::factory()->house()->create();
        $house = $profile->traditionalHouse;
        $path = 'traditional_houses/old.webp';
        Storage::disk('public')->put($path, 'photo');
        $house->update(['gallery' => [$path]]);
        $this->actingAs($profile->user)->put(route('owner.house.update'), [
            ...$this->houseData(71), 'remove_gallery_images' => [$path],
            'is_accessible' => '0', 'accessibility_notes' => ['id' => 'Ada tangga', 'en' => 'Steps'],
        ])->assertRedirect();
        $this->assertSame([], $house->fresh()->gallery);
        Storage::disk('public')->assertMissing($path);
        $this->assertFalse($profile->mapLocation()->sole()->is_accessible);
        $this->assertSame('Steps', $profile->mapLocation()->sole()->getTranslation('accessibility_notes', 'en'));
        $this->put(route('owner.house.update'), [
            ...$this->houseData(71), 'remove_gallery_images' => ['traditional_houses/other-owner.webp'],
        ])->assertSessionHasErrors('remove_gallery_images.0');
        $this->put(route('owner.house.update'), [
            ...$this->houseData(71), 'remove_gallery_images' => [['invalid']], 'ar_marker_id' => ['invalid'],
        ])->assertSessionHasErrors(['remove_gallery_images.0', 'ar_marker_id']);
    }

    public function test_gallery_cannot_grow_past_eight_existing_photos(): void
    {
        Storage::fake('public');
        $profile = UmkmProfile::factory()->house()->create();
        $profile->traditionalHouse->update(['gallery' => array_map(fn (int $id): string => 'traditional_houses/'.$id.'.jpg', range(1, 8))]);
        $this->actingAs($profile->user)->put(route('owner.house.update'), [
            ...$this->houseData(72), 'gallery_images' => [UploadedFile::fake()->image('ninth.jpg')],
        ])->assertSessionHasErrors('gallery_images');
    }

    public function test_public_registration_cannot_request_house_privileges(): void
    {
        $house = TraditionalHouse::factory()->create();
        $this->post(route('mitra.register.store'), [
            'name' => 'Pemilik Cafe', 'business_name' => 'Cafe Bambu', 'phone' => '081234567890',
            'email' => 'cafe@example.test', 'password' => 'password123', 'password_confirmation' => 'password123',
            'profile_type' => 'house', 'traditional_house_id' => $house->id, 'owner_user_id' => 123,
        ])->assertRedirect(route('owner.dashboard'));
        $profile = UmkmProfile::sole();
        $this->assertFalse($profile->isHouse());
        $this->assertNull($profile->traditional_house_id);
        $this->assertNull($house->fresh()->owner_user_id);
        $this->get(route('owner.house'))->assertForbidden();
    }

    /** @return array<string, mixed> */
    private function houseData(int $number): array
    {
        return [
            'number' => $number, 'head_name' => 'Keluarga Wayan', 'male_count' => 2, 'female_count' => 3,
            'description' => ['id' => 'Pekarangan keluarga.', 'en' => 'Family courtyard.'],
            'latitude' => -8.42123, 'longitude' => 115.36123, 'is_published' => '1', 'is_open_to_visitors' => '1',
        ];
    }
}
