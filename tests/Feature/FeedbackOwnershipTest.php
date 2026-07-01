<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies the ownership check now shared via FeedbackController::authorizeOwner()
 * (previously duplicated inline across show/edit/update/thankYou).
 */
class FeedbackOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_feedback(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $feedback = Feedback::factory()->create(['user_id' => $owner->id, 'reservation_id' => null, 'feedback_type' => 'general']);

        $this->actingAs($intruder)->get(route('feedback.show', $feedback))->assertForbidden();
        $this->actingAs($intruder)->get(route('feedback.edit', $feedback))->assertForbidden();
        $this->actingAs($intruder)->put(route('feedback.update', $feedback), ['rating' => 5])->assertForbidden();
        $this->actingAs($intruder)->get(route('feedback.thank-you', $feedback))->assertForbidden();
    }

    public function test_owner_can_view_their_own_feedback(): void
    {
        $owner = User::factory()->create();
        $feedback = Feedback::factory()->create(['user_id' => $owner->id, 'reservation_id' => null, 'feedback_type' => 'general']);

        $this->actingAs($owner)->get(route('feedback.show', $feedback))->assertOk();
    }
}
