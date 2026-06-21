<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_history_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('feedback.index'));

        $response->assertOk();
    }

    public function test_unauthenticated_user_redirected(): void
    {
        $response = $this->get(route('feedback.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_shows_empty_state_when_no_feedback(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('feedback.index'));

        $response->assertOk();
        $response->assertSee('Belum Ada Penilaian');
    }

    public function test_shows_user_feedback(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Feedback::create([
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'Test feedback',
            'feedback_type' => 'general',
            'is_public' => true,
        ]);

        $response = $this->get(route('feedback.index'));

        $response->assertOk();
        $response->assertSee('Test feedback');
        $response->assertDontSee('Belum Ada Penilaian');
    }

    public function test_does_not_show_other_users_feedback(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Feedback::create([
            'user_id' => $user2->id,
            'rating' => 4,
            'comment' => 'Feedback dari user lain',
            'feedback_type' => 'general',
            'is_public' => true,
        ]);

        $this->actingAs($user1);
        $response = $this->get(route('feedback.index'));

        $response->assertOk();
        $response->assertDontSee('Feedback dari user lain');
        $response->assertSee('Belum Ada Penilaian');
    }
}
