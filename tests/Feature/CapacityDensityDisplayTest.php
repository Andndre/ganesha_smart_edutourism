<?php

namespace Tests\Feature;

use App\Models\CapacityZone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CapacityDensityDisplayTest extends TestCase
{
    use RefreshDatabase;

    private const ZONE_POLYGON = [
        ['lat' => -1, 'lng' => -1],
        ['lat' => -1, 'lng' => 1],
        ['lat' => 1, 'lng' => 1],
        ['lat' => 1, 'lng' => -1],
    ];

    /**
     * Regression guard: current_count must come from live GPS pings
     * (App\Models\CapacityZone::withLiveCounts), never from the stale
     * `current_count` DB column — seeded/leftover values there must not leak
     * into the displayed visitor count.
     */
    public function test_home_page_density_modal_counts_live_visitors_using_dynamic_thresholds(): void
    {
        CapacityZone::factory()->create([
            'zone_identifier' => 'desa_penglipuran',
            'max_capacity' => 10,
            'current_count' => 999, // stale/seeded DB value — must NOT be shown
            'warning_threshold' => 70,
            'critical_threshold' => 95,
            'is_active' => true,
            'polygon_coordinates' => self::ZONE_POLYGON,
        ]);

        Cache::put('active_visitors', [
            'session-1' => ['lat' => 0, 'lng' => 0, 'last_seen' => now()->timestamp],
            'session-2' => ['lat' => 0.1, 'lng' => 0.1, 'last_seen' => now()->timestamp],
            'session-3' => ['lat' => 0.2, 'lng' => 0.2, 'last_seen' => now()->timestamp],
            'session-4' => ['lat' => 0.3, 'lng' => 0.3, 'last_seen' => now()->timestamp],
            'session-5' => ['lat' => 0.4, 'lng' => 0.4, 'last_seen' => now()->timestamp],
            'session-6' => ['lat' => 0.5, 'lng' => 0.5, 'last_seen' => now()->timestamp],
            'session-7' => ['lat' => 0.6, 'lng' => 0.6, 'last_seen' => now()->timestamp],
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('open-density-modal', false);
        $response->assertSee('7'); // live count, not the stale 0 in the DB column
        $response->assertSee('10');
        // 7/10 = 70% which is >= warning_threshold(70) => "Sedang", not hardcoded 60/80
        $response->assertSee('Sedang');
    }

    public function test_admin_capacity_overview_uses_zone_dynamic_thresholds_not_hardcoded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        CapacityZone::factory()->create([
            'zone_identifier' => 'desa_penglipuran',
            'max_capacity' => 1000,
            'current_count' => 650, // stale DB value — must be ignored, recomputed live as 0
            'warning_threshold' => 75,
            'critical_threshold' => 90,
            'is_active' => true,
            'polygon_coordinates' => [],
        ]);

        Cache::forget('active_visitors');

        $response = $this->actingAs($admin)->get('/admin/capacity');

        $response->assertStatus(200);
        $response->assertSee('Kapasitas Aman');
        $response->assertDontSee('Kapasitas Sedang');
        $response->assertDontSee('Kapasitas Penuh');
    }
}
