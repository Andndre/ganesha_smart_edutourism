<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\CulturalObjectRating;
use App\Models\User;
use App\Models\UserVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CulturalObjectRatingFormVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_does_not_see_rating_form(): void
    {
        $object = CulturalObject::factory()->create();

        $this->get(route('cultural-object', $object->slug))
            ->assertOk()
            ->assertDontSee('cultural-object-rating-form', false);
    }

    public function test_unvisited_user_does_not_see_rating_form(): void
    {
        $object = CulturalObject::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('cultural-object', $object->slug))
            ->assertOk()
            ->assertDontSee('cultural-object-rating-form', false);
    }

    public function test_visited_user_sees_rating_form(): void
    {
        $object = CulturalObject::factory()->create();
        $user = User::factory()->create();
        UserVisit::factory()->create(['user_id' => $user->id, 'visitable_type' => CulturalObject::class, 'visitable_id' => $object->id]);

        $this->actingAs($user)->get(route('cultural-object', $object->slug))
            ->assertOk()
            ->assertSee('cultural-object-rating-form', false);
    }

    public function test_page_never_leaks_average_rating_or_other_users_comments(): void
    {
        $object = CulturalObject::factory()->create();
        $user = User::factory()->create();
        UserVisit::factory()->create(['user_id' => $user->id, 'visitable_type' => CulturalObject::class, 'visitable_id' => $object->id]);
        CulturalObjectRating::factory()->create(['cultural_object_id' => $object->id, 'comment' => 'RAHASIA_ADMIN_ONLY']);

        $this->actingAs($user)->get(route('cultural-object', $object->slug))
            ->assertDontSee('RAHASIA_ADMIN_ONLY');
    }
}
