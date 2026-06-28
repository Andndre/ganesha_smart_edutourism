<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\VillageSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                'open_time'  => '09:00',
                'close_time' => '17:00',
            ])
            ->assertRedirect();

        $settings = VillageSettings::get();
        $this->assertEquals('09:00', \Carbon\Carbon::parse($settings->open_time)->format('H:i'));
        $this->assertEquals('17:00', \Carbon\Carbon::parse($settings->close_time)->format('H:i'));
    }

    public function test_close_time_must_be_after_open_time(): void
    {
        $this->actingAs($this->adminUser())
            ->put(route('admin.settings.update'), [
                'open_time'  => '18:00',
                'close_time' => '08:00',
            ])
            ->assertSessionHasErrors(['close_time']);
    }
}
