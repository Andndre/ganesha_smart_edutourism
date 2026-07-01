<?php

namespace Tests\Browser\Admin;

use App\Models\TourRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminTourRouteTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * @throws \Throwable
     */
    public function test_toggle_route_active_status(): void
    {
        TourRoute::factory()->create([
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
