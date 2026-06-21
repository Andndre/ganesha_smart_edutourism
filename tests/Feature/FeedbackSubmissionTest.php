<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_feedback_submission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/feedback', [
            'rating' => 5,
            'comment' => 'Bagus sekali!',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'Bagus sekali!',
            'feedback_type' => 'general',
            'reservation_id' => null,
        ]);
    }

    public function test_missing_rating_returns_validation_error(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/feedback', [
            'comment' => 'test',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);
    }

    public function test_invalid_rating_out_of_range(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/feedback', [
            'rating' => 6,
            'comment' => 'test',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);
    }

    public function test_unauthenticated_user_redirected(): void
    {
        $response = $this->post('/feedback', [
            'rating' => 4,
            'comment' => 'Good!',
        ]);

        $response->assertStatus(302);
    }

    public function test_reservation_id_is_null(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson('/feedback', [
            'rating' => 3,
            'comment' => 'Cukup bagus',
        ]);

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $user->id,
            'reservation_id' => null,
        ]);
    }

    public function test_feedback_type_is_general(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson('/feedback', [
            'rating' => 4,
            'comment' => 'Recommended!',
        ]);

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $user->id,
            'feedback_type' => 'general',
        ]);
    }
}
