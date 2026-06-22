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
     * Test arriving at a point with quizzes does not advance the session.
     */
    public function test_arrive_at_point_with_quizzes_does_not_advance_session(): void
    {
        // Arrange
        $user = User::factory()->create();
        $route = TourRoute::create([
            'name' => ['en' => 'Quiz Route', 'id' => 'Rute Berkuiz'],
            'description' => ['en' => 'Has quiz.', 'id' => 'Ada kuisnya.'],
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 30,
            'distance_meters' => 350,
            'is_active' => true,
        ]);

        $culturalObject = CulturalObject::create([
            'name' => ['en' => 'Quiz Temple', 'id' => 'Candi Berkuiz'],
            'slug' => 'candi-berkuiz',
            'description' => ['en' => 'Cultural description', 'id' => 'Deskripsi objek budaya'],
            'category' => 'temple',
            'ar_marker_id' => 'marker_candi_berkuiz',
        ]);

        // Add a quiz
        $quiz = CulturalObjectQuiz::create([
            'cultural_object_id' => $culturalObject->id,
            'question' => ['en' => 'Is this a temple?', 'id' => 'Apakah ini candi?'],
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
