<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * place_type memilih glyph pin di peta, jadi admin harus bisa mengaturnya — dan
 * nilai di luar preset harus ditolak supaya ikonnya tidak diam-diam jatuh ke default.
 */
class AdminCulturalObjectPinFieldsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'name' => ['en' => 'Puseh Temple', 'id' => 'Pura Puseh'],
            'description' => ['en' => 'Description', 'id' => 'Deskripsi'],
            'category' => 'parahyangan',
        ], $extra);
    }

    public function test_admin_can_save_place_type(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cultural-objects.store'), $this->payload(['place_type' => 'pura']))
            ->assertSessionHasNoErrors();

        $this->assertSame('pura', CulturalObject::firstOrFail()->place_type);
    }

    public function test_place_type_is_optional(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cultural-objects.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $this->assertNull(CulturalObject::firstOrFail()->place_type);
    }

    public function test_place_type_outside_the_preset_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cultural-objects.store'), $this->payload(['place_type' => 'warung']))
            ->assertSessionHasErrors('place_type');

        $this->assertSame(0, CulturalObject::count());
    }

    public function test_admin_can_mark_object_as_house_detail(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cultural-objects.store'), $this->payload(['is_detail' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertTrue(CulturalObject::firstOrFail()->is_detail);
    }

    /**
     * Checkbox tidak terkirim saat tidak dicentang, jadi objek biasa harus tetap
     * tingkat 1 — kalau default-nya salah, seluruh peta ikut hilang di zoom jauh.
     */
    public function test_objects_default_to_top_level(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cultural-objects.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $this->assertFalse(CulturalObject::firstOrFail()->is_detail);
    }

    public function test_admin_can_change_place_type_on_an_existing_object(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cultural-objects.store'), $this->payload(['place_type' => 'pura']));

        $object = CulturalObject::firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.cultural-objects.update', $object->id), $this->payload(['place_type' => 'bale']))
            ->assertSessionHasNoErrors();

        $this->assertSame('bale', $object->fresh()->place_type);
    }

    public function test_edit_form_preselects_the_saved_place_type(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cultural-objects.store'), $this->payload(['place_type' => 'patung']));

        $object = CulturalObject::firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('admin.cultural-objects.edit', $object->id))
            ->assertOk()
            ->assertSee('value="patung" selected', false);
    }

    /**
     * Map-manager memakai ulang form yang sama untuk edit dengan mengganti action-nya,
     * lalu mengisi tiap field satu per satu lewat JS. Field yang tidak ikut diisi akan
     * tampil kosong saat objek dibuka — dan karena select tetap terkirim saat simpan,
     * nilainya yang tersimpan ikut terhapus. Change-detector yang disengaja: ini
     * memastikan kedua baris pengisian itu tidak hilang lagi.
     */
    public function test_map_manager_editor_populates_both_pin_fields(): void
    {
        $script = file_get_contents(
            resource_path('views/admin/map-manager/partials/scripts/editor.blade.php')
        );

        $this->assertStringContainsString('select[name="place_type"]', $script);
        $this->assertStringContainsString('name="is_detail"', $script);
    }

    public function test_admin_form_offers_both_pin_fields(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.cultural-objects.create'));

        $response->assertOk();
        $response->assertSee('name="place_type"', false);
        $response->assertSee('name="is_detail"', false);
    }
}
