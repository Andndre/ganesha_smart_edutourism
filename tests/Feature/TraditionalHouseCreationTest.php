<?php

namespace Tests\Feature;

use App\Models\TraditionalHouse;
use App\Models\UmkmProduct;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TraditionalHouseCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_offers_one_location_type_picker_without_duplicate_buttons_or_dropdown(): void
    {
        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.map-manager'));
        $response->assertOk()->assertSee('Apa yang ingin ditambahkan?')->assertSee('Lanjutkan')
            ->assertSeeInOrder(['Pekarangan warga', 'Usaha', 'Objek budaya', 'Fasilitas umum'])
            ->assertDontSee('id="type-selector"', false)
            ->assertDontSee('id="map-create-house"', false)
            ->assertDontSee('id="map-create-business"', false);
        $this->assertSame(4, preg_match_all('/<input[^>]*name="map_location_type"/', $response->getContent()));
    }

    public function test_creation_form_has_three_steps_and_prefills_the_selected_map_point(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.houses.create', ['latitude' => -8.42345, 'longitude' => 115.36542]))
            ->assertOk()->assertViewIs('admin.houses.create')
            ->assertViewHas('prefill', ['latitude' => '-8.42345', 'longitude' => '115.36542'])
            ->assertSee('1. Data rumah')->assertSee('2. Lokasi rumah')->assertSee('3. Pengelola rumah')
            ->assertSee('Gunakan titik ini')->assertSee('Atur nanti')->assertSee('Periksa sebelum menyimpan')
            ->assertDontSee('name="umkm_profile_ids[]"', false);
    }

    public function test_minimal_draft_creates_a_house_and_inactive_umkm_without_an_owner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.houses.store'), $this->draft())
            ->assertRedirect()->assertSessionHas('success', fn ($message) => str_contains($message, 'UMKM Rumah sudah dibuat otomatis'));
        $house = TraditionalHouse::sole();
        $profile = $house->umkmProfile()->sole();
        $this->assertNull($house->owner_user_id);
        $this->assertNull($profile->user_id);
        $this->assertTrue($profile->isHouse());
        $this->assertFalse($profile->is_active);
        $this->assertFalse($house->is_published);
        $this->assertSame(0, $house->male_count);
        $this->assertSame('Rumah No. 73', $profile->getTranslation('business_name', 'id'));
        $this->assertDatabaseCount('users', 1);
        UmkmProduct::factory()->create([
            'umkm_profile_id' => $profile->id,
            'name' => ['id' => 'Produk Draf', 'en' => 'Draft Product'],
            'is_active' => true, 'stock' => null,
        ]);
        auth()->logout();
        $this->get(route('houses.show', 73))->assertNotFound();
        $this->get(route('umkm.store', $profile->id))->assertNotFound();
        $this->get(route('explore'))->assertOk()->assertViewHas('locations', fn ($locations) => count($locations) === 0);
        $this->getJson(route('umkm.api-search', ['q' => 'Produk Draf']))->assertOk()->assertJsonPath('products', []);
    }

    public function test_publish_from_the_creation_flow_creates_one_public_house_marker(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->post(route('admin.houses.store'), [
            ...$this->draft(), 'is_published' => '1', 'latitude' => -8.42345, 'longitude' => 115.36542,
        ])->assertRedirect();
        $house = TraditionalHouse::sole();
        $profile = $house->umkmProfile()->sole();
        $this->assertTrue($house->is_published);
        $this->assertTrue($profile->is_active);
        $this->assertSame($house->mapLocation->latitude, $profile->mapLocation->latitude);
        auth()->logout();
        $this->get(route('houses.show', 73))->assertOk();
        $this->get(route('umkm.store', $profile->id))->assertOk();
        $this->get(route('explore'))->assertViewHas('locations', fn ($locations) => count($locations) === 1 && $locations[0]['place_type'] === 'rumah');
    }

    public function test_existing_account_is_assigned_in_the_same_submission(): void
    {
        $owner = User::factory()->create(['role' => 'umkm_owner']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->post(route('admin.houses.store'), [
            ...$this->draft(), 'owner_mode' => 'existing', 'owner_user_id' => $owner->id,
        ])->assertRedirect();
        $this->assertSame($owner->id, TraditionalHouse::sole()->owner_user_id);
        $this->assertTrue($owner->umkmProfile()->sole()->isHouse());
        $this->actingAs($owner)->get(route('owner.house'))->assertOk();
    }

    public function test_new_account_house_and_umkm_are_created_together_with_a_hashed_password(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->post(route('admin.houses.store'), [
            ...$this->draft(), ...$this->newAccount(),
        ])->assertRedirect();
        $owner = User::where('email', 'pengelola@example.test')->sole();
        $this->assertTrue($owner->isUmkmOwner());
        $this->assertTrue(Hash::check('new-password123', $owner->password));
        $this->assertSame($owner->id, TraditionalHouse::sole()->owner_user_id);
        $this->assertSame(TraditionalHouse::sole()->id, $owner->umkmProfile()->sole()->traditional_house_id);
    }

    public function test_failed_submission_preserves_inputs_but_does_not_flash_password_or_create_an_account(): void
    {
        User::factory()->create(['email' => 'pengelola@example.test']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->from(route('admin.houses.create'))->post(route('admin.houses.store'), [
                ...$this->draft(), ...$this->newAccount(),
            ])->assertSessionHasErrors('new_owner_email')
            ->assertSessionHasInput('head_name', 'Keluarga Wayan')
            ->assertSessionMissing('_old_input.password')->assertSessionMissing('_old_input.password_confirmation');
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('traditional_houses', 0);
        $this->assertDatabaseCount('umkm_profiles', 0);
    }

    public function test_cafe_account_cannot_be_renamed_or_converted_by_selecting_it_as_house_manager(): void
    {
        $cafe = UmkmProfile::factory()->create(['business_name' => ['id' => 'Kafe Bambu', 'en' => 'Bamboo Cafe']]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->post(route('admin.houses.store'), [
            ...$this->draft(), 'owner_mode' => 'existing', 'owner_user_id' => $cafe->user_id,
        ])->assertSessionHasErrors('owner_user_id');
        $this->assertDatabaseCount('traditional_houses', 0);
        $this->assertFalse($cafe->fresh()->isHouse());
        $this->assertSame('Kafe Bambu', $cafe->fresh()->getTranslation('business_name', 'id'));
    }

    public function test_transaction_failure_removes_new_account_house_and_uploaded_photo(): void
    {
        Storage::fake('public');
        $otherProfile = UmkmProfile::factory()->house()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->post(route('admin.houses.store'), [
            ...$this->draft(), ...$this->newAccount(), 'umkm_profile_ids' => [$otherProfile->id],
            'cover_image' => UploadedFile::fake()->image('house.jpg'),
        ])->assertSessionHasErrors('umkm_profile_ids');
        $this->assertDatabaseMissing('users', ['email' => 'pengelola@example.test']);
        $this->assertDatabaseMissing('traditional_houses', ['number' => 73]);
        $this->assertDatabaseCount('umkm_profiles', 1);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_duplicate_number_displays_a_link_to_the_existing_house_and_preserves_entered_data(): void
    {
        $house = TraditionalHouse::factory()->create(['number' => 73]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->from(route('admin.houses.create'))->post(route('admin.houses.store'), $this->draft())
            ->assertSessionHasErrors('number')->assertSessionHasInput('head_name', 'Keluarga Wayan');
        $this->get(route('admin.houses.create'))->assertOk()->assertSee('Rumah No. 73 sudah terdaftar.')
            ->assertSee(route('admin.houses.edit', $house), false)->assertSee('Buka rumah tersebut');
        $this->assertDatabaseCount('traditional_houses', 1);
    }

    public function test_a_saved_draft_can_be_published_without_manually_activating_or_linking_its_umkm(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->post(route('admin.houses.store'), $this->draft())->assertRedirect();
        $house = TraditionalHouse::sole();
        $this->get(route('admin.houses.edit', $house))->assertOk()->assertSee('name="activate_house_umkm"', false);
        $this->put(route('admin.houses.update', $house), [
            'number' => 73, 'head_name' => $house->head_name, 'male_count' => 0, 'female_count' => 0,
            'is_published' => '1', 'activate_house_umkm' => '1', 'latitude' => -8.42345, 'longitude' => 115.36542,
        ])->assertRedirect();
        $this->assertTrue($house->fresh()->is_published);
        $this->assertTrue($house->umkmProfile()->sole()->is_active);
    }

    #[DataProvider('unauthorizedRoles')]
    public function test_non_admin_cannot_create_a_house_or_an_account(string $role): void
    {
        $this->actingAs(User::factory()->create(['role' => $role]))->post(route('admin.houses.store'), [
            ...$this->draft(), ...$this->newAccount(),
        ])->assertForbidden();
        $this->assertDatabaseCount('traditional_houses', 0);
        $this->assertDatabaseMissing('users', ['email' => 'pengelola@example.test']);
    }

    public static function unauthorizedRoles(): array
    {
        return [['umkm_owner'], ['tourist'], ['admin_viewer'], ['ticket_officer']];
    }

    private function draft(): array
    {
        return ['creation_flow' => 'guided', 'number' => 73, 'head_name' => 'Keluarga Wayan', 'owner_mode' => 'later'];
    }

    private function newAccount(): array
    {
        return [
            'owner_mode' => 'new', 'new_owner_name' => 'Wayan', 'new_owner_email' => 'pengelola@example.test',
            'new_owner_phone' => '08123456789', 'password' => 'new-password123', 'password_confirmation' => 'new-password123',
        ];
    }
}
