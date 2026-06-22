<?php

namespace Tests\Feature\Admin;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackReplyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    private Feedback $feedback;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->user = User::factory()->create();

        $this->feedback = Feedback::create([
            'user_id' => $this->user->id,
            'rating' => 5,
            'comment' => 'Great experience!',
            'feedback_type' => 'general',
            'is_public' => true,
        ]);
    }

    public function test_admin_can_reply_to_feedback(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.feedback.reply', $this->feedback->id), [
            'admin_response' => 'Terima kasih atas ulasannya!',
        ]);

        $response->assertRedirect(route('admin.feedback'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('feedbacks', [
            'id' => $this->feedback->id,
            'admin_response' => 'Terima kasih atas ulasannya!',
        ]);
    }

    public function test_reply_requires_authentication(): void
    {
        $response = $this->post(route('admin.feedback.reply', $this->feedback->id), [
            'admin_response' => 'Test reply',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_reply_requires_admin_role(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('admin.feedback.reply', $this->feedback->id), [
            'admin_response' => 'Test reply',
        ]);

        $response->assertStatus(403);
    }

    public function test_reply_validates_admin_response_required(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.feedback.reply', $this->feedback->id), [
            'admin_response' => '',
        ]);

        $response->assertSessionHasErrors('admin_response');
    }

    public function test_admin_can_toggle_feedback_visibility(): void
    {
        $this->actingAs($this->admin);

        $response = $this->patch(route('admin.feedback.toggle', $this->feedback->id));

        $response->assertRedirect(route('admin.feedback'));
        $response->assertSessionHas('success');

        $this->assertFalse($this->feedback->fresh()->is_public);
    }

    public function test_toggle_public_requires_admin(): void
    {
        $this->actingAs($this->user);

        $response = $this->patch(route('admin.feedback.toggle', $this->feedback->id));

        $response->assertStatus(403);
    }

    public function test_admin_can_destroy_feedback(): void
    {
        $this->actingAs($this->admin);

        $response = $this->delete(route('admin.feedback.destroy', $this->feedback->id));

        $response->assertRedirect(route('admin.feedback'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('feedbacks', ['id' => $this->feedback->id]);
    }

    public function test_destroy_requires_admin(): void
    {
        $this->actingAs($this->user);

        $response = $this->delete(route('admin.feedback.destroy', $this->feedback->id));

        $response->assertStatus(403);
    }
}
