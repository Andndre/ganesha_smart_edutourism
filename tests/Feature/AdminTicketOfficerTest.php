<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTicketOfficerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest cannot access ticket officer management.
     */
    public function test_guests_cannot_access_ticket_officer_management(): void
    {
        $response = $this->get('/admin/ticket-officers');
        $response->assertRedirect('/login');
    }

    /**
     * Test a standard user cannot access ticket officer management.
     */
    public function test_non_admin_users_cannot_access_ticket_officer_management(): void
    {
        $user = User::factory()->create([
            'role' => 'tourist',
        ]);

        $response = $this->actingAs($user)->get('/admin/ticket-officers');
        $response->assertStatus(403);
    }

    /**
     * Test an admin can view the ticket officer list.
     */
    public function test_admin_can_access_ticket_officer_management(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $officer = User::factory()->create([
            'role' => 'ticket_officer',
            'name' => 'Officer One',
        ]);

        $response = $this->actingAs($admin)->get('/admin/ticket-officers');

        $response->assertStatus(200);
        $response->assertSee('Officer One');
        $response->assertSee('Akun Petugas Tiket');
    }

    /**
     * Test admin can create a new ticket officer account.
     */
    public function test_admin_can_create_ticket_officer(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post('/admin/ticket-officers', [
            'name' => 'Officer Two',
            'email' => 'officer2@example.com',
            'phone' => '0812345678',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/ticket-officers');
        $response->assertSessionHas('success', 'Akun petugas tiket berhasil dibuat.');

        $officer = User::where('email', 'officer2@example.com')->first();
        $this->assertNotNull($officer);
        $this->assertEquals('ticket_officer', $officer->role);
        $this->assertTrue(Hash::check('password123', $officer->password));
    }

    /**
     * Test admin can update an existing ticket officer account.
     */
    public function test_admin_can_update_ticket_officer(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $officer = User::factory()->create([
            'role' => 'ticket_officer',
            'name' => 'Officer Old',
            'email' => 'old@example.com',
            'phone' => '08111111',
        ]);

        $response = $this->actingAs($admin)->put('/admin/ticket-officers/'.$officer->id, [
            'name' => 'Officer New',
            'email' => 'new@example.com',
            'phone' => '08222222',
            'password' => 'newpassword123',
        ]);

        $response->assertRedirect('/admin/ticket-officers');
        $response->assertSessionHas('success', 'Akun petugas tiket berhasil diperbarui.');

        $officer = $officer->fresh();
        $this->assertEquals('Officer New', $officer->name);
        $this->assertEquals('new@example.com', $officer->email);
        $this->assertEquals('08222222', $officer->phone);
        $this->assertTrue(Hash::check('newpassword123', $officer->password));
    }

    /**
     * Test admin can update ticket officer without changing password.
     */
    public function test_admin_can_update_ticket_officer_without_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $oldPassword = 'original_password';
        $officer = User::factory()->create([
            'role' => 'ticket_officer',
            'name' => 'Officer Old',
            'email' => 'old@example.com',
            'password' => Hash::make($oldPassword),
        ]);

        $response = $this->actingAs($admin)->put('/admin/ticket-officers/'.$officer->id, [
            'name' => 'Officer New',
            'email' => 'new@example.com',
            'phone' => '08222222',
            'password' => '',
        ]);

        $response->assertRedirect('/admin/ticket-officers');
        $response->assertSessionHas('success', 'Akun petugas tiket berhasil diperbarui.');

        $officer = $officer->fresh();
        $this->assertEquals('Officer New', $officer->name);
        $this->assertTrue(Hash::check($oldPassword, $officer->password));
    }

    /**
     * Test admin can delete a ticket officer account.
     */
    public function test_admin_can_delete_ticket_officer(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $officer = User::factory()->create([
            'role' => 'ticket_officer',
            'name' => 'Officer To Delete',
        ]);

        $response = $this->actingAs($admin)->delete('/admin/ticket-officers/'.$officer->id);

        $response->assertRedirect('/admin/ticket-officers');
        $response->assertSessionHas('success', 'Akun petugas tiket berhasil dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $officer->id]);
    }
}
