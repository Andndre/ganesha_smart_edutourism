<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireStaleReservationsTest extends TestCase
{
    use RefreshDatabase;

    private TourPackage $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->package = TourPackage::create([
            'name' => 'Paket Test',
            'slug' => 'paket-test',
            'price' => 100000,
            'duration_hours' => 2,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);
    }

    private function makeReservation(array $overrides = []): Reservation
    {
        $user = User::factory()->create(['role' => 'tourist']);

        return Reservation::create(array_merge([
            'user_id' => $user->id,
            'tour_package_id' => $this->package->id,
            'guest_name' => 'Test User',
            'guest_email' => 'test@test.com',
            'reservation_type' => 'package',
            'scheduled_date' => now()->addDays(3),
            'party_size' => 1,
            'total_amount' => 100000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_reference' => 'TKT-TEST-'.uniqid(),
            'qr_code' => 'QR-TEST-'.uniqid(),
        ], $overrides));
    }

    public function test_cancels_pending_reservation_older_than_24_hours(): void
    {
        $reservation = $this->makeReservation(['status' => 'pending']);

        // Travel time so the reservation appears old
        $reservation->created_at = now()->subHours(25);
        $reservation->save();

        $this->artisan('reservations:expire-stale')->assertSuccessful();

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
            'cancellation_type' => 'system_expire',
        ]);
    }

    public function test_does_not_cancel_pending_reservation_newer_than_24_hours(): void
    {
        $reservation = $this->makeReservation(['status' => 'pending']);

        // Fresh reservation — created just now
        $reservation->created_at = now()->subHours(23);
        $reservation->save();

        $this->artisan('reservations:expire-stale')->assertSuccessful();

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'pending',
        ]);
    }

    public function test_does_not_cancel_confirmed_reservations(): void
    {
        $reservation = $this->makeReservation([
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $reservation->created_at = now()->subDays(5);
        $reservation->save();

        $this->artisan('reservations:expire-stale')->assertSuccessful();

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_does_not_affect_already_cancelled_reservations(): void
    {
        $reservation = $this->makeReservation([
            'status' => 'cancelled',
            'cancellation_type' => 'user',
        ]);

        $reservation->created_at = now()->subDays(2);
        $reservation->save();

        $this->artisan('reservations:expire-stale')->assertSuccessful();

        // cancellation_type should remain 'user', not overwritten to 'system_expire'
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'cancellation_type' => 'user',
        ]);
    }

    public function test_sets_cancellation_note_on_expired_reservation(): void
    {
        $reservation = $this->makeReservation(['status' => 'pending']);
        $reservation->created_at = now()->subHours(25);
        $reservation->save();

        $this->artisan('reservations:expire-stale')->assertSuccessful();

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'cancellation_note' => 'Cancelled by system: pending > 24 hours',
        ]);
    }

    public function test_cancels_multiple_stale_reservations_in_one_run(): void
    {
        $stale1 = $this->makeReservation(['status' => 'pending']);
        $stale1->created_at = now()->subHours(30);
        $stale1->save();

        $stale2 = $this->makeReservation(['status' => 'pending']);
        $stale2->created_at = now()->subDays(2);
        $stale2->save();

        $fresh = $this->makeReservation(['status' => 'pending']);
        $fresh->created_at = now()->subHours(1);
        $fresh->save();

        $this->artisan('reservations:expire-stale')->assertSuccessful();

        $this->assertDatabaseHas('reservations', ['id' => $stale1->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('reservations', ['id' => $stale2->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('reservations', ['id' => $fresh->id, 'status' => 'pending']);
    }

    public function test_outputs_count_of_cancelled_reservations(): void
    {
        $stale = $this->makeReservation(['status' => 'pending']);
        $stale->created_at = now()->subHours(30);
        $stale->save();

        $this->artisan('reservations:expire-stale')
            ->expectsOutput('Cancelled 1 pending reservations')
            ->assertSuccessful();
    }
}
