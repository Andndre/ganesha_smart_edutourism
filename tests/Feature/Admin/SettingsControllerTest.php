<?php

namespace Tests\Feature\Admin;

use App\Models\CulturalObject;
use App\Models\User;
use App\Models\VillageSettings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->adminUser())->get(route('admin.settings'));
        $response->assertOk()->assertViewIs('admin.settings.index');
    }

    public function test_admin_can_update_operational_hours(): void
    {
        $this->actingAs($this->adminUser())
            ->put(route('admin.settings.update'), [
                'open_time' => '09:00',
                'close_time' => '17:00',
            ])
            ->assertRedirect();

        $settings = VillageSettings::get();
        $this->assertEquals('09:00', Carbon::parse($settings->open_time)->format('H:i'));
        $this->assertEquals('17:00', Carbon::parse($settings->close_time)->format('H:i'));
    }

    public function test_close_time_must_be_after_open_time(): void
    {
        $this->actingAs($this->adminUser())
            ->put(route('admin.settings.update'), [
                'open_time' => '18:00',
                'close_time' => '08:00',
            ])
            ->assertSessionHasErrors(['close_time']);
    }

    public function test_admin_can_choose_separate_shared_and_featured_parents_and_clear_the_featured_choice(): void
    {
        $courtyard = CulturalObject::factory()->create();
        $featured = CulturalObject::factory()->create();
        $this->actingAs($this->adminUser())->put(route('admin.settings.update'), [
            'open_time' => '08:00', 'close_time' => '18:00',
            'house_cultural_object_id' => $courtyard->id, 'featured_cultural_object_id' => $featured->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('village_settings', [
            'id' => 1, 'house_cultural_object_id' => $courtyard->id, 'featured_cultural_object_id' => $featured->id,
        ]);
        $this->put(route('admin.settings.update'), [
            'open_time' => '09:00', 'close_time' => '17:00', 'featured_cultural_object_id' => '',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('village_settings', [
            'id' => 1, 'house_cultural_object_id' => $courtyard->id, 'featured_cultural_object_id' => null,
        ]);
    }

    #[DataProvider('culturalSettingFields')]
    public function test_child_objects_and_missing_objects_cannot_be_selected_as_global_parents(string $field): void
    {
        $root = CulturalObject::factory()->create();
        $child = CulturalObject::factory()->create(['parent_id' => $root->id]);
        VillageSettings::get()->update([$field => $root->id]);
        $this->actingAs($this->adminUser());

        foreach ([$child->id, 999999] as $invalidId) {
            $this->put(route('admin.settings.update'), [
                'open_time' => '09:00', 'close_time' => '17:00', $field => $invalidId,
            ])->assertSessionHasErrors($field);
            $this->assertSame($root->id, VillageSettings::get()->getAttribute($field));
        }
    }

    public static function culturalSettingFields(): array
    {
        return [
            'shared house parent' => ['house_cultural_object_id'],
            'featured parent' => ['featured_cultural_object_id'],
        ];
    }

    public function test_settings_only_offer_root_objects_and_show_the_current_choices(): void
    {
        $root = CulturalObject::factory()->create(['name' => ['id' => 'Pekarangan Rumah Adat', 'en' => 'Courtyard']]);
        CulturalObject::factory()->create(['parent_id' => $root->id]);
        VillageSettings::get()->update(['house_cultural_object_id' => $root->id, 'featured_cultural_object_id' => $root->id]);

        $response = $this->actingAs($this->adminUser())->get(route('admin.settings'))->assertOk()
            ->assertSee('Induk budaya untuk seluruh rumah')->assertSee('Objek budaya unggulan')
            ->assertSee('Pekarangan Rumah Adat — 1 ruang budaya');

        $this->assertSame([$root->id], $response->viewData('culturalRoots')->modelKeys());
        $this->assertSame($root->id, $response->viewData('settings')->featured_cultural_object_id);
    }

    public function test_read_only_admin_cannot_change_global_cultural_settings(): void
    {
        $root = CulturalObject::factory()->create();

        $this->actingAs(User::factory()->create(['role' => 'admin_viewer']))->put(route('admin.settings.update'), [
            'open_time' => '09:00', 'close_time' => '17:00', 'house_cultural_object_id' => $root->id,
        ])->assertForbidden();

        $this->assertNull(VillageSettings::get()->house_cultural_object_id);
    }
}
