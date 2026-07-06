<?php

namespace Tests\Feature;

use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AdminCulturalObjectImportTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');

        // Create an admin user to authenticate
        $this->adminUser = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'preferred_language' => 'en',
        ]);
    }

    /**
     * Test download template is working.
     */
    public function test_admin_can_download_import_template(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.cultural-objects.import-template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename=template_import_objek_budaya.xlsx');
    }

    /**
     * Test uploading a valid XLSX file works and populates database.
     */
    public function test_admin_can_import_valid_xlsx_file(): void
    {
        Storage::fake('public');

        // 1. Create in-memory XLSX file using PhpSpreadsheet
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Nama (ID)',
            'Nama (EN)',
            'Kategori (temple/house/craft/tradition)',
            'Deskripsi Singkat (ID)',
            'Deskripsi Singkat (EN)',
            'Deskripsi Lengkap (ID)',
            'Deskripsi Lengkap (EN)',
            'Latitude',
            'Longitude',
            'Akses Disabilitas (Y/N)',
            'Catatan Aksesibilitas (ID)',
            'Catatan Aksesibilitas (EN)',
            'Marker ID (Opsional)',
        ];

        foreach ($headers as $colIndex => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter.'1', $header);
        }

        $row1 = [
            'Pura Kehen',
            'Kehen Temple',
            'temple',
            'Pura bersejarah di Bangli',
            'Historical temple in Bangli',
            'Pura Kehen adalah tempat pemujaan leluhur...',
            'Kehen temple is a place of worship...',
            '-8.445123',
            '115.355123',
            'Y',
            'Terdapat ramp landai.',
            'Ramps are available.',
            'MARKER_KEHEN',
        ];

        $row2 = [
            'Baju Barong',
            'Barong Shirt',
            'craft',
            'Kerajinan kaos lukis barong',
            'Barong painted t-shirt craft',
            'Kaos lukis khas Bali yang dibuat seniman...',
            'Typical Balinese painted t-shirt made by artists...',
            '-8.451000',
            '115.352000',
            'N',
            'Jalan berbatu kasar.',
            'Rough stone road.',
            '', // Empty marker
        ];

        foreach ($row1 as $colIndex => $value) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter.'2', $value);
        }

        foreach ($row2 as $colIndex => $value) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter.'3', $value);
        }

        // Save spreadsheet to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        // Create UploadedFile
        $uploadedFile = new UploadedFile(
            $tempFile,
            'import_test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // 2. Post file to import route
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.cultural-objects.import-xlsx'), [
                'file' => $uploadedFile,
            ]);

        $response->assertRedirect(route('admin.map-manager'));
        $response->assertSessionHas('success');

        // 3. Verify Database records
        $this->assertDatabaseHas('cultural_objects', [
            'slug' => 'kehen-temple',
        ]);

        $object1 = CulturalObject::where('slug', 'kehen-temple')->firstOrFail();
        $this->assertEquals('Kehen Temple', $object1->getTranslation('name', 'en'));
        $this->assertEquals('Pura Kehen', $object1->getTranslation('name', 'id'));
        $this->assertEquals('temple', $object1->category);

        $this->assertNotNull($object1->mapLocation);
        $this->assertEquals(-8.445123, $object1->mapLocation->latitude);
        $this->assertEquals(115.355123, $object1->mapLocation->longitude);
        $this->assertTrue($object1->mapLocation->is_accessible);

        // ArModel check
        $arModel = ArModel::where('ar_marker_id', 'MARKER_KEHEN')->firstOrFail();
        $this->assertEquals('Kehen Temple Model', $arModel->getTranslation('name', 'en'));
        $this->assertEquals($object1->mapLocation->id, $arModel->map_location_id);

        // Object 2 check
        $object2 = CulturalObject::where('slug', 'barong-shirt')->firstOrFail();
        $this->assertEquals('Barong Shirt', $object2->getTranslation('name', 'en'));
        $this->assertFalse($object2->mapLocation->is_accessible);

        // Clean up temp file
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    /**
     * Test uploading invalid file format fails validation.
     */
    public function test_admin_cannot_upload_invalid_file_format(): void
    {
        $file = UploadedFile::fake()->create('invalid.txt', 100);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.cultural-objects.import-xlsx'), [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors(['file']);
    }
}
