<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
     * Test that guest/unauthenticated users are blocked from admin routing.
     */
    public function test_unauthenticated_user_cannot_access_admin_routing(): void
    {
        $response = $this->postJson(route('admin.routing.directions'), [
            'coordinates' => [
                [115.35824, -8.43125],
                [115.35850, -8.43000],
            ],
        ]);

        // Admin route requires auth middleware, should return 401 Unauthorized for JSON requests
        $response->assertStatus(401);
    }

    /**
     * Test that a guest user can fetch routing directions from public route.
     */
    public function test_guest_can_get_routing_directions(): void
    {
        Http::fake([
            '*/ors/v2/directions/*' => Http::response([
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'properties' => [
                            'summary' => [
                                'distance' => 150.0,
                                'duration' => 120.0,
                            ],
                        ],
                        'geometry' => [
                            'type' => 'LineString',
                            'coordinates' => [
                                [115.35824, -8.43125],
                                [115.35850, -8.43000],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('routing.directions'), [
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
     * Test that an admin user can fetch routing directions from local ORS.
     */
    public function test_admin_user_can_get_routing_directions(): void
    {
        Http::fake([
            '*/ors/v2/directions/*' => Http::response([
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'properties' => [
                            'summary' => [
                                'distance' => 150.0,
                                'duration' => 120.0,
                            ],
                        ],
                        'geometry' => [
                            'type' => 'LineString',
                            'coordinates' => [
                                [115.35824, -8.43125],
                                [115.35850, -8.43000],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

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

        // Test non-numeric coordinates (prevents PHP 8.4 TypeError on division)
        $response3 = $this->actingAs($this->adminUser)
            ->postJson(route('admin.routing.directions'), [
                'coordinates' => [
                    ['invalid-lng', -8.43125],
                    [115.35850, 'invalid-lat'],
                ],
            ]);
        $response3->assertStatus(422);
        $response3->assertJsonValidationErrors(['coordinates.0.0', 'coordinates.1.1']);

        // Test out-of-range coordinates and excessive waypoints (> 25)
        $response4 = $this->actingAs($this->adminUser)
            ->postJson(route('admin.routing.directions'), [
                'coordinates' => array_fill(0, 26, [200.0, -95.0]),
            ]);
        $response4->assertStatus(422);
        $response4->assertJsonValidationErrors(['coordinates', 'coordinates.0.0', 'coordinates.0.1']);
    }

    /**
     * Test that identical route requests return cached response without calling ORS again.
     */
    public function test_identical_route_requests_return_cached_response_without_calling_ors_again(): void
    {
        Http::fake([
            '*/ors/v2/directions/*' => Http::response([
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'properties' => [
                            'summary' => ['distance' => 150.0, 'duration' => 120.0],
                        ],
                        'geometry' => [
                            'type' => 'LineString',
                            'coordinates' => [
                                [115.35825, -8.43125],
                                [115.35850, -8.43000],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $payload = [
            'coordinates' => [
                [115.35825, -8.43125],
                [115.35850, -8.43000],
            ],
        ];

        $firstResponse = $this->postJson(route('routing.directions'), $payload);
        $firstResponse->assertStatus(200)
            ->assertHeader('X-Cache', 'MISS');

        $secondResponse = $this->postJson(route('routing.directions'), $payload);
        $secondResponse->assertStatus(200)
            ->assertHeader('X-Cache', 'HIT')
            ->assertExactJson($firstResponse->json());

        Http::assertSentCount(1);
    }

    /**
     * Test that nearby coordinates within the ~5.5m grid (0.00005 deg) snap to the same cache key.
     */
    public function test_nearby_coordinates_within_5_5m_grid_share_cache_hit(): void
    {
        Http::fake([
            '*/ors/v2/directions/*' => Http::response([
                'type' => 'FeatureCollection',
                'features' => [],
            ], 200),
        ]);

        // Tourist A at [115.35824, -8.43126] -> snaps to [115.35825, -8.43125]
        $responseA = $this->postJson(route('routing.directions'), [
            'coordinates' => [
                [115.35824, -8.43126],
                [115.35851, -8.43001],
            ],
        ]);
        $responseA->assertStatus(200)->assertHeader('X-Cache', 'MISS');

        // Verify ORS received the snapped coordinates
        Http::assertSent(function ($request) {
            return $request['coordinates'] === [
                [115.35825, -8.43125],
                [115.35850, -8.43000],
            ];
        });

        // Tourist B ~2m away at [115.35826, -8.43124] -> also snaps to [115.35825, -8.43125]
        $responseB = $this->postJson(route('routing.directions'), [
            'coordinates' => [
                [115.35826, -8.43124],
                [115.35849, -8.42999],
            ],
        ]);
        $responseB->assertStatus(200)->assertHeader('X-Cache', 'HIT');

        // ORS must only have been called once
        Http::assertSentCount(1);
    }

    /**
     * Test that failed ORS responses are never cached in Redis.
     */
    public function test_failed_ors_response_is_not_cached(): void
    {
        Http::fakeSequence()
            ->push(['error' => 'ORS overloaded'], 503)
            ->push([
                'type' => 'FeatureCollection',
                'features' => [],
            ], 200);

        $payload = [
            'coordinates' => [
                [115.35825, -8.43125],
                [115.35850, -8.43000],
            ],
        ];

        // First call fails with 503
        $failedResponse = $this->postJson(route('routing.directions'), $payload);
        $failedResponse->assertStatus(503);

        // Second call hits ORS again (not cached) and succeeds
        $recoveredResponse = $this->postJson(route('routing.directions'), $payload);
        $recoveredResponse->assertStatus(200)
            ->assertHeader('X-Cache', 'MISS');

        Http::assertSentCount(2);
    }
}
