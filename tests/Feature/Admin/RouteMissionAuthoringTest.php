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

        // Re-save unchanged -> same mission id (stable).
        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", $payload($missionsJson))->assertRedirect();
        $this->assertEquals($firstId, RouteMission::where('tour_route_point_id', $point->id)->firstOrFail()->id);

        // Save with empty missions -> mission removed.
        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", $payload('[]'))->assertRedirect();
        $this->assertEquals(0, RouteMission::where('tour_route_point_id', $point->id)->count());
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
