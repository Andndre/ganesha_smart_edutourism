<?php

namespace Tests\Browser\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminDashboardTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_dashboard_widgets_visible(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/dashboard')
                ->waitForText('Dashboard')
                ->assertSee('Dashboard')
                ->assertSee('Pengunjung Hari Ini')
                ->assertSee('Rating Kepuasan');
        });
    }
}
