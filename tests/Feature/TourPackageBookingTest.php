<?php

namespace Tests\Feature;

use App\Mail\ETicketMail;
use App\Models\Reservation;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TourPackageBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Midtrans\Snap statically before it gets loaded, registering it on every test run
        $snapMock = \Mockery::mock('alias:Midtrans\Snap');
        $snapMock->shouldReceive('getSnapToken')
            ->andReturnUsing(function ($params) {
                if (config('midtrans.server_key') === 'invalid-key') {
                    throw new \Exception('Midtrans connection error');
                }

                return 'mocked-snap-token-abc-123';
            });

        // Mock Midtrans\Transaction statically
        $transactionMock = \Mockery::mock('alias:Midtrans\Transaction');
        $transactionMock->shouldReceive('status')
            ->byDefault()
            ->andThrow(new \Exception('Midtrans status mock exception'));
    }

    /**
     * Test guests can view tour packages list.
     */
    public function test_guests_can_view_tour_packages_list(): void
    {
        $activePackage1 = TourPackage::create([
            'name' => 'Paket Edukasi A',
            'slug' => 'paket-edukasi-a',
            'description' => 'Belajar budaya lokal Bali.',
            'price' => 100000.00,
            'duration_hours' => 3.5,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        $activePackage2 = TourPackage::create([
            'name' => 'Paket Edukasi B',
            'slug' => 'paket-edukasi-b',
            'description' => 'Petualangan sawah hijau.',
            'price' => 150000.00,
            'duration_hours' => 4.0,
            'max_capacity' => 15,
            'min_capacity' => 2,
            'is_active' => true,
        ]);

        $inactivePackage = TourPackage::create([
            'name' => 'Paket Non-Aktif',
            'slug' => 'paket-non-aktif',
            'description' => 'Paket yang belum siap.',
            'price' => 50000.00,
            'duration_hours' => 2.0,
            'max_capacity' => 5,
            'min_capacity' => 1,
            'is_active' => false,
        ]);

        $response = $this->get(route('tour-packages'));

        $response->assertStatus(200);
        $response->assertSee('Paket Edukasi A');
        $response->assertSee('Paket Edukasi B');
        $response->assertDontSee('Paket Non-Aktif');
    }

    /**
     * Test guests can view tour package details.
     */
    public function test_guests_can_view_tour_package_detail(): void
    {
        $package = TourPackage::create([
            'name' => 'Paket Spesial Penglipuran',
            'slug' => 'paket-spesial-penglipuran',
            'description' => 'Deskripsi detail paket wisata edukasi.',
            'price' => 200000.00,
            'duration_hours' => 5.0,
            'max_capacity' => 20,
            'min_capacity' => 2,
            'is_active' => true,
        ]);

        $response = $this->get(route('tour-package', $package->id));

        $response->assertStatus(200);
        $response->assertSee('Paket Spesial Penglipuran');
        $response->assertSee('Deskripsi detail paket wisata edukasi.');
    }

    /**
     * Test guests cannot access checkout without login.
     */
    public function test_guests_cannot_access_checkout_without_login(): void
    {
        $package = TourPackage::create([
            'name' => 'Paket Edukasi',
            'slug' => 'paket-edukasi',
            'price' => 100000.00,
            'duration_hours' => 3.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('tour-package.book', $package->id));

        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated users can access the checkout page.
     */
    public function test_authenticated_user_can_access_checkout_page(): void
    {
        $user = User::factory()->create(['role' => 'tourist']);

        $package = TourPackage::create([
            'name' => 'Paket Budaya Bali',
            'slug' => 'paket-budaya-bali',
            'description' => 'Belajar tari bali.',
            'price' => 120000.00,
            'duration_hours' => 3.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('tour-package.book', $package->id));

        $response->assertStatus(200);
        $response->assertSee('Paket Budaya Bali');
    }

    /**
     * Test authenticated users cannot book with invalid party size.
     */
    public function test_authenticated_user_cannot_process_booking_with_invalid_party_size(): void
    {
        $user = User::factory()->create(['role' => 'tourist']);

        $package = TourPackage::create([
            'name' => 'Paket Private',
            'slug' => 'paket-private',
            'price' => 300000.00,
            'duration_hours' => 3.0,
            'max_capacity' => 5,
            'min_capacity' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('tour-package.process', $package->id), [
            'scheduled_date' => today()->addDays(1)->format('Y-m-d'),
            'scheduled_time' => '10:00',
            'party_size' => 1, // too small
            'guest_name' => 'Agung Andre',
            'guest_email' => 'andre@test.com',
            'guest_phone' => '0812345678',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Jumlah peserta harus antara 2 dan 5 orang.',
        ]);
    }

    /**
     * Test booking creation failure path (Midtrans Exception throws 500 but saves Reservation in DB).
     */
    public function test_booking_creation_fails_midtrans_gracefully(): void
    {
        $user = User::factory()->create(['role' => 'tourist']);

        $package = TourPackage::create([
            'name' => 'Paket Edukasi Alam',
            'slug' => 'paket-edukasi-alam',
            'price' => 100000.00,
            'duration_hours' => 3.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        // Force exception throwing in the mock
        config(['midtrans.server_key' => 'invalid-key']);

        $response = $this->actingAs($user)->postJson(route('tour-package.process', $package->id), [
            'scheduled_date' => today()->addDays(1)->format('Y-m-d'),
            'scheduled_time' => '09:00',
            'party_size' => 3,
            'guest_name' => 'Wayan Sukra',
            'guest_email' => 'wayan@test.com',
            'guest_phone' => '08122334455',
        ]);

        $response->assertStatus(500);
        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Gagal terhubung ke sistem pembayaran. Silakan coba lagi.',
        ]);

        // Verify the reservation is still created in DB with status pending
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'tour_package_id' => $package->id,
            'guest_name' => 'Wayan Sukra',
            'guest_email' => 'wayan@test.com',
            'guest_phone' => '08122334455',
            'party_size' => 3,
            'total_amount' => 300000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }

    /**
     * Test successful booking creation with Mocked Midtrans.
     */
    public function test_authenticated_user_can_process_booking_successfully_with_mocked_midtrans(): void
    {
        $user = User::factory()->create(['role' => 'tourist']);

        $package = TourPackage::create([
            'name' => 'Paket Edukasi Alam',
            'slug' => 'paket-edukasi-alam',
            'price' => 100000.00,
            'duration_hours' => 3.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        // Clear server key to valid state
        config(['midtrans.server_key' => 'test-server-key']);

        $response = $this->actingAs($user)->postJson(route('tour-package.process', $package->id), [
            'scheduled_date' => today()->addDays(2)->format('Y-m-d'),
            'scheduled_time' => '09:00',
            'party_size' => 2,
            'guest_name' => 'Made Sukra',
            'guest_email' => 'made@test.com',
            'guest_phone' => '08122334466',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'snap_token' => 'mocked-snap-token-abc-123',
        ]);

        // Verify Reservation is in database
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'tour_package_id' => $package->id,
            'guest_name' => 'Made Sukra',
            'guest_email' => 'made@test.com',
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }

    /**
     * Test Midtrans webhook handles successful payment.
     */
    public function test_midtrans_webhook_handles_successful_payment(): void
    {
        Mail::fake();

        config(['midtrans.server_key' => 'test-server-key']);

        $user = User::factory()->create(['role' => 'tourist']);
        $package = TourPackage::create([
            'name' => 'Paket Budaya',
            'slug' => 'paket-budaya',
            'price' => 150000.00,
            'duration_hours' => 3.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        $orderId = 'TKT-ABCD1234-123456';
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'guest_name' => 'Ketut Sukra',
            'guest_email' => 'ketut@test.com',
            'guest_phone' => '0812345',
            'tour_package_id' => $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => today()->addDays(3),
            'scheduled_time' => '10:00',
            'party_size' => 1,
            'total_amount' => 150000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_reference' => $orderId,
            'qr_code' => 'TEST-QR-WEBHOOK',
        ]);

        $statusCode = '200';
        $grossAmount = '150000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.'test-server-key');

        $response = $this->postJson('/api/midtrans/webhook', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'gopay',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Notification processed successfully',
        ]);

        // Assert DB status is now confirmed/paid
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'gopay',
        ]);

        // Assert E-Ticket email was queued
        Mail::assertQueued(ETicketMail::class, function ($mail) use ($reservation) {
            return $mail->hasTo('ketut@test.com') && $mail->reservation->id === $reservation->id;
        });
    }

    /**
     * Test Midtrans webhook handles cancelled payments.
     */
    public function test_midtrans_webhook_handles_cancelled_payment(): void
    {
        config(['midtrans.server_key' => 'test-server-key']);

        $user = User::factory()->create(['role' => 'tourist']);
        $package = TourPackage::create([
            'name' => 'Paket Budaya',
            'slug' => 'paket-budaya',
            'price' => 150000.00,
            'duration_hours' => 3.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        $orderId = 'TKT-CANCEL12-123456';
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'guest_name' => 'Ketut Sukra',
            'guest_email' => 'ketut@test.com',
            'guest_phone' => '0812345',
            'tour_package_id' => $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => today()->addDays(3),
            'scheduled_time' => '10:00',
            'party_size' => 1,
            'total_amount' => 150000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_reference' => $orderId,
            'qr_code' => 'TEST-QR-WEBHOOK-CANCEL',
        ]);

        $statusCode = '200';
        $grossAmount = '150000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.'test-server-key');

        $response = $this->postJson('/api/midtrans/webhook', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'cancel',
            'payment_type' => 'gopay',
        ]);

        $response->assertStatus(200);

        // Assert DB status is now cancelled
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
        ]);
    }

    /**
     * Test profile bookings list and dynamic filtering.
     */
    public function test_profile_bookings_index_shows_correct_filtered_bookings(): void
    {
        $user = User::factory()->create(['role' => 'tourist']);
        $package = TourPackage::create([
            'name' => 'Paket Budaya',
            'slug' => 'paket-budaya',
            'price' => 150000.00,
            'duration_hours' => 3.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        // Confirmed Booking
        $activeBooking = Reservation::create([
            'user_id' => $user->id,
            'guest_name' => 'User Active',
            'guest_email' => 'active@test.com',
            'guest_phone' => '0812',
            'tour_package_id' => $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => today()->addDays(3),
            'scheduled_time' => '10:00',
            'party_size' => 1,
            'total_amount' => 150000.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_reference' => 'TKT-ACTIVE',
            'qr_code' => 'QR-ACTIVE',
        ]);

        // Pending Booking
        $pendingBooking = Reservation::create([
            'user_id' => $user->id,
            'guest_name' => 'User Pending',
            'guest_email' => 'pending@test.com',
            'guest_phone' => '0813',
            'tour_package_id' => $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => today()->addDays(4),
            'scheduled_time' => '11:00',
            'party_size' => 2,
            'total_amount' => 300000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_reference' => 'TKT-PENDING',
            'qr_code' => 'QR-PENDING',
        ]);

        // Cancelled Booking
        $cancelledBooking = Reservation::create([
            'user_id' => $user->id,
            'guest_name' => 'User Cancelled',
            'guest_email' => 'cancel@test.com',
            'guest_phone' => '0814',
            'tour_package_id' => $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => today()->addDays(5),
            'scheduled_time' => '12:00',
            'party_size' => 1,
            'total_amount' => 150000.00,
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'payment_reference' => 'TKT-CANCEL',
            'qr_code' => 'QR-CANCEL',
        ]);

        // 1. Get "All" (semua)
        $response = $this->actingAs($user)->get(route('bookings', ['filter' => 'semua']));
        $response->assertStatus(200);
        $response->assertSee('TKT-ACTIVE');
        $response->assertSee('TKT-PENDING');
        $response->assertSee('TKT-CANCEL');

        // 2. Get "Active" (aktif)
        $response = $this->actingAs($user)->get(route('bookings', ['filter' => 'aktif']));
        $response->assertStatus(200);
        $response->assertSee('TKT-ACTIVE');
        $response->assertDontSee('TKT-PENDING');
        $response->assertDontSee('TKT-CANCEL');

        // 3. Get "Finished" (selesai)
        $response = $this->actingAs($user)->get(route('bookings', ['filter' => 'selesai']));
        $response->assertStatus(200);
        $response->assertDontSee('TKT-ACTIVE');
        $response->assertDontSee('TKT-PENDING');
        $response->assertSee('TKT-CANCEL');
    }

    /**
     * Test booking does not require scheduled_time.
     */
    public function test_booking_does_not_require_scheduled_time(): void
    {
        $user = User::factory()->create(['role' => 'tourist']);
        $package = TourPackage::create([
            'name' => 'Paket Test No Time',
            'slug' => 'paket-test-no-time',
            'description' => 'Test package',
            'price' => 100000.00,
            'duration_hours' => 3.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        config(['midtrans.server_key' => 'test-server-key']);

        $response = $this->actingAs($user)->postJson(route('tour-package.process', $package->id), [
            'scheduled_date' => now()->addDay()->format('Y-m-d'),
            'party_size'     => 2,
            'guest_name'     => 'Test User',
            'guest_email'    => 'test@example.com',
            'guest_phone'    => '081234567890',
        ]);

        // Should NOT fail with "scheduled_time is required"
        $response->assertJsonMissingValidationErrors(['scheduled_time']);
    }

    /**
     * Test reservation has no scheduled_time column.
     */
    public function test_reservation_has_no_scheduled_time_column(): void
    {
        $this->assertFalse(
            \Illuminate\Support\Facades\Schema::hasColumn('reservations', 'scheduled_time'),
            'scheduled_time column should have been dropped'
        );
    }
}
