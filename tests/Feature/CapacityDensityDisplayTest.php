<?php

namespace Tests\Feature;

use App\Events\VillageDensityUpdated;
use App\Events\ZoneOccupancyUpdated;
use App\Models\CapacityZone;
use App\Models\User;
use App\Models\WeatherReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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

    public function test_village_density_and_zone_occupancy_broadcasts_are_deduplicated_when_count_unchanged(): void
    {
        Event::fake([VillageDensityUpdated::class, ZoneOccupancyUpdated::class]);

        CapacityZone::factory()->create([
            'zone_identifier' => 'desa_penglipuran',
            'max_capacity' => 10,
            'warning_threshold' => 60,
            'critical_threshold' => 80,
            'is_active' => true,
            'polygon_coordinates' => self::ZONE_POLYGON,
        ]);

        // 1st ping: visitor-1 enters zone -> count changes to 1 -> must broadcast
        $this->postJson('/api/tracking/ping', [
            'latitude' => 0.1,
            'longitude' => 0.1,
            'session_id' => 'visitor-1',
        ])->assertOk();

        Event::assertDispatchedTimes(VillageDensityUpdated::class, 1);
        Event::assertDispatchedTimes(ZoneOccupancyUpdated::class, 1);
        Event::assertDispatched(VillageDensityUpdated::class, function (VillageDensityUpdated $event): bool {
            return $event->totalCurrent === 1
                && $event->totalMax === 10
                && $event->densityPercent === 10
                && $event->status['key'] === 'safe';
        });

        // 2nd ping: same visitor moves inside the same zone -> count is still 1 -> must NOT re-broadcast
        $this->postJson('/api/tracking/ping', [
            'latitude' => 0.2,
            'longitude' => 0.2,
            'session_id' => 'visitor-1',
        ])->assertOk();

        Event::assertDispatchedTimes(VillageDensityUpdated::class, 1);
        Event::assertDispatchedTimes(ZoneOccupancyUpdated::class, 1);

        // 3rd ping: visitor-2 enters zone -> count changes to 2 -> must broadcast again
        $this->postJson('/api/tracking/ping', [
            'latitude' => 0.3,
            'longitude' => 0.3,
            'session_id' => 'visitor-2',
        ])->assertOk();

        Event::assertDispatchedTimes(VillageDensityUpdated::class, 2);
        Event::assertDispatchedTimes(ZoneOccupancyUpdated::class, 2);

        // Visitor leaves -> count drops to 1 -> must broadcast update
        $this->postJson('/api/tracking/leave', [
            'session_id' => 'visitor-2',
        ])->assertOk();

        Event::assertDispatchedTimes(VillageDensityUpdated::class, 3);
        Event::assertDispatchedTimes(ZoneOccupancyUpdated::class, 3);
    }

    public function test_weather_report_cached_avoids_duplicate_db_queries_and_invalidates_on_save(): void
    {
        WeatherReport::create([
            'id' => 1,
            'temperature' => 25.0,
            'condition' => 'Cerah',
            'weather_code' => 0,
            'humidity' => 80,
            'wind_speed' => 4.0,
        ]);

        // Prime cache
        $first = WeatherReport::cached();
        $this->assertNotNull($first);
        $this->assertEquals(25.0, (float) $first->temperature);

        // Second call must hit cache with 0 DB queries
        DB::enableQueryLog();
        DB::flushQueryLog();
        $second = WeatherReport::cached();
        $this->assertCount(0, DB::getQueryLog());
        $this->assertEquals(25.0, (float) $second->temperature);
        DB::disableQueryLog();

        // Updating model must flush cache via observer
        WeatherReport::whereKey(1)->first()->update(['temperature' => 29.5]);
        $updated = WeatherReport::cached();
        $this->assertEquals(29.5, (float) $updated->temperature);
    }
}
