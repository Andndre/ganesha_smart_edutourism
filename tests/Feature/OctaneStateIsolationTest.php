<?php

namespace Tests\Feature;

use App\Services\TusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class OctaneStateIsolationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that translator locale is flushed and does not leak across requests.
     */
    public function test_translator_state_does_not_leak_across_operations(): void
    {
        // Request 1: User chooses English
        $response1 = $this->get('/?locale=en');
        $response1->assertStatus(200);
        $this->assertEquals('en', App::getLocale());

        // Simulate Octane OperationTerminated flushing bindings listed in config('octane.flush')
        foreach (Config::get('octane.flush', []) as $binding) {
            $this->app->forgetInstance($binding);
        }

        // Flush session for the next simulated visitor
        session()->flush();

        // Request 2: New unauthenticated visitor without locale query param
        $response2 = $this->get('/');
        $response2->assertStatus(200);

        // Translator must resolve back to default Indonesian ('id'), not contaminated with 'en'
        $this->assertEquals(config('app.locale', 'id'), App::getLocale());
    }

    /**
     * Test that concurrent tracking pings with atomic lock record visitor state reliably.
     */
    public function test_tracking_ping_stores_visitors_atomically(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->postJson('/api/tracking/ping', [
                'latitude' => -8.4223,
                'longitude' => 115.3594,
                'session_id' => "session_atom_{$i}",
                'user_name' => "Visitor {$i}",
            ]);
            $response->assertStatus(200)
                ->assertJson(['status' => 'tracked']);
        }

        $activeVisitors = Cache::get('active_visitors', []);
        for ($i = 1; $i <= 5; $i++) {
            $this->assertArrayHasKey("session_atom_{$i}", $activeVisitors);
        }
    }

    /**
     * Test that TusService throws clean RuntimeException on missing temp file instead of fatal error.
     */
    public function test_tus_service_handles_missing_file_cleanly(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Temp file not found: non-existent-uuid');

        TusService::moveFromTemp('non-existent-uuid.glb', 'models');
    }
}
