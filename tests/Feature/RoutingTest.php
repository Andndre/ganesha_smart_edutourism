<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user to authenticate
        $this->adminUser = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);
    }

    /**
     * Test that guest/unauthenticated users are redirected/blocked.
     */
    public function test_unauthenticated_user_cannot_access_routing(): void
    {
        $response = $this->postJson(route('admin.routing.directions'), [
            'coordinates' => [
                [115.35824, -8.43125],
                [115.35850, -8.43000],
            ],
        ]);

        // Route requires auth middleware, should return 401 Unauthorized for JSON requests
        $response->assertStatus(401);
    }

    /**
     * Test that an admin user can fetch routing directions from local ORS.
     */
    public function test_admin_user_can_get_routing_directions(): void
    {
        // Query coordinates inside Desa Penglipuran, Bali
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.routing.directions'), [
                'coordinates' => [
                    [115.35824, -8.43125],
                    [115.35850, -8.43000],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'type',
            'features' => [
                '*' => [
                    'type',
                    'properties',
                    'geometry' => [
                        'type',
                        'coordinates',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test coordinate validation.
     */
    public function test_routing_validation_errors(): void
    {
        // Test missing coordinates
        $response1 = $this->actingAs($this->adminUser)
            ->postJson(route('admin.routing.directions'), []);
        $response1->assertStatus(422);
        $response1->assertJsonValidationErrors(['coordinates']);

        // Test malformed coordinates
        $response2 = $this->actingAs($this->adminUser)
            ->postJson(route('admin.routing.directions'), [
                'coordinates' => [
                    [115.35824], // needs 2 values (lat, lng)
                ],
            ]);
        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['coordinates']);
    }
}
