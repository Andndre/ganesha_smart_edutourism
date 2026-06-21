<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\RouteSession;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\User;
use App\Models\UserVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitRecordingTest extends TestCase
{
    use RefreshDatabase;

    public function test_visit_recorded_when_user_arrives_at_point(): void
    {
        $user = User::factory()->create();
        $culturalObject = CulturalObject::factory()->create();
        $route = TourRoute::factory()->create();
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

        $response = $this->actingAs($user)->getJson("/edutourism/arrive/{$point->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_visits', [
            'user_id' => $user->id,
            'visitable_type' => CulturalObject::class,
            'visitable_id' => $culturalObject->id,
            'route_session_id' => $session->id,
        ]);
    }

    public function test_visit_not_recorded_for_guest_users(): void
    {
        $culturalObject = CulturalObject::factory()->create();
        $route = TourRoute::factory()->create();
        $point = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::class,
            'locationable_id' => $culturalObject->id,
            'order' => 1,
        ]);

        $response = $this->getJson("/edutourism/arrive/{$point->id}");

        $response->assertOk();

        $this->assertDatabaseCount('user_visits', 0);
    }

    public function test_duplicate_arrival_does_not_create_duplicate_visit(): void
    {
        $user = User::factory()->create();
        $culturalObject = CulturalObject::factory()->create();
        $route = TourRoute::factory()->create();
        $point = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::class,
            'locationable_id' => $culturalObject->id,
            'order' => 1,
        ]);
        RouteSession::create([
            'user_id' => $user->id,
            'tour_route_id' => $route->id,
            'current_point_id' => $point->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)->getJson("/edutourism/arrive/{$point->id}");
        $this->actingAs($user)->getJson("/edutourism/arrive/{$point->id}");

        $this->assertDatabaseCount('user_visits', 1);
    }

    public function test_visit_records_correct_visitable_data(): void
    {
        $user = User::factory()->create();
        $culturalObject = CulturalObject::factory()->create();
        $route = TourRoute::factory()->create();
        $point = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::class,
            'locationable_id' => $culturalObject->id,
            'order' => 1,
        ]);
        RouteSession::create([
            'user_id' => $user->id,
            'tour_route_id' => $route->id,
            'current_point_id' => $point->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)->getJson("/edutourism/arrive/{$point->id}");

        $this->assertDatabaseHas('user_visits', [
            'visitable_type' => CulturalObject::class,
            'visitable_id' => $culturalObject->id,
        ]);
    }
}
