<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test admin login flow and redirect to dashboard.
     */
    public function test_admin_login(): void
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

    /**
     * Test capacity zones management page.
     */
    public function test_admin_capacity_zones(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/capacity')
                ->waitForText('Sistem Peringatan Kapasitas')
                ->assertSee('Daftar Zona Kapasitas')
                ->press('+ Buat Zona Baru')
                ->waitForText('Buat Zona Baru')
                ->type('name', 'Zona Test Dusk')
                ->type('zone_identifier', 'zona_test_dusk')
                ->type('max_capacity', '100')
                ->type('warning_threshold', '75')
                ->type('critical_threshold', '90')
                ->press('Simpan')
                ->waitForText('Zona Test Dusk')
                ->assertSee('Zona Test Dusk');
        });
    }

    /**
     * Test UMKM categories management page.
     */
    public function test_admin_umkm_categories(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/umkm/categories')
                ->script("localStorage.setItem('umkm_categories_tour_completed', 'true');");

            $browser->refresh()
                ->waitForText('Kategori Produk UMKM')
                ->click('#tour-add-btn')
                ->waitForText('Tambah Kategori Produk')
                ->waitFor('#field-name-en')
                // The form default locale is 'en'
                ->type('name[en]', 'Dusk Test Category EN')
                // Switch language to 'id' using the locale button click
                ->press('Indonesia')
                ->waitFor('#field-name-id')
                ->type('name[id]', 'Dusk Test Kategori ID')
                ->press('Simpan')
                ->waitForText('Dusk Test Kategori ID')
                ->assertSee('Dusk Test Kategori ID');
        });
    }
}
