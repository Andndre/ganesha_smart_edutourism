<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\TourRouteRequest;
use App\Models\CulturalObject;
use App\Models\RouteMission;
use App\Models\RouteSession;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RouteMissionPuzzleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private TourRoute $route;

    private TourRoutePoint $point;

    private CulturalObject $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->route = TourRoute::create([
            'name' => ['en' => 'Puzzle Route', 'id' => 'Rute Puzzle'],
            'description' => ['en' => 'Test route.', 'id' => 'Rute uji.'],
            'difficulty' => 'easy',
            'estimated_duration_minutes' => 60,
            'distance_meters' => 800,
            'is_active' => true,
        ]);

        $this->object = CulturalObject::factory()->create([
            'name' => ['en' => 'Angkul-Angkul Gate', 'id' => 'Angkul-Angkul'],
            'historical_images' => ['cultural_objects/angkul.jpg'],
        ]);

        $this->point = TourRoutePoint::create([
            'tour_route_id' => $this->route->id,
            'locationable_type' => $this->object->getMorphClass(),
            'locationable_id' => $this->object->id,
            'order' => 1,
            'qr_code_token' => 'EDU-PUZZLE-P1',
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function puzzleMission(array $config = [], ?int $timeLimit = null): RouteMission
    {
        return RouteMission::create([
            'tour_route_point_id' => $this->point->id,
            'type' => 'puzzle',
            'title' => ['en' => 'Photo Puzzle', 'id' => 'Puzzle Foto'],
            'config' => $config,
            'points' => 100,
            'time_limit_seconds' => $timeLimit,
            'order' => 1,
        ]);
    }

    protected function startSession(): RouteSession
    {
        return RouteSession::create([
            'user_id' => $this->user->id,
            'tour_route_id' => $this->route->id,
            'current_point_id' => $this->point->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_validation_accepts_the_puzzle_mission_type(): void
    {
        $rules = (new TourRouteRequest)->rules();

        $this->assertStringContainsString('puzzle', $rules['points.*.missions.*.type'][2]);

        $validator = Validator::make(
            ['points' => [['missions' => [['type' => 'puzzle']]]]],
            ['points.*.missions.*.type' => $rules['points.*.missions.*.type']]
        );

        $this->assertFalse($validator->fails());
    }

    public function test_admin_validation_still_rejects_an_unknown_mission_type(): void
    {
        $rules = (new TourRouteRequest)->rules();

        $validator = Validator::make(
            ['points' => [['missions' => [['type' => 'sudoku']]]]],
            ['points.*.missions.*.type' => $rules['points.*.missions.*.type']]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_puzzle_mission_renders_with_the_admin_configured_photo_and_grid(): void
    {
        $this->puzzleMission([
            'image' => 'http://localhost/storage/mission_assets/custom.jpg',
            'grid_size' => 5,
            'prompt' => ['en' => 'Rebuild the gate.', 'id' => 'Susun kembali gerbangnya.'],
        ], 90);

        $this->startSession();

        $response = $this->actingAs($this->user)->get(route('edutourism.active', ['locale' => 'id']));

        $response->assertOk();
        $response->assertSee('eduGamePuzzle', false);
        // Js::from escapes the slashes; the photo now reaches the board only through x-data.
        $response->assertSee('mission_assets\\/custom.jpg', false);
        $response->assertSee('Susun kembali gerbangnya.', false);
        // grid_size and the mission-level time limit (a column, not config) both reach the component.
        // Js::from hex-escapes the quotes so the JSON survives inside the x-data attribute.
        $response->assertSee('\\u0022grid_size\\u0022:5', false);
        $response->assertSee(', 100, 90, ', false);
    }

    public function test_puzzle_mission_falls_back_to_the_points_cultural_object_photo(): void
    {
        $this->puzzleMission(['grid_size' => 3]);
        $this->startSession();

        $response = $this->actingAs($this->user)->get(route('edutourism.active'));

        $response->assertOk();
        $response->assertSee('cultural_objects\\/angkul.jpg', false);
        $response->assertDontSee('Misi puzzle ini belum punya foto');
    }

    public function test_puzzle_mission_warns_when_no_photo_can_be_resolved(): void
    {
        $this->object->update(['historical_images' => []]);
        $this->puzzleMission();
        $this->startSession();

        $response = $this->actingAs($this->user)->get(route('edutourism.active', ['locale' => 'id']));

        $response->assertOk();
        $response->assertSee('Misi puzzle ini belum punya foto. Hubungi pengelola.');
        $response->assertDontSee('x-data="eduGamePuzzle(', false);
    }

    public function test_only_a_puzzle_point_widens_the_mission_column(): void
    {
        // A puzzle point: the board sits beside its reference, so the column has to be wide.
        $this->puzzleMission(['grid_size' => 4]);
        $this->startSession();

        $wide = $this->actingAs($this->user)->get(route('edutourism.active'));
        // Matched with the closing quote: other stages on this page carry their own
        // "mx-auto max-w-md ..." classes, so a loose substring would collide with them.
        $wide->assertSee('class="mx-auto max-w-4xl"', false);
        $wide->assertDontSee('class="mx-auto max-w-md"', false);

        // The same point without a puzzle keeps the narrow default.
        RouteMission::where('tour_route_point_id', $this->point->id)->delete();
        RouteMission::create([
            'tour_route_point_id' => $this->point->id,
            'type' => 'riddle',
            'title' => ['en' => 'Riddle', 'id' => 'Teka-teki'],
            'config' => ['riddle' => ['en' => 'Who?', 'id' => 'Siapa?'], 'answers' => ['merajan']],
            'points' => 100,
            'order' => 1,
        ]);

        $narrow = $this->actingAs($this->user)->get(route('edutourism.active'));
        $narrow->assertSee('class="mx-auto max-w-md"', false);
        $narrow->assertDontSee('class="mx-auto max-w-4xl"', false);
    }

    public function test_completing_a_puzzle_mission_scores_through_the_shared_endpoint(): void
    {
        $mission = $this->puzzleMission(['grid_size' => 3]);
        $session = $this->startSession();

        $this->actingAs($this->user)
            ->postJson("/edutourism/mission/{$mission->id}/complete", ['earned' => 70])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals(70, $session->fresh()->total_score);
    }
}
