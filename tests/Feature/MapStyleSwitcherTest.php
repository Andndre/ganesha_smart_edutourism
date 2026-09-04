<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * map-style-script dipakai bersama oleh /explore dan lima peta editor di admin/owner.
 * Peta editor itu dibuat dengan `attributionControl: false`, jadi apa pun yang
 * ditambahkan ke initMapStyleSwitcher harus aman saat kontrol tersebut tidak ada —
 * kalau melempar, initMap ikut gagal dan seluruh peta admin kosong.
 */
class MapStyleSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_map_manager_page_renders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.map-manager'))
            ->assertOk();
    }

    /**
     * Change-detector, disengaja: ini mencocokkan teks, bukan menjalankan JS-nya.
     * Penjaga sesungguhnya adalah optional chaining di skripnya; assertion ini hanya
     * memastikan penjaga itu tidak terhapus tanpa sengaja.
     */
    public function test_attribution_prefix_call_is_guarded(): void
    {
        $this->get('/explore')
            ->assertOk()
            ->assertSee('map.attributionControl?.setPrefix', false);
    }
}
