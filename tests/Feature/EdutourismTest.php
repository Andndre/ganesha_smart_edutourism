<?php

namespace Tests\Feature;

use App\Models\TourRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EdutourismTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test public edutourism index page displays active routes.
     */
    public function test_edutourism_index_page_displays_active_routes(): void
    {
        // Arrange
        $activeRoute = TourRoute::create([
            'name' => 'Rute Edukasi Alam',
            'description' => 'Menjelajahi keindahan alam desa.',
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 45,
            'distance_meters' => 500,
            'is_smart_route' => true,
            'is_active' => true,
        ]);

        $inactiveRoute = TourRoute::create([
            'name' => 'Rute Rahasia',
            'description' => 'Rute tersembunyi yang belum aktif.',
            'difficulty' => 'challenging',
            'estimated_duration_minutes' => 90,
            'distance_meters' => 1200,
            'is_smart_route' => true,
            'is_active' => false,
        ]);

        // Act
        $response = $this->get(route('edutourism.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Rute Edukasi Alam');
        $response->assertSee('Menjelajahi keindahan alam desa.');
        $response->assertDontSee('Rute Rahasia');
    }

    /**
     * Test user can access starting a route preview and get active session redirection.
     */
    public function test_user_can_start_route_session(): void
    {
        // Arrange
        $user = User::factory()->create();
        $route = TourRoute::create([
            'name' => 'Rute Budaya',
            'description' => 'Mengenal adat istiadat desa.',
            'difficulty' => 'moderate',
            'estimated_duration_minutes' => 60,
            'distance_meters' => 800,
            'is_smart_route' => true,
            'is_active' => true,
        ]);

        // Act
        $response = $this->actingAs($user)
            ->postJson(route('edutourism.start', ['id' => $route->id]));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('edutourism.active'),
        ]);
    }

    /**
     * Test guest can access starting a route preview and get active session redirection with cookie.
     */
    public function test_guest_can_start_route_session_and_receives_cookie(): void
    {
        // Arrange
        $route = TourRoute::create([
            'name' => 'Rute Budaya',
            'description' => 'Mengenal adat istiadat desa.',
            'difficulty' => 'moderate',
            'estimated_duration_minutes' => 60,
            'distance_meters' => 800,
            'is_smart_route' => true,
            'is_active' => true,
        ]);

        // Act
        $response = $this->postJson(route('edutourism.start', ['id' => $route->id]));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('edutourism.active'),
        ]);
        $response->assertCookieNotExpired('visitor_token');
    }
}
