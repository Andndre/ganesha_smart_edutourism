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

/**
 * Hari 4 Fase 2: avatar picker, per-point collectibles (heritage_key/eco_crystal),
 * and Route 2/3 badge tiers. Route 1's own badge/collectible tests already live in
 * RouteMissionTest — this file only covers what Phase 2 added.
 */
class RouteGamificationPhase2Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function makeRoute(string $name, ?string $gamificationKey = null): TourRoute
    {
        // Derive the key from the name so existing tests keep their intent, but the
        // column — not the name — is what the controller now reads.
        $gamificationKey ??= match (true) {
            str_contains($name, 'Cultural Adventure') => 'cultural_adventure',
            str_contains($name, 'Eco Quest') => 'eco_quest',
            str_contains($name, 'Heritage Quest') => 'heritage_quest',
            default => null,
        };

        return TourRoute::create([
            'name' => ['en' => $name, 'id' => $name],
            'description' => ['en' => 'Test route.', 'id' => 'Rute uji.'],
            'difficulty' => 'easy',
            'gamification_key' => $gamificationKey,
            'estimated_duration_minutes' => 90,
            'distance_meters' => 1200,
            'is_active' => true,
        ]);
    }

    public function test_preview_returns_avatar_options_for_cultural_adventure_and_eco_quest(): void
    {
        $cultural = $this->makeRoute('Penglipuran Cultural Adventure: Mystery of the Living Tradition');
        $eco = $this->makeRoute('Penglipuran Eco Quest: The Secret of the Bamboo Village');
        $heritage = $this->makeRoute('Penglipuran Heritage Quest');

        $this->actingAs($this->user)->getJson("/edutourism/routes/{$cultural->id}/preview")
            ->assertOk()->assertJsonCount(4, 'avatar_options');

        $this->actingAs($this->user)->getJson("/edutourism/routes/{$eco->id}/preview")
            ->assertOk()->assertJsonCount(4, 'avatar_options');

        $this->actingAs($this->user)->getJson("/edutourism/routes/{$heritage->id}/preview")
            ->assertOk()->assertJsonCount(0, 'avatar_options');
    }

    public function test_start_stores_valid_avatar_and_ignores_unknown_one(): void
    {
        $route = $this->makeRoute('Penglipuran Eco Quest: The Secret of the Bamboo Village');

        $this->actingAs($this->user)
            ->postJson("/edutourism/routes/{$route->id}/start", ['avatar' => 'eco_ranger'])
            ->assertOk();

        $this->assertEquals('eco_ranger', RouteSession::where('user_id', $this->user->id)->first()->selected_avatar);

        RouteSession::query()->delete();

        $this->actingAs($this->user)
            ->postJson("/edutourism/routes/{$route->id}/start", ['avatar' => 'not_a_real_avatar'])
            ->assertOk();

        $this->assertNull(RouteSession::where('user_id', $this->user->id)->first()->selected_avatar);
    }

    public function test_mission_completion_awards_sequential_heritage_key(): void
    {
        $route = $this->makeRoute('Penglipuran Cultural Adventure: Mystery of the Living Tradition');

        $pointOne = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::factory()->create()->getMorphClass(),
            'locationable_id' => CulturalObject::factory()->create()->id,
            'order' => 1,
        ]);

        $pointTwo = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::factory()->create()->getMorphClass(),
            'locationable_id' => CulturalObject::factory()->create()->id,
            'order' => 2,
        ]);

        $mission = RouteMission::create([
            'tour_route_point_id' => $pointTwo->id,
            'type' => 'matching',
            'title' => ['en' => 'Scavenger Hunt', 'id' => 'Perburuan'],
            'config' => ['mode' => 'pick', 'items' => []],
            'points' => 100,
            'order' => 1,
        ]);

        $session = RouteSession::create([
            'user_id' => $this->user->id,
            'tour_route_id' => $route->id,
            'current_point_id' => $pointTwo->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$mission->id}/complete", ['earned' => 100])
            ->assertOk();

        // Point 2 is the second point in the route -> heritage_key_2.
        $this->assertContains('heritage_key_2', $session->fresh()->collectibles_earned ?? []);
    }

    public function test_eco_quest_badge_awarded_once_all_five_crystals_collected(): void
    {
        $route = $this->makeRoute('Penglipuran Eco Quest: The Secret of the Bamboo Village');

        $point = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::factory()->create()->getMorphClass(),
            'locationable_id' => CulturalObject::factory()->create()->id,
            'order' => 1,
        ]);

        $mission = RouteMission::create([
            'tour_route_point_id' => $point->id,
            'type' => 'decision',
            'title' => ['en' => 'Eco Rescue', 'id' => 'Eco Rescue'],
            'config' => ['scenarios' => []],
            'points' => 100,
            'order' => 1,
        ]);

        $session = RouteSession::create([
            'user_id' => $this->user->id,
            'tour_route_id' => $route->id,
            'current_point_id' => $point->id,
            'collectibles_earned' => ['eco_crystal_1', 'eco_crystal_2', 'eco_crystal_3', 'eco_crystal_4'],
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$mission->id}/complete", ['earned' => 100])
            ->assertOk()
            ->assertJson(['session_status' => 'completed']);

        // This mission's own completion awards eco_crystal_1 (point order 1), already present —
        // the 5th distinct crystal must come from another point for the badge to unlock.
        $fresh = $session->fresh();
        $this->assertContains('eco_crystal_1', $fresh->collectibles_earned);
        $this->assertNull($fresh->badge_awarded);
    }

    public function test_eco_quest_badge_awarded_with_all_five_distinct_crystals(): void
    {
        $route = $this->makeRoute('Penglipuran Eco Quest: The Secret of the Bamboo Village');

        // 5 points so the completing point is order-index 5 -> eco_crystal_5.
        for ($i = 1; $i <= 4; $i++) {
            TourRoutePoint::create([
                'tour_route_id' => $route->id,
                'locationable_type' => CulturalObject::factory()->create()->getMorphClass(),
                'locationable_id' => CulturalObject::factory()->create()->id,
                'order' => $i,
            ]);
        }

        $lastPoint = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::factory()->create()->getMorphClass(),
            'locationable_id' => CulturalObject::factory()->create()->id,
            'order' => 5,
        ]);

        $mission = RouteMission::create([
            'tour_route_point_id' => $lastPoint->id,
            'type' => 'decision',
            'title' => ['en' => 'Design Challenge', 'id' => 'Design Challenge'],
            'config' => ['scenarios' => []],
            'points' => 100,
            'order' => 1,
        ]);

        $session = RouteSession::create([
            'user_id' => $this->user->id,
            'tour_route_id' => $route->id,
            'current_point_id' => $lastPoint->id,
            'collectibles_earned' => ['eco_crystal_1', 'eco_crystal_2', 'eco_crystal_3', 'eco_crystal_4'],
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$mission->id}/complete", ['earned' => 100])
            ->assertOk()
            ->assertJson(['session_status' => 'completed']);

        $this->assertEquals('Eco Guardian of Penglipuran', $session->fresh()->badge_awarded);
    }

    public function test_gamification_keys_off_column_not_editable_name(): void
    {
        // Route renamed to something with no "Eco Quest" in the name, but the stable
        // gamification_key is still 'eco_quest' — rewards must survive the rename.
        $route = $this->makeRoute('Rute Bebas Namanya Diganti Admin', 'eco_quest');

        $point = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::factory()->create()->getMorphClass(),
            'locationable_id' => CulturalObject::factory()->create()->id,
            'order' => 1,
        ]);

        $mission = RouteMission::create([
            'tour_route_point_id' => $point->id,
            'type' => 'decision',
            'title' => ['en' => 'Eco Rescue', 'id' => 'Eco Rescue'],
            'config' => ['scenarios' => []],
            'points' => 100,
            'order' => 1,
        ]);

        $session = RouteSession::create([
            'user_id' => $this->user->id,
            'tour_route_id' => $route->id,
            'current_point_id' => $point->id,
            'collectibles_earned' => ['eco_crystal_2', 'eco_crystal_3', 'eco_crystal_4', 'eco_crystal_5'],
            'status' => 'active',
        ]);

        // Completing point-1 mission awards eco_crystal_1 -> all 5 present -> badge.
        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$mission->id}/complete", ['earned' => 100])
            ->assertOk();

        $fresh = $session->fresh();
        $this->assertContains('eco_crystal_1', $fresh->collectibles_earned);
        $this->assertEquals('Eco Guardian of Penglipuran', $fresh->badge_awarded);

        // And the avatar picker still resolves off the column, not the name.
        $this->actingAs($this->user)->getJson("/edutourism/routes/{$route->id}/preview")
            ->assertOk()->assertJsonCount(4, 'avatar_options');
    }

    public function test_cultural_adventure_badge_tiers_by_score_percentage(): void
    {
        $route = $this->makeRoute('Penglipuran Cultural Adventure: Mystery of the Living Tradition');

        $point = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::factory()->create()->getMorphClass(),
            'locationable_id' => CulturalObject::factory()->create()->id,
            'order' => 1,
        ]);

        // Max possible score for this fixture: no quizzes on point 1 (0) + one 100-point mission = 100.
        $mission = RouteMission::create([
            'tour_route_point_id' => $point->id,
            'type' => 'riddle',
            'title' => ['en' => 'Final', 'id' => 'Terakhir'],
            'config' => [],
            'points' => 100,
            'order' => 1,
        ]);

        $session = RouteSession::create([
            'user_id' => $this->user->id,
            'tour_route_id' => $route->id,
            'current_point_id' => $point->id,
            'total_score' => 90,
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$mission->id}/complete", ['earned' => 0])
            ->assertOk();

        $this->assertEquals('Heritage Champion', $session->fresh()->badge_awarded);
    }

    public function test_cultural_adventure_lowest_tier_is_cultural_guardian(): void
    {
        $route = $this->makeRoute('Penglipuran Cultural Adventure: Mystery of the Living Tradition');

        $point = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => CulturalObject::factory()->create()->getMorphClass(),
            'locationable_id' => CulturalObject::factory()->create()->id,
            'order' => 1,
        ]);

        $mission = RouteMission::create([
            'tour_route_point_id' => $point->id,
            'type' => 'riddle',
            'title' => ['en' => 'Final', 'id' => 'Terakhir'],
            'config' => [],
            'points' => 100,
            'order' => 1,
        ]);

        $session = RouteSession::create([
            'user_id' => $this->user->id,
            'tour_route_id' => $route->id,
            'current_point_id' => $point->id,
            'total_score' => 10,
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$mission->id}/complete", ['earned' => 0])
            ->assertOk();

        $this->assertEquals('Cultural Guardian', $session->fresh()->badge_awarded);
    }
}
