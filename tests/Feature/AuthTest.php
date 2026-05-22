<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Page Rendering Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test the login page can be rendered.
     */
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Aplikasi');
    }

    /**
     * Test the register page can be rendered.
     */
    public function test_register_page_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Daftar Sekarang');
    }

    /*
    |--------------------------------------------------------------------------
    | Login Flow & Validation Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test users can authenticate using the login screen.
     */
    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    /**
     * Test admin users are redirected to the admin dashboard after login.
     */
    public function test_admin_users_are_redirected_to_admin_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin/dashboard');
    }

    /**
     * Test users cannot authenticate with invalid password.
     */
    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test login validation: email is required.
     */
    public function test_login_requires_email(): void
    {
        $response = $this->post('/login', [
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test login validation: email must be valid.
     */
    public function test_login_requires_valid_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'invalid-email-format',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test login validation: password is required.
     */
    public function test_login_requires_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'user@example.com',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('password');
    }

    /*
    |--------------------------------------------------------------------------
    | Registration Flow & Validation Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test new users can register.
     */
    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
        $response->assertRedirect('/');
    }

    /**
     * Test registration validation: name is required.
     */
    public function test_registration_requires_name(): void
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('name');
    }

    /**
     * Test registration validation: name cannot exceed 255 characters.
     */
    public function test_registration_name_cannot_exceed_max_length(): void
    {
        $response = $this->post('/register', [
            'name' => str_repeat('a', 256),
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('name');
    }

    /**
     * Test registration validation: email is required.
     */
    public function test_registration_requires_email(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test registration validation: email must be valid.
     */
    public function test_registration_requires_valid_email(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test registration validation: email must be unique.
     */
    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User 2',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test registration validation: password is required.
     */
    public function test_registration_requires_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('password');
    }

    /**
     * Test registration validation: password must be at least 8 characters.
     */
    public function test_registration_requires_password_min_length(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('password');
    }

    /**
     * Test registration validation: password confirmation must match.
     */
    public function test_registration_requires_password_confirmation_match(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('password');
    }

    /*
    |--------------------------------------------------------------------------
    | Middleware & Session Access Control Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test guests are redirected to login when accessing home page.
     */
    public function test_guests_are_redirected_to_login_when_accessing_home(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated users can access the home page.
     */
    public function test_authenticated_users_can_access_home(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test authenticated users visiting login page are redirected to home.
     */
    public function test_authenticated_users_are_redirected_from_login_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    }

    /**
     * Test authenticated users visiting register page are redirected to home.
     */
    public function test_authenticated_users_are_redirected_from_register_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/');
    }

    /**
     * Test users can logout.
     */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    /**
     * Test guests attempting to logout are redirected to login.
     */
    public function test_guests_cannot_logout(): void
    {
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
    }
}
