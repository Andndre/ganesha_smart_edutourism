<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests cannot access profile edit page.
     */
    public function test_guests_cannot_access_profile_edit_page(): void
    {
        $response = $this->get('/profile/edit');

        $response->assertRedirect('/login');
    }

    /**
     * Test guests cannot update profile.
     */
    public function test_guests_cannot_update_profile(): void
    {
        $response = $this->put('/profile', [
            'name' => 'New Name',
            'email' => 'newemail@example.com',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated users can view profile edit page.
     */
    public function test_authenticated_users_can_view_profile_edit_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertSee('Informasi Profil');
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    /**
     * Test user can update their profile.
     */
    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'phone' => '123',
            'nationality' => 'Indonesian',
            'preferred_language' => 'id',
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '08123',
            'nationality' => 'Malaysian',
            'preferred_language' => 'en',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success', 'Profil Anda berhasil diperbarui.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '08123',
            'nationality' => 'Malaysian',
            'preferred_language' => 'en',
        ]);
    }

    /**
     * Test user cannot update profile with an email that is already taken.
     */
    public function test_user_cannot_update_profile_with_taken_email(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->actingAs($user1)->from('/profile/edit')->put('/profile', [
            'name' => 'New Name',
            'email' => 'user2@example.com',
        ]);

        $response->assertRedirect('/profile/edit');
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test user can change their password.
     */
    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success', 'Profil Anda berhasil diperbarui.');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }
}
