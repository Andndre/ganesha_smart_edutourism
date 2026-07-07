<?php

namespace Tests\Unit;

use App\Models\CulturalObject;
use App\Models\CulturalObjectRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CulturalObjectRatingHelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_rated_by_and_rating_by_reflect_existing_rating(): void
    {
        $object = CulturalObject::factory()->create();
        $rater = User::factory()->create();
        $other = User::factory()->create();
        CulturalObjectRating::factory()->create([
            'cultural_object_id' => $object->id,
            'user_id' => $rater->id,
            'rating' => 5,
        ]);

        $this->assertTrue($object->isRatedBy($rater));
        $this->assertFalse($object->isRatedBy($other));
        $this->assertSame(5, $object->ratingBy($rater)?->rating);
        $this->assertNull($object->ratingBy($other));
        $this->assertCount(1, $object->ratings);
    }
}
