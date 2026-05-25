<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest can switch language via route.
     */
    public function test_guest_can_switch_language_via_route(): void
    {
        $response = $this->get('/lang/en');

        $response->assertSessionHas('locale', 'en');
        $response->assertRedirect();
    }

    /**
     * Test authenticated user can switch language and persist in database.
     */
    public function test_authenticated_user_can_switch_language_and_persist(): void
    {
        $user = User::factory()->create([
            'preferred_language' => 'id',
        ]);

        $response = $this->actingAs($user)->get('/lang/en');

        $response->assertSessionHas('locale', 'en');
        $response->assertRedirect();
        $this->assertEquals('en', $user->fresh()->preferred_language);
    }

    /**
     * Test that translations are correctly rendered based on active locale.
     */
    public function test_translations_are_correctly_rendered_on_profile_edit_page(): void
    {
        $user = User::factory()->create([
            'preferred_language' => 'en',
        ]);

        // Access the page with English locale active
        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertSee('Profile Information');
        $response->assertSee('Full Name');
        $response->assertDontSee('Nama Lengkap');
        $response->assertDontSee('Informasi Profil');
    }
}
