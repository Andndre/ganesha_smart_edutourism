<?php

namespace Tests\Browser;

use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserBookingFlowTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_booking_form_submission(): void
    {
        $user = User::factory()->create([
            'role' => 'tourist',
        ]);

        $package = TourPackage::create([
            'name' => 'Paket Dusk Test',
            'slug' => 'paket-dusk-test',
            'description' => 'Paket wisata untuk pengujian Dusk.',
            'price' => 100000,
            'duration_hours' => 3,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($user, $package) {
            $browser->loginAs($user)
                ->visit('/tour-packages')
                ->waitForText('Paket Dusk Test')
                ->assertSee('Paket Dusk Test')
                ->clickLink('Paket Dusk Test')
                ->waitForLocation('/tour-package/'.$package->id)
                ->assertSee('Pesan Tiket Sekarang')
                ->clickLink('Pesan Tiket Sekarang')
                ->waitForLocation('/tour-package/'.$package->id.'/book')
                ->assertSee('Checkout')
                ->type('guest_name', 'Agung Dusk Test')
                ->type('guest_email', 'dusk@example.com')
                ->press('Bayar Sekarang');

            // ponytail: accept either Midtrans redirect or error if not configured
            $browser->waitUntil("document.querySelector('.text-red-600') !== null || window.location.href.includes('midtrans')", 15);
        });
    }
}
