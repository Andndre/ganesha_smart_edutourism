<?php

namespace Tests\Browser\Admin;

use App\Models\CapacityZone;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminCapacityZoneTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_edit_zone_threshold_via_modal(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $zone = CapacityZone::create([
            'name' => 'Zona Dusk Threshold',
            'zone_identifier' => 'zona_dusk_threshold',
            'max_capacity' => 100,
            'warning_threshold' => 75,
            'critical_threshold' => 90,
            'current_count' => 0,
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $zone) {
            $browser->loginAs($admin)
                ->visit('/admin/capacity')
                ->waitForText('Sistem Peringatan Kapasitas')
                ->assertSee('Zona Dusk Threshold')
                ->script('openThresholdModal('.json_encode($zone->toArray(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).')');

            $browser->waitFor('#modal-name', 10)
                ->type('warning_threshold', '50')
                ->type('critical_threshold', '80')
                ->press('Simpan')
                ->waitUntilMissing('#modal-name')
                ->assertSee('berhasil');
        });
    }
}
