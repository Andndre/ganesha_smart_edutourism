<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\MapLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Pin objek budaya punya dua sumbu baru: place_type memilih glyph-nya, dan is_detail
 * menandai pin tingkat 2 (komponen pekarangan rumah) yang hanya muncul di zoom dekat.
 */
class ExploreCulturalPinTest extends TestCase
{
    use RefreshDatabase;

    private function makeCultural(string $name, array $extra = []): CulturalObject
    {
        return CulturalObject::create(array_merge([
            'name' => ['id' => $name, 'en' => $name],
            'slug' => Str::slug($name),
            'description' => ['id' => 'Deskripsi', 'en' => 'Description'],
            'category' => 'parahyangan',
        ], $extra));
    }

    private function pinFor(CulturalObject $object, array $extra = []): MapLocation
    {
        return MapLocation::create(array_merge([
            'locationable_type' => CulturalObject::class,
            'locationable_id' => $object->id,
            'name' => $object->getMapDisplayName(),
            'category' => 'cultural',
            'latitude' => -8.4210,
            'longitude' => 115.3592,
        ], $extra));
    }

    private function payloadFor(int $locationId): array
    {
        $response = $this->get('/explore');
        $response->assertOk();

        foreach ($response->viewData('locations') as $loc) {
            if ($loc['id'] === $locationId) {
                return $loc;
            }
        }

        $this->fail("Lokasi {$locationId} tidak ada di payload explore");
    }

    public function test_explore_payload_carries_place_type(): void
    {
        $object = $this->makeCultural('Pura Puseh', ['place_type' => 'pura']);
        $pin = $this->pinFor($object);

        $this->assertSame('pura', $this->payloadFor($pin->id)['place_type']);
    }

    public function test_cultural_object_without_place_type_falls_back_to_null(): void
    {
        $object = $this->makeCultural('Objek Tanpa Jenis');
        $pin = $this->pinFor($object);

        $this->assertNull($this->payloadFor($pin->id)['place_type']);
    }

    public function test_pins_default_to_top_level_not_detail(): void
    {
        $object = $this->makeCultural('Bale Kulkul', ['place_type' => 'bale']);
        $pin = $this->pinFor($object);

        $this->assertFalse($this->payloadFor($pin->id)['is_detail']);
    }

    /**
     * is_detail ada di objeknya, bukan di tiap pin: satu objek komponen dipin di
     * puluhan rumah dan semuanya berperilaku sama, jadi menandainya sekali sudah
     * berlaku untuk seluruh pinnya.
     */
    public function test_house_component_is_marked_as_detail_on_the_object(): void
    {
        $object = $this->makeCultural('Saka Enam', ['place_type' => 'bale', 'is_detail' => true]);
        $pin = $this->pinFor($object);

        $this->assertTrue($this->payloadFor($pin->id)['is_detail']);
    }

    public function test_marking_the_object_covers_every_one_of_its_pins(): void
    {
        $object = $this->makeCultural('Merajan Rumah Tradisional', ['place_type' => 'pura', 'is_detail' => true]);
        $first = $this->pinFor($object);
        $second = $this->pinFor($object, ['latitude' => -8.4215, 'longitude' => 115.3599]);

        $this->assertTrue($this->payloadFor($first->id)['is_detail']);
        $this->assertTrue($this->payloadFor($second->id)['is_detail']);
    }

    public function test_explore_payload_translates_cultural_object_name_and_notes_according_to_locale(): void
    {
        $object = CulturalObject::create([
            'name' => [
                'id' => 'Relief Sejarah Desa Penglipuran',
                'en' => 'Penglipuran History Relief',
            ],
            'slug' => 'penglipuran-history-relief',
            'description' => [
                'id' => 'Relief sejarah menggambarkan kronologi Desa Penglipuran.',
                'en' => 'The history relief depicts Penglipuran chronology.',
            ],
            'category' => 'parahyangan',
        ]);

        $pin = MapLocation::create([
            'locationable_type' => CulturalObject::class,
            'locationable_id' => $object->id,
            'name' => 'Relief Sejarah Desa Penglipuran', // Static database name
            'category' => 'cultural',
            'latitude' => -8.4210,
            'longitude' => 115.3592,
            'is_accessible' => true,
            'accessibility_notes' => [
                'id' => 'Akses ramah disabilitas tersedia.',
                'en' => 'Wheelchair access is available.',
            ],
        ]);

        // English request
        $responseEn = $this->get('/explore?locale=en');
        $responseEn->assertOk();
        $locationsEn = $responseEn->viewData('locations');
        $locEn = collect($locationsEn)->firstWhere('id', $pin->id);

        $this->assertNotNull($locEn);
        $this->assertSame('Penglipuran History Relief', $locEn['name']);
        $this->assertStringContainsString('The history relief depicts', $locEn['desc']);
        $this->assertSame('Wheelchair access is available.', $locEn['accessibility']);

        // Indonesian request
        $responseId = $this->get('/explore?locale=id');
        $responseId->assertOk();
        $locationsId = $responseId->viewData('locations');
        $locId = collect($locationsId)->firstWhere('id', $pin->id);

        $this->assertNotNull($locId);
        $this->assertSame('Relief Sejarah Desa Penglipuran', $locId['name']);
        $this->assertStringContainsString('Relief sejarah menggambarkan', $locId['desc']);
        $this->assertSame('Akses ramah disabilitas tersedia.', $locId['accessibility']);
    }
}
