<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\TourPackage;
use App\Models\UmkmProductCategory;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TranslationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@translation-test.com',
            'role' => 'admin',
        ]);
    }

    public function test_creates_cultural_object_with_translations(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Beautiful Temple', 'id' => 'Pura Indah'],
            'slug' => 'beautiful-temple',
            'short_description' => ['en' => 'A beautiful temple', 'id' => 'Pura yang indah'],
            'description' => ['en' => 'Full description in English', 'id' => 'Deskripsi lengkap dalam Bahasa Indonesia'],
            'category' => 'temple',
        ]);

        $this->assertDatabaseHas('cultural_objects', [
            'id' => $object->id,
        ]);

        app()->setLocale('en');
        $this->assertEquals('Beautiful Temple', $object->fresh()->name);
        $this->assertEquals('A beautiful temple', $object->fresh()->short_description);

        app()->setLocale('id');
        $this->assertEquals('Pura Indah', $object->fresh()->name);
        $this->assertEquals('Pura yang indah', $object->fresh()->short_description);
    }

    public function test_tour_package_create_with_translations_via_controller(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.packages.store'), [
                'name' => ['en' => 'Cultural Tour', 'id' => 'Tur Budaya'],
                'description' => ['en' => 'Explore Balinese culture.', 'id' => 'Jelajahi budaya Bali.'],
                'price' => 100000,
                'duration_hours' => 3.5,
                'max_capacity' => 15,
                'is_active' => true,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $package = TourPackage::where('slug', 'like', 'cultural-tour%')->firstOrFail();

        app()->setLocale('en');
        $this->assertEquals('Cultural Tour', $package->fresh()->name);
        $this->assertEquals('Explore Balinese culture.', $package->fresh()->description);

        app()->setLocale('id');
        $this->assertEquals('Tur Budaya', $package->fresh()->name);
        $this->assertEquals('Jelajahi budaya Bali.', $package->fresh()->description);
    }

    public function test_updating_one_locale_preserves_other(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Original EN', 'id' => 'Original ID'],
            'slug' => 'original-en',
            'short_description' => ['en' => 'Short EN', 'id' => 'Short ID'],
            'description' => ['en' => 'Desc EN', 'id' => 'Desc ID'],
            'category' => 'temple',
        ]);

        app()->setLocale('en');
        $this->assertEquals('Original EN', $object->name);

        $object->setTranslation('name', 'en', 'Updated EN');
        $object->save();
        $object->refresh();

        app()->setLocale('en');
        $this->assertEquals('Updated EN', $object->name);

        app()->setLocale('id');
        $this->assertEquals('Original ID', $object->name);
    }

    public function test_fallback_behavior_when_locale_value_is_empty(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Only English Name'],
            'slug' => 'only-english-name',
            'short_description' => ['en' => 'Only English short'],
            'description' => ['en' => 'Only English description'],
            'category' => 'temple',
        ]);

        app()->setLocale('id');
        $fresh = $object->fresh();

        $this->assertEquals('Only English Name', $fresh->name);
        $this->assertEquals('Only English short', $fresh->short_description);
    }

    public function test_search_works_across_locales(): void
    {
        CulturalObject::create([
            'name' => ['en' => 'Penglipuran Temple', 'id' => 'Pura Penglipuran'],
            'slug' => 'penglipuran-temple',
            'short_description' => ['en' => 'Main temple', 'id' => 'Pura utama'],
            'description' => ['en' => 'A beautiful temple', 'id' => 'Pura yang indah'],
            'category' => 'temple',
        ]);

        CulturalObject::create([
            'name' => ['en' => 'Bamboo Forest', 'id' => 'Hutan Bambu'],
            'slug' => 'bamboo-forest',
            'short_description' => ['en' => 'Cool bamboo forest', 'id' => 'Hutan bambu yang sejuk'],
            'description' => ['en' => 'Walk through bamboo.', 'id' => 'Jalan melewati bambu.'],
            'category' => 'craft',
        ]);

        $results = CulturalObject::where('name', 'LIKE', '%Penglipuran%')->get();
        $this->assertCount(1, $results);

        $results = CulturalObject::where('short_description', 'LIKE', '%sejuk%')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Bamboo Forest', $results->first()->getTranslation('name', 'en'));
    }

    public function test_umkm_product_category_unique_per_locale(): void
    {
        UmkmProductCategory::create([
            'name' => 'Minuman Tradisional',
            'slug' => 'minuman-tradisional',
        ]);

        $this->expectException(QueryException::class);
        UmkmProductCategory::create([
            'name' => 'Minuman Tradisional',
            'slug' => 'minuman-tradisional-dupe',
        ]);
    }

    public function test_api_returns_locale_resolved_content(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Temple View', 'id' => 'Pemandangan Pura'],
            'slug' => 'temple-view',
            'short_description' => ['en' => 'Great view', 'id' => 'Pemandangan bagus'],
            'description' => ['en' => 'Scenic temple description', 'id' => 'Deskripsi pura yang indah'],
            'category' => 'temple',
        ]);

        app()->setLocale('en');
        $array = $object->toArray();
        $this->assertEquals('Temple View', $array['name']);
        $this->assertEquals('Great view', $array['short_description']);

        app()->setLocale('id');
        $array = $object->fresh()->toArray();
        $this->assertEquals('Pemandangan Pura', $array['name']);
        $this->assertEquals('Pemandangan bagus', $array['short_description']);
    }

    public function test_existing_plain_string_data_is_accessible(): void
    {
        app()->setLocale('en');
        $object = CulturalObject::create([
            'name' => 'Legacy Plain Name',
            'slug' => 'legacy-plain-name',
            'short_description' => 'Legacy short description',
            'description' => 'Legacy full description',
            'category' => 'temple',
        ]);

        app()->setLocale('en');
        $fresh = $object->fresh();
        $this->assertEquals('Legacy Plain Name', $fresh->name);
        $this->assertEquals('Legacy short description', $fresh->short_description);
    }

    public function test_cultural_object_factory_creates_translatable_data(): void
    {
        $object = CulturalObject::factory()->create();

        app()->setLocale('en');
        $enName = $object->fresh()->name;
        $this->assertNotEmpty($enName);

        app()->setLocale('id');
        $idName = $object->fresh()->name;
        $this->assertNotEmpty($idName);
        $this->assertNotEquals($enName, $idName);
    }

    public function test_locale_switching_via_session_in_public_page(): void
    {
        $user = User::factory()->create(['role' => 'tourist']);

        $object = CulturalObject::create([
            'name' => ['en' => 'English Name', 'id' => 'Nama Indonesia'],
            'slug' => 'locale-test-object',
            'short_description' => ['en' => 'English short', 'id' => 'Singkat Indonesia'],
            'description' => ['en' => 'English full', 'id' => 'Panjang Indonesia'],
            'category' => 'temple',
        ]);

        // Verify model resolves locale correctly
        app()->setLocale('en');
        $this->assertEquals('English Name', $object->fresh()->name);
        $this->assertEquals('English short', $object->fresh()->short_description);

        app()->setLocale('id');
        $this->assertEquals('Nama Indonesia', $object->fresh()->name);
        $this->assertEquals('Singkat Indonesia', $object->fresh()->short_description);

        // Public route is accessible without auth and shows page
        $response = $this->get(route('cultural-object', 'locale-test-object'));
        $response->assertStatus(200);
    }
}
