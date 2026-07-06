<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\RouteMission;
use App\Models\RouteSession;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteMissionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private TourRoute $route;

    private TourRoutePoint $pointOne;

    private TourRoutePoint $pointTwo;

    private RouteMission $missionA;

    private RouteMission $missionB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->route = TourRoute::create([
            'name' => ['en' => 'Penglipuran Heritage Quest', 'id' => 'Penglipuran Heritage Quest'],
            'description' => ['en' => 'Test route.', 'id' => 'Rute uji.'],
            'difficulty' => 'easy',
            'gamification_key' => 'heritage_quest',
            'estimated_duration_minutes' => 90,
            'distance_meters' => 1200,
            'is_active' => true,
        ]);

        $objectOne = CulturalObject::factory()->create();
        $objectTwo = CulturalObject::factory()->create();

        $this->pointOne = TourRoutePoint::create([
            'tour_route_id' => $this->route->id,
            'locationable_type' => $objectOne->getMorphClass(),
            'locationable_id' => $objectOne->id,
            'order' => 1,
            'qr_code_token' => 'EDU-TEST-P1',
        ]);

        $this->pointTwo = TourRoutePoint::create([
            'tour_route_id' => $this->route->id,
            'locationable_type' => $objectTwo->getMorphClass(),
            'locationable_id' => $objectTwo->id,
            'order' => 2,
        ]);

        $this->missionA = RouteMission::create([
            'tour_route_point_id' => $this->pointOne->id,
            'type' => 'riddle',
            'title' => ['en' => 'Riddle', 'id' => 'Teka-teki'],
            'config' => ['riddle' => ['en' => 'Who am I?', 'id' => 'Siapakah aku?'], 'answers' => ['merajan']],
            'points' => 100,
            'order' => 1,
        ]);

        $this->missionB = RouteMission::create([
            'tour_route_point_id' => $this->pointOne->id,
            'type' => 'matching',
            'title' => ['en' => 'Match', 'id' => 'Cocokkan'],
            'config' => ['mode' => 'match', 'pairs' => []],
            'points' => 100,
            'order' => 2,
        ]);
    }

    protected function startSession(): RouteSession
    {
        return RouteSession::create([
            'user_id' => $this->user->id,
            'tour_route_id' => $this->route->id,
            'current_point_id' => $this->pointOne->id,
            'status' => 'active',
        ]);
    }

    public function test_arrive_returns_missions_flag_without_advancing(): void
    {
        $session = $this->startSession();

        $response = $this->actingAs($this->user)->getJson("/edutourism/arrive/{$this->pointOne->id}");

        $response->assertOk()->assertJson(['success' => true, 'has_missions' => true]);
        $this->assertEquals($this->pointOne->id, $session->fresh()->current_point_id);
    }

    public function test_complete_mission_clamps_score_and_dedupes(): void
    {
        $session = $this->startSession();

        // Score is clamped to the mission cap (100), not the reported 9999.
        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$this->missionA->id}/complete", ['earned' => 9999])
            ->assertOk()
            ->assertJson(['success' => true, 'is_last_mission' => false]);

        $this->assertEquals(100, $session->fresh()->total_score);

        // Re-submitting the same mission adds nothing.
        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$this->missionA->id}/complete", ['earned' => 80])
            ->assertOk();

        $this->assertEquals(100, $session->fresh()->total_score);
        $this->assertEquals([$this->missionA->id], $session->fresh()->missions_completed);
    }

    public function test_last_mission_advances_to_next_point(): void
    {
        $session = $this->startSession();

        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$this->missionA->id}/complete", ['earned' => 60])
            ->assertOk();

        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$this->missionB->id}/complete", ['earned' => 70])
            ->assertOk()
            ->assertJson(['is_last_mission' => true, 'session_status' => 'active']);

        $fresh = $session->fresh();
        $this->assertEquals($this->pointTwo->id, $fresh->current_point_id);
        $this->assertEquals(130, $fresh->total_score);
        $this->assertEquals(1, $fresh->points_completed);
    }

    public function test_mission_of_other_point_is_rejected(): void
    {
        $this->startSession();

        $otherMission = RouteMission::create([
            'tour_route_point_id' => $this->pointTwo->id,
            'type' => 'riddle',
            'title' => ['en' => 'Other', 'id' => 'Lain'],
            'config' => [],
            'points' => 100,
            'order' => 1,
        ]);

        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$otherMission->id}/complete", ['earned' => 50])
            ->assertStatus(422);
    }

    public function test_completing_route_awards_relative_badge(): void
    {
        // A previous champion with 150 points.
        RouteSession::create([
            'guest_token' => 'visitor_previous',
            'tour_route_id' => $this->route->id,
            'total_score' => 150,
            'status' => 'completed',
        ]);

        $session = $this->startSession();
        $session->update(['current_point_id' => $this->pointTwo->id, 'total_score' => 200]);

        $lastMission = RouteMission::create([
            'tour_route_point_id' => $this->pointTwo->id,
            'type' => 'riddle',
            'title' => ['en' => 'Final', 'id' => 'Terakhir'],
            'config' => [],
            'points' => 100,
            'order' => 1,
        ]);

        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$lastMission->id}/complete", ['earned' => 0])
            ->assertOk()
            ->assertJson(['session_status' => 'completed']);

        $this->assertEquals('Penglipuran Heritage Explorer', $session->fresh()->badge_awarded);
    }

    public function test_lower_score_gets_no_badge(): void
    {
        RouteSession::create([
            'guest_token' => 'visitor_previous',
            'tour_route_id' => $this->route->id,
            'total_score' => 900,
            'status' => 'completed',
        ]);

        $session = $this->startSession();
        $session->update(['current_point_id' => $this->pointTwo->id, 'total_score' => 10]);

        $lastMission = RouteMission::create([
            'tour_route_point_id' => $this->pointTwo->id,
            'type' => 'riddle',
            'title' => ['en' => 'Final', 'id' => 'Terakhir'],
            'config' => [],
            'points' => 100,
            'order' => 1,
        ]);

        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$lastMission->id}/complete", ['earned' => 0])
            ->assertOk();

        $this->assertNull($session->fresh()->badge_awarded);
    }

    public function test_qr_resolve_matches_current_point_token(): void
    {
        $this->startSession();

        $this->actingAs($this->user)
            ->postJson('/edutourism/qr/resolve', ['code' => 'EDU-TEST-P1'])
            ->assertOk()
            ->assertJson(['success' => true, 'point_id' => $this->pointOne->id]);

        // Full URL payloads are normalized too.
        $this->actingAs($this->user)
            ->postJson('/edutourism/qr/resolve', ['code' => 'https://example.com/edutourism/qr/EDU-TEST-P1'])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_qr_resolve_rejects_unknown_and_wrong_point(): void
    {
        $session = $this->startSession();

        $this->actingAs($this->user)
            ->postJson('/edutourism/qr/resolve', ['code' => 'TOTALLY-UNKNOWN'])
            ->assertStatus(422);

        // Token of a non-current point in the same route is rejected (gate-fenced).
        $this->pointTwo->update(['qr_code_token' => 'EDU-TEST-P2']);
        $this->actingAs($this->user)
            ->postJson('/edutourism/qr/resolve', ['code' => 'EDU-TEST-P2'])
            ->assertStatus(422);

        $this->assertEquals($this->pointOne->id, $session->fresh()->current_point_id);
    }

    public function test_first_point_quiz_success_awards_digital_passport(): void
    {
        // Point 1's quiz is now a RouteMission (500-point cap, 5 questions) — replace
        // the generic missionA/missionB from setUp() with a single quiz mission.
        RouteMission::where('tour_route_point_id', $this->pointOne->id)->delete();
        $quizMission = RouteMission::create([
            'tour_route_point_id' => $this->pointOne->id,
            'type' => 'quiz',
            'title' => ['en' => 'Unlock the Village', 'id' => 'Buka Gerbang Desa'],
            'config' => ['questions' => []],
            'points' => 500,
            'order' => 1,
        ]);

        $session = $this->startSession();

        // 4/5 correct (80%) meets the threshold.
        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$quizMission->id}/complete", ['earned' => 400])
            ->assertOk()
            ->assertJson(['is_last_mission' => true]);

        $fresh = $session->fresh();
        $this->assertContains('digital_passport', $fresh->collectibles_earned ?? []);
        $this->assertEquals($this->pointTwo->id, $fresh->current_point_id);
    }

    public function test_low_quiz_score_gets_no_passport(): void
    {
        RouteMission::where('tour_route_point_id', $this->pointOne->id)->delete();
        $quizMission = RouteMission::create([
            'tour_route_point_id' => $this->pointOne->id,
            'type' => 'quiz',
            'title' => ['en' => 'Unlock the Village', 'id' => 'Buka Gerbang Desa'],
            'config' => ['questions' => []],
            'points' => 500,
            'order' => 1,
        ]);

        $session = $this->startSession();

        // 1/5 correct (20%) misses the 80% threshold.
        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$quizMission->id}/complete", ['earned' => 100])
            ->assertOk();

        $this->assertEmpty($session->fresh()->collectibles_earned ?? []);
    }

    public function test_active_page_renders_locale_specific_intro_media(): void
    {
        $this->pointOne->update([
            'intro_video_paths' => ['id' => 'route_point_media/vid-id.mp4', 'en' => 'route_point_media/vid-en.mp4'],
            'intro_audio_paths' => ['id' => 'route_point_media/aud-id.mp3', 'en' => 'route_point_media/aud-en.mp3'],
        ]);
        $this->startSession();

        // APP_LOCALE=id in phpunit.xml — default locale renders the Indonesian paths.
        $this->actingAs($this->user)->get('/edutourism/active')
            ->assertOk()
            ->assertSee('/audio-stream/route_point_media/vid-id.mp4', false)
            ->assertSee('/audio-stream/route_point_media/aud-id.mp3', false)
            ->assertDontSee('vid-en.mp4', false);

        // SetUserLocale middleware switches on ?locale= — English paths render instead.
        $this->actingAs($this->user)->get('/edutourism/active?locale=en')
            ->assertOk()
            ->assertSee('/audio-stream/route_point_media/vid-en.mp4', false)
            ->assertSee('/audio-stream/route_point_media/aud-en.mp3', false);
    }
}
