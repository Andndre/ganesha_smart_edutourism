<?php

namespace Tests\Feature;

use App\Events\VisitorLocationUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class VisitorLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_broadcasts_with_user_name(): void
    {
        Event::fake();

        event(new VisitorLocationUpdated(-8.419, 115.321, 'test-session', 'John Doe'));

        Event::assertDispatched(VisitorLocationUpdated::class, function ($event) {
            return $event->userName === 'John Doe';
        });
    }

    public function test_event_broadcasts_without_user_name(): void
    {
        Event::fake();

        event(new VisitorLocationUpdated(-8.419, 115.321, 'test-session'));

        Event::assertDispatched(VisitorLocationUpdated::class, function ($event) {
            return $event->userName === null;
        });
    }

    public function test_ping_stores_user_name_in_cache(): void
    {
        Cache::spy();

        $response = $this->postJson('/api/tracking/ping', [
            'latitude' => -8.419,
            'longitude' => 115.321,
            'session_id' => 'test-cache-123',
            'user_name' => 'Jane Doe',
        ]);

        $response->assertStatus(200);

        Cache::shouldHaveReceived('get')
            ->with('active_visitors', [])
            ->atLeast()->once();
    }

    public function test_ping_with_invalid_user_name_returns_validation_error(): void
    {
        $response = $this->postJson('/api/tracking/ping', [
            'latitude' => -8.419,
            'longitude' => 115.321,
            'session_id' => 'test-cache-456',
            'user_name' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_name']);
    }
}
