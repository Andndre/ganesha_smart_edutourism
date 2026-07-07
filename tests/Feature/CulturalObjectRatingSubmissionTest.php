<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\User;
use App\Models\UserVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CulturalObjectRatingSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_submit_rating(): void
    {
        $object = CulturalObject::factory()->create();

        $this->post(route('cultural-object.rating.store', $object->slug), ['rating' => 5])
            ->assertRedirect(route('login'));
    }

    public function test_user_who_has_not_visited_cannot_submit_rating(): void
    {
        $object = CulturalObject::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('cultural-object.rating.store', $object->slug), ['rating' => 5])
            ->assertForbidden();

        $this->assertDatabaseCount('cultural_object_ratings', 0);
    }

    public function test_user_who_has_visited_can_submit_rating(): void
    {
        $object = CulturalObject::factory()->create();
        $user = User::factory()->create();
        UserVisit::factory()->create(['user_id' => $user->id, 'visitable_type' => CulturalObject::class, 'visitable_id' => $object->id]);

        $this->actingAs($user)
            ->postJson(route('cultural-object.rating.store', $object->slug), ['rating' => 4, 'comment' => 'Bagus'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('cultural_object_ratings', [
            'cultural_object_id' => $object->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'Bagus',
        ]);
    }

    public function test_resubmitting_updates_existing_rating_instead_of_duplicating(): void
    {
        $object = CulturalObject::factory()->create();
        $user = User::factory()->create();
        UserVisit::factory()->create(['user_id' => $user->id, 'visitable_type' => CulturalObject::class, 'visitable_id' => $object->id]);

        $this->actingAs($user)->postJson(route('cultural-object.rating.store', $object->slug), ['rating' => 2]);
        $this->actingAs($user)->postJson(route('cultural-object.rating.store', $object->slug), ['rating' => 5, 'comment' => 'Update']);

        $this->assertDatabaseCount('cultural_object_ratings', 1);
        $this->assertDatabaseHas('cultural_object_ratings', ['rating' => 5, 'comment' => 'Update']);
    }

    public function test_rating_must_be_between_1_and_5(): void
    {
        $object = CulturalObject::factory()->create();
        $user = User::factory()->create();
        UserVisit::factory()->create(['user_id' => $user->id, 'visitable_type' => CulturalObject::class, 'visitable_id' => $object->id]);

        $this->actingAs($user)
            ->postJson(route('cultural-object.rating.store', $object->slug), ['rating' => 6])
            ->assertJsonValidationErrors(['rating']);
    }
}
