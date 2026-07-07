<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmComplaintTest extends TestCase
{
    use RefreshDatabase;

    private User $tourist;

    private User $owner;

    private User $admin;

    private UmkmProfile $umkm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tourist = User::factory()->create(['role' => 'tourist']);

        $this->owner = User::factory()->create(['role' => 'umkm_owner']);
        $this->umkm = UmkmProfile::factory()->create([
            'user_id' => $this->owner->id,
            'owner_name' => $this->owner->name,
            'business_name' => ['en' => 'Owner Store', 'id' => 'Toko Pemilik'],
            'rating' => 5.0,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test tourist can submit complaint for a specific UMKM.
     */
    public function test_tourist_can_submit_complaint_for_umkm(): void
    {
        $response = $this->actingAs($this->tourist)
            ->post(route('feedback.store'), [
                'rating' => 3,
                'comment' => 'Slow service and cold food.',
                'umkm_profile_id' => $this->umkm->id,
                'feedback_type' => 'umkm',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $this->tourist->id,
            'umkm_profile_id' => $this->umkm->id,
            'feedback_type' => 'umkm',
            'rating' => 3,
            'comment' => 'Slow service and cold food.',
        ]);

        // Rating on profile should be updated to average
        $this->assertEquals(3.0, $this->umkm->fresh()->rating);
    }

    /**
     * Test UMKM Owner can access their complaints list.
     */
    public function test_owner_can_access_their_complaints(): void
    {
        Feedback::create([
            'user_id' => $this->tourist->id,
            'umkm_profile_id' => $this->umkm->id,
            'feedback_type' => 'umkm',
            'rating' => 2,
            'comment' => 'Terrible experience.',
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('owner.complaints'));

        $response->assertOk();
        $response->assertSee('Terrible experience.');
    }

    /**
     * Test Admin can access owner dashboard with specific UMKM profile context.
     */
    public function test_admin_can_preview_owner_dashboard(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('owner.dashboard', ['umkm_profile_id' => $this->umkm->id]));

        $response->assertOk();
        $this->assertEquals($this->umkm->id, session('admin_view_umkm_profile_id'));
        $response->assertSee('Admin Mode:');
    }
}
