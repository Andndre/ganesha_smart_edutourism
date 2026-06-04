<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTicketingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Midtrans\Snap statically before it gets loaded
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
            ->andReturnUsing(function ($orderId) {
                if (str_contains($orderId, 'WALKIN-TEST')) {
                    return [
                        'transaction_status' => 'settlement',
                        'payment_type' => 'qris',
                    ];
                }
                throw new \Exception('Midtrans status mock exception');
            });
    }

    /**
     * Test guests cannot access staff ticketing.
     */
    public function test_guests_cannot_access_staff_ticketing(): void
    {
        $response = $this->get('/staff/ticketing');
        $response->assertRedirect('/login');

        $responseScan = $this->get('/staff/ticketing/scan');
        $responseScan->assertRedirect('/login');
    }

    /**
     * Test standard users cannot access staff ticketing.
     */
    public function test_tourist_cannot_access_staff_ticketing(): void
    {
        $tourist = User::factory()->create(['role' => 'tourist']);

        $response = $this->actingAs($tourist)->get('/staff/ticketing');
        $response->assertStatus(403);

        $responseScan = $this->actingAs($tourist)->get('/staff/ticketing/scan');
        $responseScan->assertStatus(403);
    }

    /**
     * Test admin and ticket officer can access staff ticketing.
     */
    public function test_staff_and_admin_can_access_staff_ticketing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        // Test admin
        $responseAdmin = $this->actingAs($admin)->get('/staff/ticketing');
        $responseAdmin->assertStatus(200);
        $responseAdminScan = $this->actingAs($admin)->get('/staff/ticketing/scan');
        $responseAdminScan->assertStatus(200);

        // Test officer
        $responseOfficer = $this->actingAs($officer)->get('/staff/ticketing');
        $responseOfficer->assertStatus(200);
        $responseOfficerScan = $this->actingAs($officer)->get('/staff/ticketing/scan');
        $responseOfficerScan->assertStatus(200);
    }

    /**
     * Test staff can purchase a walk-in ticket using cash.
     */
    public function test_staff_can_process_walk_in_purchase_cash(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        $package = TourPackage::create([
            'name' => 'Walkin Paket',
            'slug' => 'walkin-paket',
            'price' => 50000.00,
            'duration_hours' => 2.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($officer)->postJson('/staff/ticketing/walk-in', [
            'guest_name' => 'Guest Walkin',
            'guest_email' => 'walkin@example.com',
            'guest_phone' => '08123456789',
            'tour_package_id' => $package->id,
            'party_size' => 2,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'payment_method' => 'cash',
            'message' => 'Tiket Walk-in (Tunai) berhasil dibuat!',
        ]);

        $this->assertDatabaseHas('reservations', [
            'guest_name' => 'Guest Walkin',
            'guest_email' => 'walkin@example.com',
            'guest_phone' => '08123456789',
            'tour_package_id' => $package->id,
            'party_size' => 2,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'completed', // Confirmed visitor walks in immediately
        ]);
    }

    /**
     * Test staff can purchase a walk-in ticket using QRIS (via Midtrans).
     */
    public function test_staff_can_process_walk_in_purchase_qris(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        $package = TourPackage::create([
            'name' => 'Walkin Paket QRIS',
            'slug' => 'walkin-paket-qris',
            'price' => 50000.00,
            'duration_hours' => 2.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        config(['midtrans.server_key' => 'test-server-key']);

        $response = $this->actingAs($officer)->postJson('/staff/ticketing/walk-in', [
            'guest_name' => 'Guest Walkin QRIS',
            'guest_email' => 'qris@example.com',
            'guest_phone' => '08123456789',
            'tour_package_id' => $package->id,
            'party_size' => 1,
            'payment_method' => 'qris',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'payment_method' => 'qris',
            'snap_token' => 'mocked-snap-token-abc-123',
        ]);

        $this->assertDatabaseHas('reservations', [
            'guest_name' => 'Guest Walkin QRIS',
            'guest_email' => 'qris@example.com',
            'guest_phone' => '08123456789',
            'tour_package_id' => $package->id,
            'party_size' => 1,
            'payment_method' => 'qris',
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);
    }

    /**
     * Test staff can verify a valid paid QR code.
     */
    public function test_staff_can_verify_valid_uncompleted_qr_code(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        $package = TourPackage::create([
            'name' => 'Verify Paket',
            'slug' => 'verify-paket',
            'price' => 50000.00,
            'duration_hours' => 2.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'guest_name' => 'Guest Ticket',
            'guest_email' => 'test@example.com',
            'guest_phone' => '08123456789',
            'tour_package_id' => $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => today(),
            'scheduled_time' => '10:00',
            'party_size' => 1,
            'total_amount' => 50000.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'qr_code' => 'TKT-TEST-VERIFY-QR',
        ]);

        $response = $this->actingAs($officer)->postJson('/staff/ticketing/verify', [
            'qr_code' => 'TKT-TEST-VERIFY-QR',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'Tiket berhasil diverifikasi! Selamat datang Guest Ticket',
        ]);

        $this->assertEquals('completed', $reservation->fresh()->status);
    }

    /**
     * Test staff cannot verify a completed QR code.
     */
    public function test_staff_cannot_verify_completed_qr_code(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        $package = TourPackage::create([
            'name' => 'Verify Paket',
            'slug' => 'verify-paket',
            'price' => 50000.00,
            'duration_hours' => 2.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'guest_name' => 'Guest Ticket',
            'guest_email' => 'test@example.com',
            'guest_phone' => '08123456789',
            'tour_package_id' => $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => today(),
            'scheduled_time' => '10:00',
            'party_size' => 1,
            'total_amount' => 50000.00,
            'status' => 'completed',
            'payment_status' => 'paid',
            'qr_code' => 'TKT-TEST-COMPLETED-QR',
        ]);

        $response = $this->actingAs($officer)->postJson('/staff/ticketing/verify', [
            'qr_code' => 'TKT-TEST-COMPLETED-QR',
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Tiket ini sudah digunakan sebelumnya.',
        ]);
    }

    /**
     * Test staff can sync the status of a pending QRIS reservation.
     */
    public function test_staff_can_sync_pending_qris_reservation(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);
        $package = TourPackage::create([
            'name' => 'Sync Package',
            'slug' => 'sync-package',
            'price' => 50000.00,
            'duration_hours' => 2.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'guest_name' => 'Guest Sync',
            'guest_email' => 'sync@example.com',
            'guest_phone' => '08123456789',
            'tour_package_id' => $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => today(),
            'scheduled_time' => '10:00',
            'party_size' => 1,
            'total_amount' => 50000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'qris',
            'payment_reference' => 'WALKIN-TEST-SYNC-1',
            'qr_code' => 'TKT-TEST-SYNC-QR',
        ]);

        $response = $this->actingAs($officer)->postJson("/staff/ticketing/sync/{$reservation->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $this->assertEquals('completed', $reservation->fresh()->status);
        $this->assertEquals('paid', $reservation->fresh()->payment_status);
        $this->assertEquals('qris', $reservation->fresh()->payment_method);
    }

    /**
     * Test index page auto-syncs pending QRIS reservations.
     */
    public function test_index_page_auto_syncs_pending_reservations(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);
        $package = TourPackage::create([
            'name' => 'Auto Sync Package',
            'slug' => 'auto-sync-package',
            'price' => 50000.00,
            'duration_hours' => 2.0,
            'max_capacity' => 10,
            'min_capacity' => 1,
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'guest_name' => 'Guest Auto Sync',
            'guest_email' => 'autosync@example.com',
            'guest_phone' => '08123456789',
            'tour_package_id' => $package->id,
            'reservation_type' => 'package',
            'scheduled_date' => today(),
            'scheduled_time' => '10:00',
            'party_size' => 1,
            'total_amount' => 50000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'qris',
            'payment_reference' => 'WALKIN-TEST-AUTOSYNC',
            'qr_code' => 'TKT-TEST-AUTOSYNC-QR',
        ]);

        $response = $this->actingAs($officer)->get('/staff/ticketing');

        $response->assertStatus(200);

        $this->assertEquals('completed', $reservation->fresh()->status);
        $this->assertEquals('paid', $reservation->fresh()->payment_status);
    }
}
