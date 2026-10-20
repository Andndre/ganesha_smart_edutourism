<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Hierarki konten: objek anak (tungku) milik objek induk (paon), punya halaman sendiri,
 * dan tidak ikut memenuhi grid /cultural.
 *
 * is_detail sengaja terpisah dari parent_id: Paon adalah komponen yang muncul di setiap
 * rumah, jadi ia butuh is_detail sendiri meski tidak punya induk.
 */
class CulturalObjectHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private function make(string $name, array $extra = []): CulturalObject
    {
        return CulturalObject::create(array_merge([
            'name' => ['id' => $name, 'en' => $name],
            'slug' => Str::slug($name),
            'description' => ['id' => 'Deskripsi', 'en' => 'Description'],
            'short_description' => ['id' => 'Ringkas', 'en' => 'Short'],
            'category' => 'pawongan',
        ], $extra));
    }

    public function test_children_are_not_listed_on_the_cultural_index(): void
    {
        $paon = $this->make('Traditional House Kitchen Paon');
        $this->make('Tungku', ['parent_id' => $paon->id]);

        $response = $this->get('/cultural');
        $response->assertOk();

        $slugs = array_column($response->viewData('objects'), 'slug');
        $this->assertContains($paon->slug, $slugs);
        $this->assertNotContains('tungku', $slugs);
    }

    public function test_parent_page_lists_its_children(): void
    {
        $paon = $this->make('Paon');
        $tungku = $this->make('Tungku', ['parent_id' => $paon->id]);
        $this->make('Objek Lain');

        $response = $this->get('/cultural/paon');
        $response->assertOk();

        $children = $response->viewData('object')['children'];
        $this->assertCount(1, $children);
        $this->assertSame('Tungku', $children[0]['name']);
        $this->assertSame($tungku->slug, $children[0]['slug']);
    }

    public function test_child_page_shows_its_parent_for_the_back_link(): void
    {
        $paon = $this->make('Paon');
        $this->make('Tungku', ['parent_id' => $paon->id]);

        $response = $this->get('/cultural/tungku');
        $response->assertOk();

        $this->assertSame('Paon', $response->viewData('object')['parent']['name']);
    }

    public function test_child_is_not_offered_as_a_parent(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $paon = $this->make('Paon');
        $tungku = $this->make('Tungku', ['parent_id' => $paon->id]);

        $this->actingAs($admin)
            ->post(route('admin.cultural-objects.store'), [
                'name' => ['en' => 'Alat Masak', 'id' => 'Alat Masak'],
                'description' => ['en' => 'D', 'id' => 'D'],
                'category' => 'pawongan',
                'parent_id' => $tungku->id,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_deleting_a_parent_with_children_is_refused(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $paon = $this->make('Paon');
        $this->make('Tungku', ['parent_id' => $paon->id]);

        $this->actingAs($admin)
            ->delete(route('admin.cultural-objects.destroy', $paon->id))
            ->assertSessionHasErrors('destroy');

        $this->assertDatabaseHas('cultural_objects', ['id' => $paon->id]);
    }

    public function test_deleting_a_child_leaves_the_parent_untouched(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $paon = $this->make('Paon');
        $tungku = $this->make('Tungku', ['parent_id' => $paon->id]);

        $this->actingAs($admin)
            ->delete(route('admin.cultural-objects.destroy', $tungku->id))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cultural_objects', ['id' => $paon->id]);
        $this->assertDatabaseMissing('cultural_objects', ['id' => $tungku->id]);
    }
}
