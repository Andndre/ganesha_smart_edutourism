<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\CulturalObjectQuiz;
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

    /**
     * Test arriving at a point with no quizzes automatically advances the session.
     */
    public function test_arrive_at_point_without_quizzes_advances_session(): void
    {
        // Arrange
        $user = User::factory()->create();
        $route = TourRoute::create([
            'name' => 'Rute Alam',
            'description' => 'Menikmati pemandangan.',
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 30,
            'distance_meters' => 300,
            'is_smart_route' => true,
            'is_active' => true,
        ]);

        $point1 = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::class,
            'locationable_id' => CulturalObject::create(['name' => 'Point 1', 'slug' => 'point-1', 'description' => 'Deskripsi objek budaya', 'category' => 'temple', 'ar_marker_id' => 'marker_point_1'])->id,
            'order' => 1,
        ]);

        $point2 = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::class,
            'locationable_id' => CulturalObject::create(['name' => 'Point 2', 'slug' => 'point-2', 'description' => 'Deskripsi objek budaya', 'category' => 'temple', 'ar_marker_id' => 'marker_point_2'])->id,
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
            'name' => 'Rute Alam Singkat',
            'description' => 'Pemandangan super singkat.',
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 15,
            'distance_meters' => 100,
            'is_smart_route' => true,
            'is_active' => true,
        ]);

        $point = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::class,
            'locationable_id' => CulturalObject::create(['name' => 'Last Point', 'slug' => 'last-point', 'description' => 'Deskripsi objek budaya', 'category' => 'temple', 'ar_marker_id' => 'marker_last_point'])->id,
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
        $this->assertEquals($point->id, $session->current_point_id);
        $this->assertEquals(1, $session->points_completed);
        $this->assertEquals('completed', $session->status);
    }

    /**
     * Test arriving at a point with quizzes does not advance the session.
     */
    public function test_arrive_at_point_with_quizzes_does_not_advance_session(): void
    {
        // Arrange
        $user = User::factory()->create();
        $route = TourRoute::create([
            'name' => 'Rute Berkuiz',
            'description' => 'Ada kuisnya.',
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 30,
            'distance_meters' => 350,
            'is_smart_route' => true,
            'is_active' => true,
        ]);

        $culturalObject = CulturalObject::create([
            'name' => 'Candi Berkuiz',
            'slug' => 'candi-berkuiz',
            'description' => 'Deskripsi objek budaya',
            'category' => 'temple',
            'ar_marker_id' => 'marker_candi_berkuiz',
        ]);

        // Add a quiz
        $quiz = CulturalObjectQuiz::create([
            'cultural_object_id' => $culturalObject->id,
            'question' => 'Apakah ini candi?',
            'option_a' => 'Ya',
            'option_b' => 'Tidak',
            'option_c' => 'Mungkin',
            'option_d' => 'Bukan',
            'correct_option' => 'A',
        ]);

        $point = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::class,
            'locationable_id' => $culturalObject->id,
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
        $response->assertJsonCount(1, 'quizzes');

        $session->refresh();
        $this->assertEquals($point->id, $session->current_point_id);
        $this->assertEquals(0, $session->points_completed);
        $this->assertEquals('active', $session->status);
    }
}
