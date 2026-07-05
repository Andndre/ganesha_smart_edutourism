<?php

namespace Tests\Feature\Admin;

use App\Models\CulturalObject;
use App\Models\RouteMission;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RouteMissionAuthoringTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function routeWithPoint(): array
    {
        $route = TourRoute::create([
            'name' => ['en' => 'R', 'id' => 'R'],
            'description' => ['en' => 'd', 'id' => 'd'],
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 60,
            'distance_meters' => 500,
            'is_active' => true,
        ]);
        $obj = CulturalObject::factory()->create();
        $point = TourRoutePoint::create([
            'tour_route_id' => $route->id,
            'locationable_type' => $obj->getMorphClass(),
            'locationable_id' => $obj->id,
            'order' => 1,
        ]);

        return [$route, $point, $obj];
    }

    public function test_updating_a_route_keeps_existing_point_ids(): void
    {
        [$route, $point, $obj] = $this->routeWithPoint();

        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", [
            'name' => ['en' => 'R', 'id' => 'R'],
            'description' => ['en' => 'd', 'id' => 'd'],
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 60,
            'distance_meters' => 500,
            'is_active' => '1',
            'points' => [
                ['id' => $point->id, 'locationable_type' => $obj->getMorphClass(), 'locationable_id' => $obj->id],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('tour_route_points', ['id' => $point->id, 'tour_route_id' => $route->id]);
        $this->assertEquals(1, TourRoutePoint::where('tour_route_id', $route->id)->count());
    }

    public function test_missions_are_created_and_kept_stable_across_updates(): void
    {
        [$route, $point, $obj] = $this->routeWithPoint();

        $missionsJson = json_encode([[
            'type' => 'riddle',
            'title' => ['en' => 'Riddle', 'id' => 'Teka-teki'],
            'points' => 50,
            'config' => ['riddle' => ['en' => 'What?', 'id' => 'Apa?'], 'answers' => ['bamboo']],
        ]]);

        $payload = function (string $json) use ($obj, $point) {
            return [
                'name' => ['en' => 'R', 'id' => 'R'],
                'description' => ['en' => 'd', 'id' => 'd'],
                'difficulty' => 'easy',
                'estimated_duration_minutes' => 60,
                'distance_meters' => 500,
                'is_active' => '1',
                'points' => [[
                    'id' => $point->id,
                    'locationable_type' => $obj->getMorphClass(),
                    'locationable_id' => $obj->id,
                    'missions' => $json,
                ]],
            ];
        };

        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", $payload($missionsJson))->assertRedirect();

        $mission = RouteMission::where('tour_route_point_id', $point->id)->firstOrFail();
        $this->assertEquals('riddle', $mission->type);
        $this->assertEquals(50, $mission->points);
        $this->assertEquals(['bamboo'], $mission->config['answers']);
        $firstId = $mission->id;

        // Re-save unchanged -> same mission id (stable). Missions are now keyed by
        // `id`, not position, so the resend must echo the real id back -- exactly
        // what the frontend's collectMissions() does via row.dataset.missionId.
        $missionsJsonWithId = json_encode([[
            'id' => $firstId,
            'type' => 'riddle',
            'title' => ['en' => 'Riddle', 'id' => 'Teka-teki'],
            'points' => 50,
            'config' => ['riddle' => ['en' => 'What?', 'id' => 'Apa?'], 'answers' => ['bamboo']],
        ]]);
        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", $payload($missionsJsonWithId))->assertRedirect();
        $this->assertEquals($firstId, RouteMission::where('tour_route_point_id', $point->id)->firstOrFail()->id);
        $this->assertEquals(1, RouteMission::where('tour_route_point_id', $point->id)->count());

        // Save with empty missions -> mission removed.
        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", $payload('[]'))->assertRedirect();
        $this->assertEquals(0, RouteMission::where('tour_route_point_id', $point->id)->count());
    }

    public function test_removing_a_middle_mission_does_not_reassign_surviving_mission_ids(): void
    {
        [$route, $point, $obj] = $this->routeWithPoint();

        $missionsJson = json_encode([
            [
                'type' => 'riddle',
                'title' => ['en' => 'Riddle A', 'id' => 'Teka-teki A'],
                'points' => 10,
                'config' => ['riddle' => ['en' => 'A?', 'id' => 'A?'], 'answers' => ['a']],
            ],
            [
                'type' => 'matching',
                'title' => ['en' => 'Matching B', 'id' => 'Mencocokkan B'],
                'points' => 20,
                'config' => ['pairs' => [['left' => 'b1', 'right' => 'b2']]],
            ],
            [
                'type' => 'sequence',
                'title' => ['en' => 'Sequence C', 'id' => 'Urutan C'],
                'points' => 30,
                'config' => ['items' => ['c1', 'c2', 'c3']],
            ],
        ]);

        $payload = fn (string $json) => [
            'name' => ['en' => 'R', 'id' => 'R'],
            'description' => ['en' => 'd', 'id' => 'd'],
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 60,
            'distance_meters' => 500,
            'is_active' => '1',
            'points' => [[
                'id' => $point->id,
                'locationable_type' => $obj->getMorphClass(),
                'locationable_id' => $obj->id,
                'missions' => $json,
            ]],
        ];

        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", $payload($missionsJson))->assertRedirect();

        $missions = RouteMission::where('tour_route_point_id', $point->id)->orderBy('order')->get();
        $this->assertCount(3, $missions);
        [$missionA, $missionB, $missionC] = $missions;

        // Remove the MIDDLE mission (B), sending only A and C back with their real ids.
        $secondSaveJson = json_encode([
            [
                'id' => $missionA->id,
                'type' => 'riddle',
                'title' => ['en' => 'Riddle A', 'id' => 'Teka-teki A'],
                'points' => 10,
                'config' => ['riddle' => ['en' => 'A?', 'id' => 'A?'], 'answers' => ['a']],
            ],
            [
                'id' => $missionC->id,
                'type' => 'sequence',
                'title' => ['en' => 'Sequence C', 'id' => 'Urutan C'],
                'points' => 30,
                'config' => ['items' => ['c1', 'c2', 'c3']],
            ],
        ]);

        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", $payload($secondSaveJson))->assertRedirect();

        // Mission B's row is actually gone.
        $this->assertDatabaseMissing('route_missions', ['id' => $missionB->id]);
        $this->assertEquals(2, RouteMission::where('tour_route_point_id', $point->id)->count());

        // Survivors kept their exact same DB ids and original content -- not
        // reassigned to each other's content because their positions shifted.
        $reloadedA = RouteMission::find($missionA->id);
        $reloadedC = RouteMission::find($missionC->id);

        $this->assertNotNull($reloadedA);
        $this->assertNotNull($reloadedC);
        $this->assertEquals('riddle', $reloadedA->type);
        $this->assertEquals(['a'], $reloadedA->config['answers']);
        $this->assertEquals('sequence', $reloadedC->type);
        $this->assertEquals(['c1', 'c2', 'c3'], $reloadedC->config['items']);

        // Order values renumbered to 1, 2 despite unchanged ids.
        $this->assertEquals(1, $reloadedA->order);
        $this->assertEquals(2, $reloadedC->order);
    }

    public function test_existing_mission_keeps_id_while_new_sibling_mission_gets_fresh_id(): void
    {
        [$route, $point, $obj] = $this->routeWithPoint();

        $existing = RouteMission::create([
            'tour_route_point_id' => $point->id,
            'type' => 'riddle',
            'title' => ['en' => 'Existing', 'id' => 'Yang Ada'],
            'points' => 10,
            'config' => ['riddle' => ['en' => 'Q?', 'id' => 'Q?'], 'answers' => ['x']],
            'order' => 1,
        ]);

        $missionsJson = json_encode([
            [
                'id' => $existing->id,
                'type' => 'riddle',
                'title' => ['en' => 'Existing', 'id' => 'Yang Ada'],
                'points' => 10,
                'config' => ['riddle' => ['en' => 'Q?', 'id' => 'Q?'], 'answers' => ['x']],
            ],
            [
                'type' => 'sequence',
                'title' => ['en' => 'New', 'id' => 'Baru'],
                'points' => 20,
                'config' => ['items' => ['a', 'b']],
            ],
        ]);

        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", [
            'name' => ['en' => 'R', 'id' => 'R'],
            'description' => ['en' => 'd', 'id' => 'd'],
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 60,
            'distance_meters' => 500,
            'is_active' => '1',
            'points' => [[
                'id' => $point->id,
                'locationable_type' => $obj->getMorphClass(),
                'locationable_id' => $obj->id,
                'missions' => $missionsJson,
            ]],
        ])->assertRedirect();

        $missions = RouteMission::where('tour_route_point_id', $point->id)->orderBy('order')->get();
        $this->assertCount(2, $missions);
        $this->assertTrue($missions->contains('id', $existing->id));
        $newMission = $missions->firstWhere('id', '!=', $existing->id);
        $this->assertNotNull($newMission);
        $this->assertEquals('sequence', $newMission->type);
    }

    public function test_mission_asset_upload_returns_public_url(): void
    {
        Storage::fake('public');

        $file = File::image('card.jpg', 200, 200);

        $res = $this->actingAs($this->admin())
            ->post('/admin/route-missions/upload-asset', ['file' => $file])
            ->assertOk()
            ->json();

        $this->assertStringStartsWith('/storage/mission_assets/', $res['url']);
        Storage::disk('public')
            ->assertExists(str_replace('/storage/', '', $res['url']));
    }
}
