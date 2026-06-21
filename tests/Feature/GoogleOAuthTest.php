<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test OAuth redirect returns 302 to Google.
     */
    public function test_redirect_to_google_returns_redirect()
    {
        $response = $this->get('/auth/google');
        $this->assertEquals(302, $response->status());
    }

    /**
     * Test callback creates new user if email doesn't exist.
     */
    public function test_callback_creates_new_user()
    {
        $socialiteUser = new SocialiteUser;
        $socialiteUser->id = 'google-id-123';
        $socialiteUser->email = 'newuser@test.com';
        $socialiteUser->name = 'New User';
        $socialiteUser->avatar = 'https://example.com/avatar.jpg';

        Socialite::fake('google', function () use ($socialiteUser) {
            return $socialiteUser;
        });

        $response = $this->get('/auth/google/callback?code=test_code&state=test_state');

        $this->assertContains($response->status(), [301, 302, 303, 307, 308]);

        $user = User::where('email', 'newuser@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('google-id-123', $user->google_id);
        $this->assertNull($user->password);
    }

    /**
     * Test callback auto-links existing user.
     */
    public function test_callback_auto_links_existing_user()
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@test.com',
            'password' => bcrypt('password123'),
            'google_id' => null,
        ]);

        $socialiteUser = new SocialiteUser;
        $socialiteUser->id = 'google-id-456';
        $socialiteUser->email = 'existing@test.com';
        $socialiteUser->name = 'Existing User';
        $socialiteUser->avatar = 'https://example.com/avatar.jpg';

        Socialite::fake('google', function () use ($socialiteUser) {
            return $socialiteUser;
        });

        $response = $this->get('/auth/google/callback?code=test_code&state=test_state');

        $user = User::where('email', 'existing@test.com')->first();
        $this->assertEquals('google-id-456', $user->google_id);
        $this->assertNotNull($user->password); // Password still exists
    }

    /**
     * Test user is logged in after callback.
     */
    public function test_user_logged_in_after_callback()
    {
        $socialiteUser = new SocialiteUser;
        $socialiteUser->id = 'google-id-789';
        $socialiteUser->email = 'login@test.com';
        $socialiteUser->name = 'Login User';
        $socialiteUser->avatar = 'https://example.com/avatar.jpg';

        Socialite::fake('google', function () use ($socialiteUser) {
            return $socialiteUser;
        });

        $response = $this->get('/auth/google/callback?code=test_code&state=test_state');

        $this->assertAuthenticated();
        $user = auth()->user();
        $this->assertEquals('login@test.com', $user->email);
    }
}
