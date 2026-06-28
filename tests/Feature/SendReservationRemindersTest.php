<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\TourPackage;
use App\Models\User;
use App\Notifications\ReservationReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendReservationRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_reminder_to_users_with_reservation_tomorrow(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $package = TourPackage::create([
            'name' => ['en' => 'Test Package', 'id' => 'Paket Tes'],
            'slug' => 'test-package',
            'description' => ['en' => 'Test', 'id' => 'Tes'],
            'price' => 100000.00,
            'duration_hours' => 2.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        Reservation::create([
            'user_id'        => $user->id,
            'guest_name'     => 'Test User',
            'tour_package_id'=> $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => now()->addDay()->toDateString(),
            'status'         => 'confirmed',
            'party_size'     => 2,
            'total_amount'   => 200000,
            'qr_code'        => 'test-qr-code-' . now()->timestamp,
        ]);

        $this->artisan('reservations:send-reminders')->assertSuccessful();

        Notification::assertSentTo($user, ReservationReminderNotification::class);
    }

    public function test_skips_guest_reservations(): void
    {
        Notification::fake();

        $package = TourPackage::create([
            'name' => ['en' => 'Test Package', 'id' => 'Paket Tes'],
            'slug' => 'test-package-guest',
            'description' => ['en' => 'Test', 'id' => 'Tes'],
            'price' => 100000.00,
            'duration_hours' => 2.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        Reservation::create([
            'user_id'        => null,
            'guest_name'     => 'Guest User',
            'tour_package_id'=> $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => now()->addDay()->toDateString(),
            'status'         => 'confirmed',
            'party_size'     => 2,
            'total_amount'   => 200000,
            'qr_code'        => 'test-qr-code-guest-' . now()->timestamp,
        ]);

        $this->artisan('reservations:send-reminders')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_skips_reservations_not_tomorrow(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $package = TourPackage::create([
            'name' => ['en' => 'Test Package', 'id' => 'Paket Tes'],
            'slug' => 'test-package-future',
            'description' => ['en' => 'Test', 'id' => 'Tes'],
            'price' => 100000.00,
            'duration_hours' => 2.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        Reservation::create([
            'user_id'        => $user->id,
            'guest_name'     => 'Test User',
            'tour_package_id'=> $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => now()->addDays(3)->toDateString(),
            'status'         => 'confirmed',
            'party_size'     => 2,
            'total_amount'   => 200000,
            'qr_code'        => 'test-qr-code-future-' . now()->timestamp,
        ]);

        $this->artisan('reservations:send-reminders')->assertSuccessful();

        Notification::assertNotSentTo($user, ReservationReminderNotification::class);
    }

    public function test_skips_cancelled_reservations(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $package = TourPackage::create([
            'name' => ['en' => 'Test Package', 'id' => 'Paket Tes'],
            'slug' => 'test-package-cancelled',
            'description' => ['en' => 'Test', 'id' => 'Tes'],
            'price' => 100000.00,
            'duration_hours' => 2.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        Reservation::create([
            'user_id'        => $user->id,
            'guest_name'     => 'Test User',
            'tour_package_id'=> $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => now()->addDay()->toDateString(),
            'status'         => 'cancelled',
            'party_size'     => 2,
            'total_amount'   => 200000,
            'qr_code'        => 'test-qr-code-cancelled-' . now()->timestamp,
        ]);

        $this->artisan('reservations:send-reminders')->assertSuccessful();

        Notification::assertNotSentTo($user, ReservationReminderNotification::class);
    }
}
