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
     * Test staff can purchase a walk-in ticket.
     */
    public function test_staff_can_process_walk_in_purchase(): void
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

        $response = $this->actingAs($officer)->post('/staff/ticketing/walk-in', [
            'guest_name' => 'Guest Walkin',
            'guest_email' => 'walkin@example.com',
            'guest_phone' => '08123456789',
            'tour_package_id' => $package->id,
            'party_size' => 2,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect('/staff/ticketing');
        $response->assertSessionHas('success', 'Tiket Walk-in berhasil dibuat!');

        $this->assertDatabaseHas('reservations', [
            'guest_name' => 'Guest Walkin',
            'guest_email' => 'walkin@example.com',
            'guest_phone' => '08123456789',
            'tour_package_id' => $package->id,
            'party_size' => 2,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'confirmed',
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
}
