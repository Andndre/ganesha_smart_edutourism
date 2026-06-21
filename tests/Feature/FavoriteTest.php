<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_favorites_page(): void
    {
        $response = $this->get(route('favorites'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_favorites(): void
    {
        $user = User::factory()->create();
        $object = CulturalObject::factory()->create();
        UserFavorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_type' => CulturalObject::class,
            'favoritable_id' => $object->id,
        ]);

        $response = $this->actingAs($user)->get(route('favorites'));

        $response->assertOk();
    }

    public function test_can_toggle_favorite_on(): void
    {
        $user = User::factory()->create();
        $object = CulturalObject::factory()->create();

        $response = $this->actingAs($user)->post(route('favorites.toggle'), [
            'favoritable_type' => CulturalObject::class,
            'favoritable_id' => $object->id,
        ]);

        $response->assertOk()->assertJson(['status' => 'added']);
        $this->assertDatabaseHas('user_favorites', [
            'user_id' => $user->id,
            'favoritable_type' => CulturalObject::class,
            'favoritable_id' => $object->id,
        ]);
    }

    public function test_can_toggle_favorite_off(): void
    {
        $user = User::factory()->create();
        $object = CulturalObject::factory()->create();

        // Toggle on
        $this->actingAs($user)->post(route('favorites.toggle'), [
            'favoritable_type' => CulturalObject::class,
            'favoritable_id' => $object->id,
        ]);

        // Toggle off
        $response = $this->actingAs($user)->post(route('favorites.toggle'), [
            'favoritable_type' => CulturalObject::class,
            'favoritable_id' => $object->id,
        ]);

        $response->assertOk()->assertJson(['status' => 'removed']);
        $this->assertDatabaseMissing('user_favorites', [
            'user_id' => $user->id,
            'favoritable_type' => CulturalObject::class,
            'favoritable_id' => $object->id,
        ]);
    }

    public function test_cannot_favorite_same_item_twice(): void
    {
        $user = User::factory()->create();
        $object = CulturalObject::factory()->create();

        // Toggle on twice
        $this->actingAs($user)->post(route('favorites.toggle'), [
            'favoritable_type' => CulturalObject::class,
            'favoritable_id' => $object->id,
        ]);
        $this->actingAs($user)->post(route('favorites.toggle'), [
            'favoritable_type' => CulturalObject::class,
            'favoritable_id' => $object->id,
        ]);

        $records = UserFavorite::where('user_id', $user->id)->get();
        $this->assertCount(0, $records);
    }

    public function test_toggle_requires_authentication(): void
    {
        $object = CulturalObject::factory()->create();

        $response = $this->post(route('favorites.toggle'), [
            'favoritable_type' => CulturalObject::class,
            'favoritable_id' => $object->id,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_favorites_page_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('favorites'));

        $response->assertOk();
    }
}
