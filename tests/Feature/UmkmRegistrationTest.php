<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class UmkmRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the UMKM registration page can be rendered.
     */
    public function test_umkm_register_page_can_be_rendered(): void
    {
        $response = $this->get('/mitra/register');

        $response->assertStatus(200);
        $response->assertSee('Daftar Mitra UMKM');
        $response->assertSee('Nama Usaha / Toko');
        $response->assertSee('Daftar menggunakan Google');
    }

    /**
     * Test a new UMKM owner can register successfully.
     */
    public function test_new_umkm_owner_can_register(): void
    {
        $response = $this->post('/mitra/register', [
            'name' => 'Wayan Sudarma',
            'business_name' => 'Warung Loloh Cemcem Wayan',
            'phone' => '081234567890',
            'email' => 'wayan@penglipuran.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'wayan@penglipuran.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserRole::UmkmOwner, $user->role);
        $this->assertEquals('081234567890', $user->phone);

        // Verify UmkmProfile was auto-created and is active
        $profile = UmkmProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertTrue($profile->is_active);
        $this->assertEquals('Wayan Sudarma', $profile->owner_name);

        $response->assertRedirect(route('owner.dashboard'));
    }

    /**
     * Test UMKM registration requires business name.
     */
    public function test_umkm_registration_requires_business_name(): void
    {
        $response = $this->post('/mitra/register', [
            'name' => 'Wayan Sudarma',
            'business_name' => '',
            'phone' => '081234567890',
            'email' => 'wayan@penglipuran.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('business_name');
    }

    /**
     * Test UMKM registration requires phone number.
     */
    public function test_umkm_registration_requires_phone(): void
    {
        $response = $this->post('/mitra/register', [
            'name' => 'Wayan Sudarma',
            'business_name' => 'Warung Cemcem',
            'phone' => '',
            'email' => 'wayan@penglipuran.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('phone');
    }

    /**
     * Test authenticated users cannot access UMKM registration page.
     */
    public function test_authenticated_users_are_redirected_from_umkm_register_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/mitra/register');

        $response->assertRedirect('/');
    }

    /**
     * Test Google OAuth redirect stores UMKM intent in session.
     */
    public function test_google_redirect_sets_umkm_intent(): void
    {
        $response = $this->get('/auth/google?intent=umkm');

        $response->assertStatus(302);
        $response->assertSessionHas('auth_intent', 'umkm');
    }

    /**
     * Test Google OAuth callback creates UMKM owner and profile when intent is umkm.
     */
    public function test_google_callback_creates_umkm_owner_with_intent(): void
    {
        $socialiteUser = new SocialiteUser;
        $socialiteUser->id = 'google-umkm-123';
        $socialiteUser->email = 'mitra_baru@penglipuran.com';
        $socialiteUser->name = 'Ketut Mitra';
        $socialiteUser->avatar = 'https://example.com/avatar.jpg';

        Socialite::fake('google', function () use ($socialiteUser) {
            return $socialiteUser;
        });

        // Seed session with umkm intent
        $response = $this->withSession(['auth_intent' => 'umkm'])
            ->get('/auth/google/callback?code=test_code&state=test_state');

        $this->assertAuthenticated();

        $user = User::where('email', 'mitra_baru@penglipuran.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserRole::UmkmOwner, $user->role);

        // Verify UmkmProfile was auto-created and active
        $profile = UmkmProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertTrue($profile->is_active);

        $response->assertRedirect(route('owner.dashboard'));
    }

    /**
     * Test Google callback upgrades existing tourist to UMKM owner when registering via intent.
     */
    public function test_google_callback_upgrades_existing_tourist_with_umkm_intent(): void
    {
        $existingTourist = User::factory()->create([
            'email' => 'tourist_to_umkm@penglipuran.com',
            'role' => UserRole::Tourist,
            'name' => 'Nyoman Wisata',
        ]);

        $socialiteUser = new SocialiteUser;
        $socialiteUser->id = 'google-tourist-456';
        $socialiteUser->email = 'tourist_to_umkm@penglipuran.com';
        $socialiteUser->name = 'Nyoman Wisata';
        $socialiteUser->avatar = 'https://example.com/avatar.jpg';

        Socialite::fake('google', function () use ($socialiteUser) {
            return $socialiteUser;
        });

        $response = $this->withSession(['auth_intent' => 'umkm'])
            ->get('/auth/google/callback?code=test_code&state=test_state');

        $existingTourist->refresh();
        $this->assertEquals(UserRole::UmkmOwner, $existingTourist->role);

        $profile = UmkmProfile::where('user_id', $existingTourist->id)->first();
        $this->assertNotNull($profile);
        $this->assertTrue($profile->is_active);

        $response->assertRedirect(route('owner.dashboard'));
    }
}
