<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\RouteSession;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
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
            'name' => ['en' => 'Nature Education Route', 'id' => 'Rute Edukasi Alam'],
            'description' => ['en' => 'Exploring the beauty of the village nature.', 'id' => 'Menjelajahi keindahan alam desa.'],
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 45,
            'distance_meters' => 500,
            'is_active' => true,
        ]);

        $inactiveRoute = TourRoute::create([
            'name' => ['en' => 'Secret Route', 'id' => 'Rute Rahasia'],
            'description' => ['en' => 'Hidden route not yet active.', 'id' => 'Rute tersembunyi yang belum aktif.'],
            'difficulty' => 'challenging',
            'estimated_duration_minutes' => 90,
            'distance_meters' => 1200,
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
            'name' => ['en' => 'Cultural Route', 'id' => 'Rute Budaya'],
            'description' => ['en' => 'Learn about village customs.', 'id' => 'Mengenal adat istiadat desa.'],
            'difficulty' => 'moderate',
            'estimated_duration_minutes' => 60,
            'distance_meters' => 800,
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
            'name' => ['en' => 'Cultural Route', 'id' => 'Rute Budaya'],
            'description' => ['en' => 'Learn about village customs.', 'id' => 'Mengenal adat istiadat desa.'],
            'difficulty' => 'moderate',
            'estimated_duration_minutes' => 60,
            'distance_meters' => 800,
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

    /**
     * Test a stale session whose user no longer exists (e.g. after migrate:fresh)
     * falls back to a guest session instead of throwing a foreign key violation.
     */
    public function test_stale_authenticated_session_falls_back_to_guest_session(): void
    {
        // Arrange
        $user = User::factory()->create();
        $route = TourRoute::create([
            'name' => ['en' => 'Cultural Route', 'id' => 'Rute Budaya'],
            'description' => ['en' => 'Learn about village customs.', 'id' => 'Mengenal adat istiadat desa.'],
            'difficulty' => 'moderate',
            'estimated_duration_minutes' => 60,
            'distance_meters' => 800,
            'is_active' => true,
        ]);

        // Simulate a stale session: the session still references the user, but
        // the user record has been removed from the database.
        $user->delete();

        // Act
        $response = $this->actingAs($user)
            ->postJson(route('edutourism.start', ['id' => $route->id]));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('edutourism.active'),
        ]);

        $this->assertDatabaseHas('route_sessions', [
            'tour_route_id' => $route->id,
            'status' => 'active',
            'user_id' => null,
        ]);
    }

    /**
     * Test arriving at a point with no quizzes automatically advances the session.
     */
    public function test_arrive_at_point_without_quizzes_advances_session(): void
    {
        // Arrange
        $user = User::factory()->create();
        $route = TourRoute::create([
            'name' => ['en' => 'Nature Route', 'id' => 'Rute Alam'],
            'description' => ['en' => 'Enjoy the scenery.', 'id' => 'Menikmati pemandangan.'],
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 30,
            'distance_meters' => 300,
            'is_active' => true,
        ]);

        $point1 = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::class,
            'locationable_id' => CulturalObject::create(['name' => ['en' => 'Point 1', 'id' => 'Point 1'], 'slug' => 'point-1', 'description' => ['en' => 'Cultural description', 'id' => 'Deskripsi objek budaya'], 'category' => 'temple', 'ar_marker_id' => 'marker_point_1'])->id,
            'order' => 1,
        ]);

        $point2 = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::class,
            'locationable_id' => CulturalObject::create(['name' => ['en' => 'Point 2', 'id' => 'Point 2'], 'slug' => 'point-2', 'description' => ['en' => 'Cultural description', 'id' => 'Deskripsi objek budaya'], 'category' => 'temple', 'ar_marker_id' => 'marker_point_2'])->id,
            'order' => 2,
        ]);

        $session = RouteSession::create([
            'user_id' => $user->id,
            'tour_route_id' => $route->id,
            'current_point_id' => $point1->id,
            'status' => 'active',
        ]);

        // Act
        $response = $this->actingAs($user)
            ->get(route('edutourism.arrive', ['pointId' => $point1->id]));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $session->refresh();
        $this->assertEquals($point2->id, $session->current_point_id);
        $this->assertEquals(1, $session->points_completed);
        $this->assertEquals('active', $session->status);
    }

    /**
     * Test arriving at the last point with no quizzes completes the session.
     */
    public function test_arrive_at_last_point_without_quizzes_completes_session(): void
    {
        // Arrange
        $user = User::factory()->create();
        $route = TourRoute::create([
            'name' => ['en' => 'Short Nature Route', 'id' => 'Rute Alam Singkat'],
            'description' => ['en' => 'Very short scenery.', 'id' => 'Pemandangan super singkat.'],
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 15,
            'distance_meters' => 100,
            'is_active' => true,
        ]);

        $point = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::class,
            'locationable_id' => CulturalObject::create(['name' => ['en' => 'Last Point', 'id' => 'Last Point'], 'slug' => 'last-point', 'description' => ['en' => 'Cultural description', 'id' => 'Deskripsi objek budaya'], 'category' => 'temple', 'ar_marker_id' => 'marker_last_point'])->id,
            'order' => 1,
        ]);

        $session = RouteSession::create([
            'user_id' => $user->id,
            'tour_route_id' => $route->id,
            'current_point_id' => $point->id,
            'status' => 'active',
        ]);

        // Act
        $response = $this->actingAs($user)
            ->get(route('edutourism.arrive', ['pointId' => $point->id]));

        // Assert
        $response->assertStatus(200);

        $session->refresh();
        $this->assertNull($session->current_point_id);
        $this->assertEquals(1, $session->points_completed);
        $this->assertEquals('completed', $session->status);
    }

    /**
     * Test authenticated user can stop their active route session.
     */
    public function test_authenticated_user_can_stop_active_session(): void
    {
        // Arrange
        $user = User::factory()->create();
        $session = RouteSession::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        // Act
        $response = $this->actingAs($user)
            ->postJson(route('edutourism.stop'));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('edutourism.index'),
        ]);

        $session->refresh();
        $this->assertEquals('abandoned', $session->status);
    }

    /**
     * Test guest can stop their active route session via guest_token.
     */
    public function test_guest_can_stop_active_session(): void
    {
        // Arrange
        $session = RouteSession::factory()->create([
            'user_id' => null,
            'guest_token' => 'guest_stop_test_token',
            'status' => 'active',
        ]);

        // Act
        $response = $this->withSession(['guest_token' => 'guest_stop_test_token'])
            ->postJson(route('edutourism.stop'));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('edutourism.index'),
        ]);

        $session->refresh();
        $this->assertEquals('abandoned', $session->status);
    }

    /**
     * Test stopping with no active session is a harmless no-op.
     */
    public function test_stop_with_no_active_session_is_idempotent(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->postJson(route('edutourism.stop'));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('edutourism.index'),
        ]);
    }
}
