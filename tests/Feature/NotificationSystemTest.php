<?php

namespace Tests\Feature;

use App\Events\CrowdAlertSent;
use App\Events\EventReminderSent;
use App\Models\CapacityZone;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event as EventFacade;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test crowd alert is broadcast when a zone exceeds warning threshold.
     */
    public function test_crowd_alert_is_broadcast_when_zone_exceeds_warning_threshold(): void
    {
        EventFacade::fake([CrowdAlertSent::class]);

        // Arrange: Create a capacity zone with low capacity
        $zone = CapacityZone::create([
            'name' => 'Pura Penataran Test',
            'zone_identifier' => 'pura_test',
            'latitude' => -8.4201,
            'longitude' => 115.3595,
            'radius_meters' => 100,
            'max_capacity' => 10,
            'warning_threshold' => 70,
            'critical_threshold' => 90,
            'current_count' => 0,
            'is_active' => true,
        ]);

        // Arrange: Seed cache with enough visitors inside the zone to exceed warning (8/10 = 80%)
        $activeVisitors = [];
        for ($i = 0; $i < 8; $i++) {
            $activeVisitors["session-$i"] = [
                'lat' => -8.4201 + ($i * 0.00001),
                'lng' => 115.3595,
                'last_seen' => now()->timestamp,
            ];
        }
        Cache::put('active_visitors', $activeVisitors, now()->addMinutes(5));

        // Act: Send a GPS ping from inside the zone
        $response = $this->postJson('/api/tracking/ping', [
            'latitude' => -8.4201,
            'longitude' => 115.3595,
            'session_id' => 'new-visitor',
        ]);

        // Assert
        $response->assertStatus(200);
        EventFacade::assertDispatched(CrowdAlertSent::class, function ($event) use ($zone) {
            return $event->zoneName === $zone->name
                && in_array($event->level, ['warning', 'critical']);
        });
    }

    /**
     * Test crowd alert is not broadcast again within cooldown period.
     */
    public function test_crowd_alert_respects_cooldown_period(): void
    {
        EventFacade::fake([CrowdAlertSent::class]);

        // Arrange
        $zone = CapacityZone::create([
            'name' => 'Zona Cooldown Test',
            'zone_identifier' => 'cooldown_test',
            'latitude' => -8.4201,
            'longitude' => 115.3595,
            'radius_meters' => 100,
            'max_capacity' => 10,
            'warning_threshold' => 70,
            'critical_threshold' => 90,
            'current_count' => 0,
            'is_active' => true,
        ]);

        // Simulate cooldown already in place
        Cache::put("crowd_alert_sent:{$zone->id}", true, now()->addMinutes(5));

        $activeVisitors = [];
        for ($i = 0; $i < 8; $i++) {
            $activeVisitors["session-$i"] = [
                'lat' => -8.4201,
                'lng' => 115.3595,
                'last_seen' => now()->timestamp,
            ];
        }
        Cache::put('active_visitors', $activeVisitors, now()->addMinutes(5));

        // Act
        $this->postJson('/api/tracking/ping', [
            'latitude' => -8.4201,
            'longitude' => 115.3595,
            'session_id' => 'cooldown-test',
        ]);

        // Assert: No alert broadcast due to cooldown
        EventFacade::assertNotDispatched(CrowdAlertSent::class);
    }

    /**
     * Test event reminders command broadcasts for upcoming events.
     */
    public function test_event_reminders_command_broadcasts_for_upcoming_events(): void
    {
        EventFacade::fake([EventReminderSent::class]);

        // Arrange: Create an event starting in 15 minutes
        Event::create([
            'name' => 'Tari Barong Penglipuran',
            'slug' => 'tari-barong-test',
            'category' => 'cultural',
            'start_datetime' => now()->addMinutes(15),
            'end_datetime' => now()->addMinutes(75),
            'is_free' => true,
            'location_name' => 'Balai Banjar',
        ]);

        // Arrange: Create an event starting in 2 hours (should NOT be reminded)
        Event::create([
            'name' => 'Workshop Bambu',
            'slug' => 'workshop-bambu-test',
            'category' => 'workshop',
            'start_datetime' => now()->addHours(2),
            'end_datetime' => now()->addHours(4),
            'is_free' => false,
            'price' => 50000,
            'location_name' => 'Area Bambu',
        ]);

        // Act
        $this->artisan('events:send-reminders')
            ->assertSuccessful();

        // Assert
        EventFacade::assertDispatched(EventReminderSent::class, function ($event) {
            return $event->eventName === 'Tari Barong Penglipuran'
                && $event->locationName === 'Balai Banjar';
        });

        EventFacade::assertNotDispatched(EventReminderSent::class, function ($event) {
            return $event->eventName === 'Workshop Bambu';
        });
    }

    /**
     * Test event reminders command does nothing when no upcoming events exist.
     */
    public function test_event_reminders_command_does_nothing_when_no_upcoming_events(): void
    {
        EventFacade::fake([EventReminderSent::class]);

        // Act
        $this->artisan('events:send-reminders')
            ->assertSuccessful();

        // Assert
        EventFacade::assertNotDispatched(EventReminderSent::class);
    }

    /**
     * Test tracking ping endpoint validates required fields.
     */
    public function test_tracking_ping_validates_required_fields(): void
    {
        $response = $this->postJson('/api/tracking/ping', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['latitude', 'longitude', 'session_id']);
    }
}
