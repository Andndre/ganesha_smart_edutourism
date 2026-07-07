<?php

namespace Tests\Unit;

use App\Models\CulturalObject;
use App\Models\CulturalObjectRating;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CulturalObjectRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_rating_belongs_to_cultural_object_and_user(): void
    {
        $object = CulturalObject::factory()->create();
        $user = User::factory()->create();
        $rating = CulturalObjectRating::factory()->create([
            'cultural_object_id' => $object->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        $this->assertInstanceOf(BelongsTo::class, $rating->culturalObject());
        $this->assertTrue($rating->culturalObject->is($object));
        $this->assertTrue($rating->user->is($user));
        $this->assertSame(4, $rating->rating);
    }

    public function test_duplicate_rating_for_same_user_and_object_is_rejected_by_db(): void
    {
        $object = CulturalObject::factory()->create();
        $user = User::factory()->create();
        CulturalObjectRating::factory()->create(['cultural_object_id' => $object->id, 'user_id' => $user->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        CulturalObjectRating::factory()->create(['cultural_object_id' => $object->id, 'user_id' => $user->id]);
    }
}
