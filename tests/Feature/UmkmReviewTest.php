<?php

namespace Tests\Feature;

use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_submit_a_public_umkm_review(): void
    {
        $profile = UmkmProfile::factory()->create();

        $this->post(route('umkm.review.store', $profile), ['rating' => 5])
            ->assertRedirect(route('login'));
        $this->assertDatabaseCount('umkm_reviews', 0);
    }

    public function test_user_creates_then_updates_their_single_review(): void
    {
        $user = User::factory()->create(['role' => 'tourist']);
        $profile = UmkmProfile::factory()->create();

        $this->actingAs($user)->post(route('umkm.review.store', $profile), ['rating' => 4, 'comment' => 'Bagus sekali.'])->assertRedirect();
        $this->actingAs($user)->post(route('umkm.review.store', $profile), ['rating' => 5, 'comment' => 'Lebih baik lagi.'])->assertRedirect();

        $this->assertDatabaseCount('umkm_reviews', 1);
        $this->assertDatabaseHas('umkm_reviews', ['user_id' => $user->id, 'umkm_profile_id' => $profile->id, 'rating' => 5, 'comment' => 'Lebih baik lagi.']);
    }
}
