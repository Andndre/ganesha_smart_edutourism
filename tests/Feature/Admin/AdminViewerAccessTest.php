<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminViewerAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_viewer_can_view_dashboard(): void
    {
        $viewer = User::factory()->create(['role' => 'admin_viewer']);

        $this->actingAs($viewer)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_admin_viewer_cannot_submit_mutating_request(): void
    {
        $viewer = User::factory()->create(['role' => 'admin_viewer']);

        $this->actingAs($viewer)
            ->post(route('admin.capacity.store'), [])
            ->assertForbidden();
    }

    public function test_admin_can_still_submit_mutating_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->post(route('admin.capacity.store'), [
                'name' => 'Test Zone',
                'max_capacity' => 100,
                'warning_threshold' => 70,
                'critical_threshold' => 90,
            ]);

        $response->assertStatus(302);
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_viewer_can_access_staff_ticketing_pages(): void
    {
        $viewer = User::factory()->create(['role' => 'admin_viewer']);

        $this->actingAs($viewer)->get(route('staff.ticketing'))->assertOk();
        $this->actingAs($viewer)->get(route('staff.ticketing.stats'))->assertOk();
        $this->actingAs($viewer)->get(route('staff.ticketing.scan'))->assertOk();
    }
}
