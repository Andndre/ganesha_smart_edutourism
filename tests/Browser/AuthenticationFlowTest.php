<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthenticationFlowTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_admin_login_redirect(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->visit('/login')
                ->type('email', $admin->email)
                ->type('password', 'password123')
                ->press('Sign In to App')
                ->waitForLocation('/admin/dashboard')
                ->assertPathIs('/admin/dashboard')
                ->assertSee('Dashboard');
        });
    }

    public function test_staff_login_then_access_staff_route(): void
    {
        $staff = User::factory()->create([
            'role' => 'ticket_officer',
        ]);

        $this->browse(function (Browser $browser) use ($staff) {
            $browser->loginAs($staff)
                ->visit('/staff/ticketing')
                ->waitForText('Ticketing Point of Sale')
                ->assertSee('Ticketing Point of Sale');
        });
    }

    public function test_owner_login_then_access_owner_route(): void
    {
        $owner = User::factory()->create([
            'role' => 'umkm_owner',
        ]);

        $this->browse(function (Browser $browser) use ($owner) {
            $browser->loginAs($owner)
                ->visit('/owner/products')
                ->waitForText('Daftar Produk Toko')
                ->assertSee('Daftar Produk Toko');
        });
    }

    public function test_register_new_user(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('name', 'Dusk User Baru')
                ->type('email', 'dusk_user_baru@example.com')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->check('#terms')
                ->press('Register Now')
                ->waitForLocation('/', 15)
                ->assertPathIs('/')
                ->assertDontSee('Buat Akun');
        });
    }
}
