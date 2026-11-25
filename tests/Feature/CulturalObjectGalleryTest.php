<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class CulturalObjectGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_form_and_cards_use_public_storage_urls_for_every_saved_photo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $object = CulturalObject::factory()->create([
            'historical_images' => ['images/first.webp', 'images/second.webp'],
        ]);

        $this->actingAs($admin)->get(route('admin.cultural-objects.edit', $object->id))
            ->assertOk()
            ->assertSee(asset('storage/images/first.webp'))
            ->assertSee(asset('storage/images/second.webp'))
            ->assertDontSee('src="'.asset('images/first.webp').'"', false);
        $this->get(route('admin.cultural-objects'))
            ->assertOk()
            ->assertSee('src="'.asset('storage/images/first.webp').'"', false)
            ->assertSee('2 foto')
            ->assertSee('Ubah objek');
        auth()->logout();
        $this->get(route('cultural-object', $object->slug))
            ->assertOk()
            ->assertSee('storage\/images\/first.webp', false)
            ->assertSee('storage\/images\/second.webp', false);
    }

    public function test_adding_multiple_photos_keeps_the_saved_photos_and_their_order(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/original.webp', 'original');
        $admin = User::factory()->create(['role' => 'admin']);
        $object = CulturalObject::factory()->create(['historical_images' => ['images/original.webp']]);

        $this->actingAs($admin)->put(route('admin.cultural-objects.update', $object->id), [
            'name' => $object->getTranslations('name'),
            'category' => $object->category,
            'historical_images' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.png')],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $photos = $object->fresh()->historical_images;
        $this->assertCount(3, $photos);
        $this->assertSame('images/original.webp', $photos[0]);
        foreach ($photos as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_removing_a_photo_keeps_the_other_photos_and_deletes_only_the_unused_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/first.webp', 'first');
        Storage::disk('public')->put('images/second.webp', 'second');
        $admin = User::factory()->create(['role' => 'admin']);
        $object = CulturalObject::factory()->create(['historical_images' => ['images/first.webp', 'images/second.webp']]);

        $this->actingAs($admin)->put(route('admin.cultural-objects.update', $object->id), [
            'name' => $object->getTranslations('name'),
            'category' => $object->category,
            'removed_historical_images' => ['images/first.webp'],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(['images/second.webp'], $object->fresh()->historical_images);
        Storage::disk('public')->assertMissing('images/first.webp');
        Storage::disk('public')->assertExists('images/second.webp');
    }

    public function test_a_shared_photo_is_kept_when_removed_from_one_object(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/shared.webp', 'shared');
        $admin = User::factory()->create(['role' => 'admin']);
        $object = CulturalObject::factory()->create(['historical_images' => ['images/shared.webp']]);
        CulturalObject::factory()->create(['historical_images' => ['images/shared.webp']]);

        $this->actingAs($admin)->put(route('admin.cultural-objects.update', $object->id), [
            'name' => $object->getTranslations('name'),
            'category' => $object->category,
            'removed_historical_images' => ['images/shared.webp'],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame([], $object->fresh()->historical_images);
        Storage::disk('public')->assertExists('images/shared.webp');
    }

    public function test_cannot_remove_a_photo_that_belongs_to_another_object(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/other.webp', 'other');
        $admin = User::factory()->create(['role' => 'admin']);
        $object = CulturalObject::factory()->create(['historical_images' => []]);
        $other = CulturalObject::factory()->create(['historical_images' => ['images/other.webp']]);

        $this->actingAs($admin)->putJson(route('admin.cultural-objects.update', $object->id), [
            'name' => $object->getTranslations('name'),
            'category' => $object->category,
            'removed_historical_images' => ['images/other.webp'],
        ])->assertUnprocessable()->assertJsonValidationErrors('removed_historical_images.0');

        $this->assertSame([], $object->fresh()->historical_images);
        $this->assertSame(['images/other.webp'], $other->fresh()->historical_images);
        Storage::disk('public')->assertExists('images/other.webp');
    }

    public function test_gallery_limit_counts_saved_photos_and_allows_replacing_one_at_the_limit(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $paths = array_map(fn (int $index): string => "images/photo-{$index}.webp", range(1, 20));
        $object = CulturalObject::factory()->create(['historical_images' => $paths]);
        $data = ['name' => $object->getTranslations('name'), 'category' => $object->category];

        $this->actingAs($admin)->putJson(route('admin.cultural-objects.update', $object->id), [
            ...$data, 'historical_images' => [UploadedFile::fake()->image('extra.jpg')],
        ])->assertUnprocessable()->assertJsonValidationErrors('historical_images');
        $this->assertSame($paths, $object->fresh()->historical_images);
        $this->assertSame([], Storage::disk('public')->allFiles());

        $this->put(route('admin.cultural-objects.update', $object->id), [
            ...$data,
            'historical_images' => [UploadedFile::fake()->image('replacement.jpg')],
            'removed_historical_images' => [$paths[0]],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $photos = $object->fresh()->historical_images;
        $this->assertCount(20, $photos);
        $this->assertSame('images/photo-2.webp', $photos[0]);
        Storage::disk('public')->assertExists($photos[19]);
    }

    #[TestWith(['pUrA'])]
    #[TestWith(['PENATARAN'])]
    #[TestWith(['  pura   agung  '])]
    #[TestWith(['agung pura'])]
    #[TestWith(['MAIN TEMPLE'])]
    #[TestWith(['tem MAIN'])]
    public function test_search_ignores_case_spacing_and_word_order_in_both_languages(string $search): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $match = CulturalObject::factory()->create(['name' => ['id' => 'Pura Penataran Agung', 'en' => 'Main Village Temple']]);
        CulturalObject::factory()->create(['name' => ['id' => 'Hutan Bambu', 'en' => 'Bamboo Forest']]);

        $response = $this->actingAs($admin)->get(route('admin.cultural-objects', ['search' => $search]))->assertOk();

        $this->assertSame([$match->id], $response->viewData('objects')->pluck('id')->all());
    }

    public function test_search_keeps_user_input_as_bound_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        CulturalObject::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.cultural-objects', ['search' => "' OR 1=1 --"]))->assertOk();

        $this->assertCount(0, $response->viewData('objects'));
        $response->assertSee('Objek tidak ditemukan')->assertSee('Tampilkan semua objek');
    }
}
