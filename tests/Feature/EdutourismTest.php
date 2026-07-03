<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\CulturalObjectQuiz;
use App\Models\QuizAnswer;
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

    /**
     * Test submitting an incorrect answer still advances the session and awards no points.
     */
    public function test_submit_incorrect_quiz_answer_still_advances_session(): void
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
            'slug' => 'candi-berkuiz-incorrect',
            'description' => ['en' => 'Cultural description', 'id' => 'Deskripsi objek budaya'],
            'category' => 'temple',
            'ar_marker_id' => 'marker_candi_berkuiz_incorrect',
        ]);

        $quiz = CulturalObjectQuiz::create([
            'cultural_object_id' => $culturalObject->id,
            'question' => ['en' => 'Is this a temple?', 'id' => 'Apakah ini candi?'],
            'option_a' => 'Ya',
            'option_b' => 'Tidak',
            'option_c' => 'Mungkin',
            'option_d' => 'Bukan',
            'correct_option' => 'A',
            'explanation' => ['en' => 'Yes, it is a temple.', 'id' => 'Ya, ini adalah candi.'],
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

        // Act — answer with the wrong option, as the last (only) quiz for this point
        $response = $this->actingAs($user)
            ->postJson(route('edutourism.quiz.submit', ['quizId' => $quiz->id]), [
                'answer' => 'B',
                'is_last_quiz' => true,
            ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_correct' => false,
            'correct_option' => 'A',
        ]);

        $session->refresh();
        $this->assertEquals(0, $session->total_score);
        $this->assertEquals(1, $session->points_completed);
        $this->assertEquals('completed', $session->status);
        $this->assertNull($session->current_point_id);

        $this->assertDatabaseHas('quiz_answers', [
            'route_session_id' => $session->id,
            'cultural_object_quiz_id' => $quiz->id,
            'selected_option' => 'B',
            'is_correct' => false,
        ]);
    }

    /**
     * Test re-submitting a quiz already answered correctly does not award points twice.
     */
    public function test_resubmitting_correct_quiz_answer_does_not_double_score(): void
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
            'slug' => 'candi-berkuiz-double-score',
            'description' => ['en' => 'Cultural description', 'id' => 'Deskripsi objek budaya'],
            'category' => 'temple',
            'ar_marker_id' => 'marker_candi_berkuiz_double_score',
        ]);

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

        // Act — answer correctly twice (simulating revisiting the point)
        $this->actingAs($user)->postJson(route('edutourism.quiz.submit', ['quizId' => $quiz->id]), [
            'answer' => 'A',
            'is_last_quiz' => true,
        ]);
        $session->refresh();
        $session->update(['status' => 'active', 'current_point_id' => $point->id]);

        $this->actingAs($user)->postJson(route('edutourism.quiz.submit', ['quizId' => $quiz->id]), [
            'answer' => 'A',
            'is_last_quiz' => true,
        ]);

        // Assert
        $session->refresh();
        $this->assertEquals(100, $session->total_score);
        $this->assertEquals(1, QuizAnswer::where('route_session_id', $session->id)->count());
    }
}
