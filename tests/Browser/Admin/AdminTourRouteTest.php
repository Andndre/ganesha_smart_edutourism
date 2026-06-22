<?php

namespace Tests\Browser\Admin;

use App\Models\User;
use App\Models\TourRoute;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminTourRouteTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_toggle_route_active_status(): void
    {
        $route = TourRoute::factory()->create([
            'name' => 'Rute Dusk Test',
            'is_active' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/tour-routes')
                ->waitForText('Rute Dusk Test')
                ->press('Nonaktifkan')
                ->waitForText('dinonaktifkan')
                ->assertSee('Aktifkan');
        });
    }
}
