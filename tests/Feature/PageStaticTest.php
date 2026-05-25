<?php

namespace Tests\Feature;

use Tests\TestCase;

class PageStaticTest extends TestCase
{
    /**
     * Test terms page is publicly accessible and renders Indonesian by default.
     */
    public function test_terms_page_is_publicly_accessible_in_indonesian(): void
    {
        $response = $this->get('/terms');

        $response->assertStatus(200);
        $response->assertSee('Syarat & Ketentuan Penggunaan');
        $response->assertSee('Pendaftaran Akun');
        $response->assertSee('Kembali ke Pendaftaran');
        $response->assertDontSee('Terms & Conditions');
    }

    /**
     * Test privacy page is publicly accessible and renders Indonesian by default.
     */
    public function test_privacy_page_is_publicly_accessible_in_indonesian(): void
    {
        $response = $this->get('/privacy');

        $response->assertStatus(200);
        $response->assertSee('Kebijakan Privasi');
        $response->assertSee('Informasi yang Kami Kumpulkan');
        $response->assertSee('Kembali ke Pendaftaran');
        $response->assertDontSee('Information We Collect');
    }

    /**
     * Test terms page renders English content when session locale is set to en.
     */
    public function test_terms_page_renders_in_english_when_locale_active(): void
    {
        // Switch language via session
        $response = $this->withSession(['locale' => 'en'])->get('/terms');

        $response->assertStatus(200);
        $response->assertSee('Terms & Conditions');
        $response->assertSee('Account Registration');
        $response->assertSee('Back to Registration');
        $response->assertDontSee('Syarat & Ketentuan Penggunaan');
    }

    /**
     * Test privacy page renders English content when session locale is set to en.
     */
    public function test_privacy_page_renders_in_english_when_locale_active(): void
    {
        // Switch language via session
        $response = $this->withSession(['locale' => 'en'])->get('/privacy');

        $response->assertStatus(200);
        $response->assertSee('Privacy Policy');
        $response->assertSee('Information We Collect');
        $response->assertSee('Back to Registration');
        $response->assertDontSee('Informasi yang Kami Kumpulkan');
    }

    /**
     * Test registration page shows translated terms & privacy labels.
     */
    public function test_registration_page_shows_translated_labels(): void
    {
        // Default Indonesian
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Saya menyetujui');
        $response->assertSee('Syarat & Ketentuan');
        $response->assertSee('Kebijakan Privasi');

        // English
        $responseEn = $this->withSession(['locale' => 'en'])->get('/register');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('I agree to the');
        $responseEn->assertSee('Terms & Conditions');
        $responseEn->assertSee('Privacy Policy');
    }
}
