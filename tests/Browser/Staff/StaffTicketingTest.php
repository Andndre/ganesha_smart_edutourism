<?php

namespace Tests\Browser\Staff;

use App\Models\Reservation;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StaffTicketingTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_walk_in_ticket_creation(): void
    {
        $staff = User::factory()->create(['role' => 'ticket_officer']);

        $package = TourPackage::create([
            'name' => 'Paket Test Dusk',
            'slug' => 'paket-test-dusk',
            'price' => 50000.00,
            'duration_hours' => 2.0,
            'max_capacity' => 20,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($staff, $package) {
            $browser->loginAs($staff)
                ->visit('/staff/ticketing')
                ->waitForText('Ticketing Point of Sale')
                ->press('Beli Tiket Walk-in')
                ->waitForText('Pembelian Tiket Walk-in')
                ->type('guest_name', 'Tamu Dusk')
                ->select('tour_package_id', $package->id)
                ->type('party_size', '2')
                ->select('payment_method', 'cash')
                ->press('Proses & Cetak Tiket')
                ->waitForText('Berhasil')
                ->assertSee('Tiket Walk-in');
        });
    }

    public function test_verify_ticket_code(): void
    {
        $staff = User::factory()->create(['role' => 'ticket_officer']);

        $reservation = Reservation::create([
            'guest_name' => 'Tamu Verify',
            'guest_email' => 'verify@example.com',
            'guest_phone' => '08123456789',
            'reservation_type' => 'custom_tour',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '10:00:00',
            'party_size' => 1,
            'total_amount' => 0,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'qr_code' => 'VERIFY-DUSK-123',
        ]);

        $this->browse(function (Browser $browser) use ($staff, $reservation) {
            $browser->loginAs($staff)
                ->visit('/staff/ticketing/scan')
                ->waitForText('Scanner Tiket Masuk')
                ->script(<<<JS
                    fetch('/staff/ticketing/verify', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        },
                        body: JSON.stringify({ qr_code: '{$reservation->qr_code}' })
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        var el = document.createElement('div');
                        el.id = 'dusk-verify-result';
                        el.textContent = data.success ? 'berhasil' : 'gagal: ' + data.message;
                        document.body.appendChild(el);
                    });
                JS);

            $browser->waitForTextIn('#dusk-verify-result', 'berhasil');
        });
    }
}
