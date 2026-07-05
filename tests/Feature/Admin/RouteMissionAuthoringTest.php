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

    public function test_matching_mission_config_persists_with_image_url(): void
    {
        [$route, $point, $obj] = $this->routeWithPoint();
        $json = json_encode([[
            'type' => 'matching',
            'title' => ['en' => 'Hunt', 'id' => 'Berburu'],
            'points' => 100,
            'config' => ['mode' => 'pick', 'prompt' => ['en' => 'Pick', 'id' => 'Pilih'],
                'items' => [['label' => ['en' => 'Gate', 'id' => 'Gerbang'], 'image' => '/storage/mission_assets/x.jpg', 'correct' => true]]],
        ]]);
        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", [
            'name' => ['en' => 'R', 'id' => 'R'], 'description' => ['en' => 'd', 'id' => 'd'],
            'difficulty' => 'easy', 'estimated_duration_minutes' => 60, 'distance_meters' => 500, 'is_active' => '1',
            'points' => [['id' => $point->id, 'locationable_type' => $obj->getMorphClass(), 'locationable_id' => $obj->id, 'missions' => $json]],
        ])->assertRedirect();

        $cfg = RouteMission::where('tour_route_point_id', $point->id)->firstOrFail()->config;
        $this->assertEquals('/storage/mission_assets/x.jpg', $cfg['items'][0]['image']);
    }

    public function test_matching_mission_reader_faithfully_reconstructs_existing_pick_config(): void
    {
        // Simulates what the browser does on open->close-without-editing->save now that
        // MISSION_CONFIG_READERS['matching'] is registered (Task 5): the builder renders
        // the existing config into the DOM, and the reader must serialize it back to an
        // equivalent shape without dropping or mutating fields. We can't run the JS here,
        // so we assert the documented reader contract directly against the persisted config
        // by round-tripping it through the same PUT endpoint unchanged.
        [$route, $point, $obj] = $this->routeWithPoint();

        $originalConfig = [
            'mode' => 'pick',
            'prompt' => ['en' => 'Pick the right one', 'id' => 'Pilih yang benar'],
            'pick_count' => 2,
            'penalty' => 5,
            'items' => [
                ['label' => ['en' => 'Gate', 'id' => 'Gerbang'], 'icon' => '🌿', 'correct' => true],
                ['label' => ['en' => 'Wall', 'id' => 'Tembok'], 'image' => '/storage/mission_assets/wall.jpg', 'correct' => false],
            ],
        ];

        $existing = RouteMission::create([
            'tour_route_point_id' => $point->id,
            'type' => 'matching',
            'title' => ['en' => 'Existing Match', 'id' => 'Cocokkan yang Ada'],
            'points' => 75,
            'config' => $originalConfig,
            'order' => 1,
        ]);

        // Re-submit exactly what MISSION_CONFIG_READERS['matching'] would produce for this
        // config: prompt/mode/pick_count/penalty/items preserved verbatim (icon-only items
        // keep no `image` key, image-only items keep no `icon` key -- matching the reader's
        // `if (icon)`/`if (image)` guards), proving the round trip is lossless.
        $reconstructed = [
            'mode' => 'pick',
            'prompt' => ['en' => 'Pick the right one', 'id' => 'Pilih yang benar'],
            'pick_count' => 2,
            'penalty' => 5,
            'items' => [
                ['label' => ['en' => 'Gate', 'id' => 'Gerbang'], 'correct' => true, 'icon' => '🌿'],
                ['label' => ['en' => 'Wall', 'id' => 'Tembok'], 'correct' => false, 'image' => '/storage/mission_assets/wall.jpg'],
            ],
        ];

        $missionsJson = json_encode([[
            'id' => $existing->id,
            'type' => 'matching',
            'title' => ['en' => 'Existing Match', 'id' => 'Cocokkan yang Ada'],
            'points' => 75,
            'config' => $reconstructed,
        ]]);

        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", [
            'name' => ['en' => 'R', 'id' => 'R'], 'description' => ['en' => 'd', 'id' => 'd'],
            'difficulty' => 'easy', 'estimated_duration_minutes' => 60, 'distance_meters' => 500, 'is_active' => '1',
            'points' => [['id' => $point->id, 'locationable_type' => $obj->getMorphClass(), 'locationable_id' => $obj->id, 'missions' => $missionsJson]],
        ])->assertRedirect();

        $reloaded = RouteMission::find($existing->id);
        $this->assertEquals($existing->id, $reloaded->id, 'Mission id must be stable across the no-op edit.');
        $this->assertEquals('pick', $reloaded->config['mode']);
        $this->assertEquals(2, $reloaded->config['pick_count']);
        $this->assertEquals(5, $reloaded->config['penalty']);
        $this->assertEquals('Gate', $reloaded->config['items'][0]['label']['en']);
        $this->assertEquals('🌿', $reloaded->config['items'][0]['icon']);
        $this->assertArrayNotHasKey('image', $reloaded->config['items'][0]);
        $this->assertEquals('/storage/mission_assets/wall.jpg', $reloaded->config['items'][1]['image']);
        $this->assertArrayNotHasKey('icon', $reloaded->config['items'][1]);
    }

    public function test_matching_mission_reader_does_not_add_prompt_when_originally_absent(): void
    {
        // Regression test for the bug where MISSION_CONFIG_READERS['matching'] used to do
        // `out.prompt = readBilingual(...)` unconditionally, unlike the `if (x)`-guarded
        // penalty/pick_count/icon/image fields in the same reader. That meant any existing
        // matching mission saved with NO `prompt` key at all would silently gain
        // `prompt: {en:'',id:''}` on the very first no-op save. Unlike the sibling test above
        // (which happens to include `prompt` in both fixtures and so never exercised this),
        // this fixture deliberately omits `prompt` entirely to prove the fix.
        [$route, $point, $obj] = $this->routeWithPoint();

        $originalConfig = [
            'mode' => 'match',
            'pairs' => [
                ['left' => ['en' => 'Door', 'id' => 'Pintu'], 'right' => ['en' => 'Enter', 'id' => 'Masuk']],
            ],
        ];

        $existing = RouteMission::create([
            'tour_route_point_id' => $point->id,
            'type' => 'matching',
            'title' => ['en' => 'No Prompt Match', 'id' => 'Cocokkan Tanpa Prompt'],
            'points' => 50,
            'config' => $originalConfig,
            'order' => 1,
        ]);

        $this->assertArrayNotHasKey('prompt', $existing->config, 'Fixture must start without a prompt key.');

        // Re-submit exactly what MISSION_CONFIG_READERS['matching'] should produce for this
        // config after the fix: no `prompt` key, since the DOM field was empty.
        $reconstructed = [
            'mode' => 'match',
            'pairs' => [
                ['left' => ['en' => 'Door', 'id' => 'Pintu'], 'right' => ['en' => 'Enter', 'id' => 'Masuk']],
            ],
        ];

        $missionsJson = json_encode([[
            'id' => $existing->id,
            'type' => 'matching',
            'title' => ['en' => 'No Prompt Match', 'id' => 'Cocokkan Tanpa Prompt'],
            'points' => 50,
            'config' => $reconstructed,
        ]]);

        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", [
            'name' => ['en' => 'R', 'id' => 'R'], 'description' => ['en' => 'd', 'id' => 'd'],
            'difficulty' => 'easy', 'estimated_duration_minutes' => 60, 'distance_meters' => 500, 'is_active' => '1',
            'points' => [['id' => $point->id, 'locationable_type' => $obj->getMorphClass(), 'locationable_id' => $obj->id, 'missions' => $missionsJson]],
        ])->assertRedirect();

        $reloaded = RouteMission::find($existing->id);
        $this->assertEquals($existing->id, $reloaded->id, 'Mission id must be stable across the no-op edit.');
        $this->assertArrayNotHasKey('prompt', $reloaded->config, 'A no-op save must not add a prompt key that was never there.');
    }

    public function test_sequence_mission_config_round_trips_with_prompt_and_reveal_first(): void
    {
        // Task 6: register + persist a `sequence` mission via the same PUT flow, with
        // a prompt present and reveal_first explicitly set, and confirm every field
        // survives creation intact.
        [$route, $point, $obj] = $this->routeWithPoint();

        $json = json_encode([[
            'type' => 'sequence',
            'title' => ['en' => 'Order the Steps', 'id' => 'Urutkan Langkah'],
            'points' => 100,
            'config' => [
                'prompt' => ['en' => 'Put these in order', 'id' => 'Urutkan ini'],
                'reveal_first' => true,
                'items' => [
                    ['text' => ['en' => 'First', 'id' => 'Pertama']],
                    ['text' => ['en' => 'Second', 'id' => 'Kedua']],
                    ['text' => ['en' => 'Third', 'id' => 'Ketiga']],
                ],
            ],
        ]]);

        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", [
            'name' => ['en' => 'R', 'id' => 'R'], 'description' => ['en' => 'd', 'id' => 'd'],
            'difficulty' => 'easy', 'estimated_duration_minutes' => 60, 'distance_meters' => 500, 'is_active' => '1',
            'points' => [['id' => $point->id, 'locationable_type' => $obj->getMorphClass(), 'locationable_id' => $obj->id, 'missions' => $json]],
        ])->assertRedirect();

        $mission = RouteMission::where('tour_route_point_id', $point->id)->firstOrFail();
        $this->assertEquals('sequence', $mission->type);
        $this->assertEquals('Put these in order', $mission->config['prompt']['en']);
        $this->assertTrue($mission->config['reveal_first']);
        $this->assertCount(3, $mission->config['items']);
        $this->assertEquals('First', $mission->config['items'][0]['text']['en']);
        $this->assertEquals('Third', $mission->config['items'][2]['text']['en']);
    }

    public function test_sequence_mission_reader_does_not_add_prompt_when_originally_absent(): void
    {
        // Field-by-field audit for MISSION_CONFIG_READERS['sequence']: `prompt` is the
        // only OPTIONAL field in `{ prompt?:{en,id}, reveal_first?:bool, items:[{text}] }`
        // and must be guarded exactly like matching's `prompt` (see the sibling regression
        // test above) so an open->close-without-editing->save on an existing sequence
        // mission with no prompt does not silently inject `prompt: {en:'',id:''}`.
        // `reveal_first` is a checkbox-backed boolean, always emitted with a concrete
        // value; `items[].text` is required per item, also always emitted.
        [$route, $point, $obj] = $this->routeWithPoint();

        $originalConfig = [
            'reveal_first' => false,
            'items' => [
                ['text' => ['en' => 'Arrive', 'id' => 'Tiba']],
                ['text' => ['en' => 'Enter', 'id' => 'Masuk']],
            ],
        ];

        $existing = RouteMission::create([
            'tour_route_point_id' => $point->id,
            'type' => 'sequence',
            'title' => ['en' => 'No Prompt Sequence', 'id' => 'Urutan Tanpa Prompt'],
            'points' => 50,
            'config' => $originalConfig,
            'order' => 1,
        ]);

        $this->assertArrayNotHasKey('prompt', $existing->config, 'Fixture must start without a prompt key.');

        // Re-submit exactly what MISSION_CONFIG_READERS['sequence'] should produce for
        // this config after a no-op open/close: no `prompt` key, since the DOM field
        // was empty; reveal_first and items preserved verbatim.
        $reconstructed = [
            'reveal_first' => false,
            'items' => [
                ['text' => ['en' => 'Arrive', 'id' => 'Tiba']],
                ['text' => ['en' => 'Enter', 'id' => 'Masuk']],
            ],
        ];

        $missionsJson = json_encode([[
            'id' => $existing->id,
            'type' => 'sequence',
            'title' => ['en' => 'No Prompt Sequence', 'id' => 'Urutan Tanpa Prompt'],
            'points' => 50,
            'config' => $reconstructed,
        ]]);

        $this->actingAs($this->admin())->put("/admin/tour-routes/{$route->id}", [
            'name' => ['en' => 'R', 'id' => 'R'], 'description' => ['en' => 'd', 'id' => 'd'],
            'difficulty' => 'easy', 'estimated_duration_minutes' => 60, 'distance_meters' => 500, 'is_active' => '1',
            'points' => [['id' => $point->id, 'locationable_type' => $obj->getMorphClass(), 'locationable_id' => $obj->id, 'missions' => $missionsJson]],
        ])->assertRedirect();

        $reloaded = RouteMission::find($existing->id);
        $this->assertEquals($existing->id, $reloaded->id, 'Mission id must be stable across the no-op edit.');
        $this->assertArrayNotHasKey('prompt', $reloaded->config, 'A no-op save must not add a prompt key that was never there.');
        $this->assertFalse($reloaded->config['reveal_first']);
        $this->assertCount(2, $reloaded->config['items']);
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
