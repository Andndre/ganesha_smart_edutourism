<?php

namespace Tests\Feature;

use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Models\TraditionalHouse;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraditionalHouseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_a_house_with_a_location_and_active_umkm(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $umkm = UmkmProfile::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->post(route('admin.houses.store'), [
            'number' => 18,
            'head_name' => 'Jero Wayan Sujana',
            'male_count' => 2,
            'female_count' => 3,
            'description' => ['id' => 'Rumah keluarga Sujana.', 'en' => 'The Sujana family courtyard.'],
            'latitude' => -8.42345,
            'longitude' => 115.36542,
            'umkm_profile_ids' => [$umkm->id],
            'is_published' => '1',
            'is_open_to_visitors' => '1',
        ])->assertRedirect();

        $house = TraditionalHouse::where('number', 18)->firstOrFail();
        $this->assertTrue($house->is_published);
        $this->assertNotNull($house->ar_marker_id);
        $this->assertSame($house->id, $umkm->fresh()->traditional_house_id);
        $this->assertSame(-8.42345, $house->mapLocation->latitude);

        auth()->logout();
        $this->get(route('houses.index'))->assertOk()->assertSee('Jero Wayan Sujana')->assertSee('1 pekarangan terverifikasi');
        $this->get(route('houses.show', ['number' => 18]))->assertOk()->assertSee('Jero Wayan Sujana');
        $this->get(route('umkm.store', ['id' => $umkm->id]))->assertOk()->assertSee('Bagian dari Pekarangan No.');
        $this->get(route('ar-scan-redirect', ['arMarkerId' => $house->ar_marker_id]))
            ->assertRedirect(route('houses.show', ['number' => 18]));
        $this->getJson('/api/ar/model?marker='.$house->ar_marker_id)
            ->assertOk()->assertJsonPath('redirect_url', route('houses.show', ['number' => 18]));
    }

    public function test_publishing_requires_location_and_active_umkm(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inactive = UmkmProfile::factory()->create(['is_active' => false]);

        $this->actingAs($admin)->post(route('admin.houses.store'), [
            'number' => 24,
            'head_name' => 'Ibu Made Suartini',
            'male_count' => 1,
            'female_count' => 2,
            'umkm_profile_ids' => [$inactive->id],
            'is_published' => '1',
        ])->assertSessionHasErrors(['latitude', 'longitude', 'umkm_profile_ids']);

        $this->assertDatabaseMissing('traditional_houses', ['number' => 24]);
    }

    public function test_drafts_and_houses_without_active_umkm_are_hidden(): void
    {
        $draft = TraditionalHouse::factory()->create(['number' => 31]);
        $draft->syncMapLocation(['category' => 'cultural', 'latitude' => -8.42, 'longitude' => 115.36]);
        $umkm = UmkmProfile::factory()->create(['traditional_house_id' => $draft->id, 'is_active' => true]);

        $this->get(route('houses.index'))->assertOk()->assertDontSee('Pekarangan #31');
        $this->get(route('houses.show', ['number' => 31]))->assertNotFound();

        $draft->update(['is_published' => true]);
        $umkm->update(['is_active' => false]);

        $this->get(route('houses.show', ['number' => 31]))->assertNotFound();
        $this->get(route('ar-scan-redirect', ['arMarkerId' => 'unknown-house']))->assertNotFound();
    }

    public function test_admin_viewer_cannot_change_a_house(): void
    {
        $viewer = User::factory()->create(['role' => 'admin_viewer']);
        $house = TraditionalHouse::factory()->create();

        $this->actingAs($viewer)->put(route('admin.houses.update', $house), ['head_name' => 'Changed'])
            ->assertForbidden();

        $this->assertSame($house->head_name, $house->fresh()->head_name);
    }

    public function test_directory_search_uses_house_numbers_and_actual_count(): void
    {
        $numberEighteenUmkm = null;
        foreach ([18, 24] as $number) {
            $house = TraditionalHouse::factory()->create(['number' => $number, 'is_published' => true]);
            $house->syncMapLocation(['category' => 'cultural', 'latitude' => -8.42, 'longitude' => 115.36]);
            $umkm = UmkmProfile::factory()->create(['traditional_house_id' => $house->id, 'is_active' => true]);
            if ($number === 18) {
                $numberEighteenUmkm = $umkm;
            }
        }

        $this->get(route('houses.index', ['q' => '18']))
            ->assertOk()->assertSee('Pekarangan #18')->assertDontSee('Pekarangan #24')
            ->assertSee('2 pekarangan terverifikasi');
        $this->get(route('umkm', ['tab' => 'direktori', 'q' => '18']))
            ->assertOk()->assertSee(route('umkm.store', ['id' => $numberEighteenUmkm->id]), false)
            ->assertSee('Pekarangan No. 18');
    }

    public function test_admin_can_hide_a_published_house_and_remove_it_from_the_public_map(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $house = TraditionalHouse::factory()->create(['is_published' => true]);
        $house->syncMapLocation(['category' => 'cultural', 'latitude' => -8.42, 'longitude' => 115.36]);
        $umkm = UmkmProfile::factory()->create(['traditional_house_id' => $house->id, 'is_active' => true]);

        $this->actingAs($admin)->put(route('admin.houses.update', $house), [
            'number' => $house->number,
            'head_name' => $house->head_name,
            'male_count' => $house->male_count,
            'female_count' => $house->female_count,
            'latitude' => -8.42,
            'longitude' => 115.36,
            'umkm_profile_ids' => [$umkm->id],
            'is_published' => '0',
        ])->assertRedirect();

        $this->assertFalse($house->fresh()->is_published);
        auth()->logout();
        $this->get(route('houses.show', ['number' => $house->number]))->assertNotFound();
        $this->get(route('explore'))->assertOk()->assertDontSee('Pekarangan No. '.$house->number);
    }

    public function test_legacy_cultural_marker_still_redirects_to_its_object(): void
    {
        $object = CulturalObject::factory()->create();
        $model = ArModel::factory()->create(['cultural_object_id' => $object->id]);

        $this->get(route('ar-scan-redirect', ['arMarkerId' => $model->ar_marker_id]))
            ->assertRedirect(route('cultural-object', ['slug' => $object->slug]));
    }

    public function test_house_without_3d_models_does_not_offer_a_model_link(): void
    {
        $house = TraditionalHouse::factory()->create(['is_published' => true]);
        $house->syncMapLocation(['category' => 'cultural', 'latitude' => -8.42, 'longitude' => 115.36]);
        UmkmProfile::factory()->create(['traditional_house_id' => $house->id, 'is_active' => true]);
        $house->culturalObjects()->attach(CulturalObject::factory()->create());

        $this->get(route('houses.show', ['number' => $house->number]))
            ->assertOk()->assertDontSee('Lihat model 3D');
    }

    public function test_house_profile_shows_products_and_available_cultural_model(): void
    {
        $house = TraditionalHouse::factory()->create(['is_published' => true]);
        $house->syncMapLocation(['category' => 'cultural', 'latitude' => -8.42, 'longitude' => 115.36]);
        $umkm = UmkmProfile::factory()->create(['traditional_house_id' => $house->id, 'is_active' => true]);
        $category = UmkmProductCategory::factory()->create(['name' => ['id' => 'Anyaman Bambu', 'en' => 'Bamboo Weaving']]);
        UmkmProduct::factory()->create(['umkm_profile_id' => $umkm->id, 'umkm_product_category_id' => $category->id]);
        $object = CulturalObject::factory()->create();
        $house->culturalObjects()->attach($object);
        $model = ArModel::factory()->create(['cultural_object_id' => $object->id]);

        $this->get(route('houses.show', ['number' => $house->number]))
            ->assertOk()
            ->assertSee('Anyaman Bambu')
            ->assertSee(route('ar-scan', ['model_id' => $model->id]), false);
    }
}
