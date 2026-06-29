<?php

namespace Tests\Unit;

use App\Models\UmkmProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the HasSlug trait via UmkmProductCategory (which uses HasSlug on a translatable 'name').
 */
class HasSlugTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(string $name, string $slug): UmkmProductCategory
    {
        return UmkmProductCategory::create([
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    // -----------------------------------------------------------------------
    // generateSlug
    // -----------------------------------------------------------------------

    public function test_generate_slug_from_explicit_string(): void
    {
        $category = new UmkmProductCategory;

        $slug = $category->generateSlug('Kopi Bali');

        $this->assertEquals('kopi-bali', $slug);
    }

    public function test_generate_slug_handles_special_characters(): void
    {
        $category = new UmkmProductCategory;

        $slug = $category->generateSlug('Kerajinan & Seni');

        $this->assertEquals('kerajinan-seni', $slug);
    }

    public function test_generate_slug_from_model_translatable_field(): void
    {
        $category = UmkmProductCategory::create([
            'name' => ['en' => 'Balinese Coffee', 'id' => 'Kopi Bali'],
            'slug' => 'placeholder',
        ]);

        // generateSlug() reads $model->name via Spatie's accessor (locale-aware)
        app()->setLocale('en');
        $slug = $category->generateSlug();

        $this->assertEquals('balinese-coffee', $slug);
    }

    // -----------------------------------------------------------------------
    // generateUniqueSlug
    // -----------------------------------------------------------------------

    public function test_generate_unique_slug_appends_random_suffix(): void
    {
        $category = new UmkmProductCategory;

        $slug = $category->generateUniqueSlug('Kopi Bali');

        $this->assertStringStartsWith('kopi-bali-', $slug);
        $this->assertGreaterThan(strlen('kopi-bali-'), strlen($slug));
    }

    // -----------------------------------------------------------------------
    // generateCollisionFreeSlug
    // -----------------------------------------------------------------------

    public function test_collision_free_slug_returns_base_slug_when_no_collision(): void
    {
        $category = new UmkmProductCategory;

        $slug = $category->generateCollisionFreeSlug('Kopi Bali');

        $this->assertEquals('kopi-bali', $slug);
    }

    public function test_collision_free_slug_appends_counter_on_collision(): void
    {
        $this->makeCategory('Kopi Bali', 'kopi-bali');

        $category = new UmkmProductCategory;
        $slug = $category->generateCollisionFreeSlug('Kopi Bali');

        $this->assertEquals('kopi-bali-1', $slug);
    }

    public function test_collision_free_slug_increments_counter_for_multiple_collisions(): void
    {
        $this->makeCategory('Kopi Bali', 'kopi-bali');
        $this->makeCategory('Kopi Bali 1', 'kopi-bali-1');
        $this->makeCategory('Kopi Bali 2', 'kopi-bali-2');

        $category = new UmkmProductCategory;
        $slug = $category->generateCollisionFreeSlug('Kopi Bali');

        $this->assertEquals('kopi-bali-3', $slug);
    }

    public function test_collision_free_slug_excludes_given_model_id(): void
    {
        // Simulate an update: the category already owns 'kopi-bali', so updating
        // it should not trigger a counter — its own slug is excluded.
        $existing = $this->makeCategory('Kopi Bali', 'kopi-bali');

        $slug = $existing->generateCollisionFreeSlug('Kopi Bali', $existing->id);

        $this->assertEquals('kopi-bali', $slug);
    }

    public function test_collision_free_slug_excludes_only_the_specified_id(): void
    {
        $cat1 = $this->makeCategory('Kopi Bali', 'kopi-bali');
        $cat2 = $this->makeCategory('Kopi Bali Copy', 'kopi-bali-1');

        // Updating cat2 — cat1's slug ('kopi-bali') still collides, so result is 'kopi-bali-2'
        $slug = $cat2->generateCollisionFreeSlug('Kopi Bali', $cat2->id);

        $this->assertEquals('kopi-bali-1', $slug);
    }
}
