<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\RouteSession;
use App\Models\TourRoute;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SmartEdutourismFlowTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_start_route(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();
            $route = TourRoute::factory()->create([
                'is_active' => true,
                'name' => 'Rute Wisata Dusk',
            ]);

            $browser->loginAs($user)
                ->visit('/edutourism')
                ->waitForText('Rute Wisata Dusk')
                ->press('Mulai Jelajah')
                ->waitFor('#btn-start-route:not([disabled])');

            $browser->script("
                navigator.geolocation.getCurrentPosition = function(success, error) {
                    success({ coords: { latitude: -8.4098, longitude: 115.2671 } });
                };
            ");

            $browser->press('Mulai Eksplorasi')
                ->waitForLocation('/edutourism/active')
                ->assertPathIs('/edutourism/active');
        });
    }

    public function test_active_route_displays_points(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();
            $route = TourRoute::factory()->create([
                'is_active' => true,
                'name' => 'Rute Wisata Dusk',
            ]);

            RouteSession::factory()->create([
                'user_id' => $user->id,
                'tour_route_id' => $route->id,
                'status' => 'active',
            ]);

            $browser->loginAs($user)
                ->visit('/edutourism/active');

            $browser->script("
                navigator.geolocation.getCurrentPosition = function(success, error) {
                    success({ coords: { latitude: -8.4098, longitude: 115.2671 } });
                };
                navigator.geolocation.watchPosition = function(success, error) {
                    success({ coords: { latitude: -8.4098, longitude: 115.2671 } });
                    return 1;
                };
            ");

            $browser->waitForText('Rute Wisata Dusk')
                ->assertSee('Rute Wisata Dusk');
        });
    }
}
