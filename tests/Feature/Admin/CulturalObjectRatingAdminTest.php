<?php

namespace Tests\Feature\Admin;

use App\Models\CulturalObject;
use App\Models\CulturalObjectRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CulturalObjectRatingAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_regular_user_cannot_access_admin_ratings_page(): void
    {
        $this->get(route('admin.cultural-object-ratings'))->assertRedirect(route('login'));

        $user = User::factory()->create(['role' => 'tourist']);
        $this->actingAs($user)->get(route('admin.cultural-object-ratings'))->assertForbidden();
    }

    public function test_admin_sees_average_and_count_per_object(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'preferred_language' => 'en']);
        $object = CulturalObject::factory()->create(['name' => ['en' => 'Old House', 'id' => 'Rumah Lama']]);
        CulturalObjectRating::factory()->create(['cultural_object_id' => $object->id, 'rating' => 4]);
        CulturalObjectRating::factory()->create(['cultural_object_id' => $object->id, 'rating' => 2]);

        $response = $this->actingAs($admin)->get(route('admin.cultural-object-ratings'));

        $response->assertOk();
        $response->assertSee('Old House');
        $response->assertSee('3.0');
        $response->assertSee('(2)');
    }

    public function test_admin_can_delete_a_rating(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $object = CulturalObject::factory()->create();
        $rating = CulturalObjectRating::factory()->create([
            'cultural_object_id' => $object->id,
            'rating' => 1,
            'comment' => 'Spam / inappropriate',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.cultural-object-ratings.destroy', $rating));

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('cultural_object_ratings', ['id' => $rating->id]);
    }

    public function test_guest_and_regular_user_cannot_delete_a_rating(): void
    {
        $object = CulturalObject::factory()->create();
        $rating = CulturalObjectRating::factory()->create(['cultural_object_id' => $object->id]);

        $this->delete(route('admin.cultural-object-ratings.destroy', $rating))->assertRedirect(route('login'));

        $user = User::factory()->create(['role' => 'tourist']);
        $this->actingAs($user)->delete(route('admin.cultural-object-ratings.destroy', $rating))->assertForbidden();

        $this->assertDatabaseHas('cultural_object_ratings', ['id' => $rating->id]);
    }
}
