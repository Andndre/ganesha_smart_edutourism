<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\RouteSession;
use App\Models\User;
use App\Models\UserFavorite;
use App\Models\UserVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_favorite_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $fav = UserFavorite::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $fav->user);
        $this->assertTrue($fav->user->is($user));
    }

    public function test_user_favorite_is_polymorphic(): void
    {
        $fav = UserFavorite::factory()->create();

        $this->assertInstanceOf(CulturalObject::class, $fav->favoritable);
    }

    public function test_user_visit_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $visit = UserVisit::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $visit->user);
        $this->assertTrue($visit->user->is($user));
    }

    public function test_user_visit_is_polymorphic(): void
    {
        $visit = UserVisit::factory()->create();

        $this->assertInstanceOf(CulturalObject::class, $visit->visitable);
    }

    public function test_user_visit_belongs_to_route_session(): void
    {
        $visit = UserVisit::factory()->create();

        $this->assertInstanceOf(RouteSession::class, $visit->routeSession);
    }

    public function test_cultural_object_morphs_to_favorites(): void
    {
        $object = CulturalObject::factory()->create();
        UserFavorite::factory()->create(['favoritable_id' => $object->id]);

        $this->assertCount(1, $object->favorites);
    }

    public function test_cultural_object_isFavoritedBy_returns_correct_bool(): void
    {
        $user = User::factory()->create();
        $object = CulturalObject::factory()->create();

        $this->assertFalse($object->isFavoritedBy($user));

        UserFavorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_id' => $object->id,
        ]);

        $this->assertTrue($object->isFavoritedBy($user));
    }

    public function test_cultural_object_morphs_to_visits(): void
    {
        $object = CulturalObject::factory()->create();
        UserVisit::factory()->create(['visitable_id' => $object->id]);

        $this->assertCount(1, $object->visits);
    }

    public function test_cultural_object_isVisitedBy_returns_correct_bool(): void
    {
        $user = User::factory()->create();
        $object = CulturalObject::factory()->create();

        $this->assertFalse($object->isVisitedBy($user));

        UserVisit::factory()->create([
            'user_id' => $user->id,
            'visitable_id' => $object->id,
        ]);

        $this->assertTrue($object->isVisitedBy($user));
    }
}
