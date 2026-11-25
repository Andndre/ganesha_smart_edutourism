<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\HandlesFileUploads;
use App\Http\Concerns\NormalizesMultilingualInput;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CulturalObjectRequest;
use App\Http\Requests\Admin\ImportXlsxRequest;
use App\Http\Requests\Admin\UploadEditorImageRequest;
use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Services\ArModelService;
use App\Services\ImageOptimizerService;
use App\Services\UnusedArAssetCleaner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CulturalObjectController extends Controller
{
    use HandlesFileUploads;
    use NormalizesMultilingualInput;

    public function __construct(private ArModelService $assets, private UnusedArAssetCleaner $cleaner) {}

    /**
     * Display a listing of cultural objects (dedicated management page, separate from map-manager).
     */
    public function index(Request $request): View
    {
        $query = CulturalObject::query();

        $grammar = $query->getQuery()->getGrammar();
        foreach (preg_split('/\s+/u', mb_strtolower(trim($request->string('search')->toString())), -1, PREG_SPLIT_NO_EMPTY) as $word) {
            $query->where(function ($query) use ($word, $grammar): void {
                $query->whereLike(DB::raw('LOWER('.$grammar->wrap('name->id').')'), '%'.$word.'%')
                    ->orWhereLike(DB::raw('LOWER('.$grammar->wrap('name->en').')'), '%'.$word.'%');
            });
        }

        $hasModel = fn ($modelQuery) => $modelQuery->whereNotNull('model_3d_path')->where('model_3d_path', '!=', '');
        match ($request->query('ar_status')) {
            'with_model' => $query->whereHas('arModel', $hasModel),
            'without_model' => $query->whereDoesntHave('arModel', $hasModel),
            'without_marker' => $query->whereHas('arModel', fn ($modelQuery) => $hasModel($modelQuery)
                ->where(fn ($markerQuery) => $markerQuery->whereNull('ar_marker_id')->orWhere('ar_marker_id', ''))),
            default => null,
        };

        $objects = $query->with(['arModel', 'parent'])->withCount('mapLocations')->orderBy('name->'.app()->getLocale())->paginate(15)->withQueryString();

        return view('admin.cultural-objects.index', compact('objects'));
    }

    /**
     * Show the form for creating a new cultural object.
     */
    public function create(): View
    {
        return view('admin.cultural-objects.create', [
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    /**
     * Show the form for editing an existing cultural object.
     */
    public function edit(int $id): View
    {
        $object = CulturalObject::with('arModel')->findOrFail($id);

        // Diri sendiri dan turunannya tidak boleh jadi induk — itu siklus.
        return view('admin.cultural-objects.edit', [
            'object' => $object,
            'parentOptions' => $this->parentOptions($object),
        ]);
    }

    /**
     * Root objects usable as a parent, minus the edited object and its descendants.
     *
     * ponytail: hierarki dua level saja — anak dari anak tetap bisa dibuat lewat DB,
     * tapi UI menganggap semua induk adalah objek root. Naikkan kalau butuh lebih dalam.
     */
    private function parentOptions(?CulturalObject $except = null): array
    {
        $excluded = $except
            ? array_merge([$except->id], $except->children()->pluck('id')->all())
            : [];

        return CulturalObject::roots()
            ->when($excluded, fn ($q) => $q->whereNotIn('id', $excluded))
            ->orderBy('name->'.app()->getLocale())
            ->get()
            ->mapWithKeys(fn ($o) => [$o->id => $o->getTranslation('name', app()->getLocale())])
            ->all();
    }

    /**
     * Store a newly created cultural object in storage.
     */
    public function store(CulturalObjectRequest $request): RedirectResponse
    {
        $this->persist($request);

        return $this->redirectAfterSave($request, 'Objek budaya berhasil ditambahkan.');
    }

    /**
     * Redirect to the dedicated management page when the request came from there
     * (via a `redirect_to=cultural-objects` hidden field), otherwise back to map-manager.
     */
    private function redirectAfterSave(Request $request, string $message): RedirectResponse
    {
        $route = $request->input('redirect_to') === 'cultural-objects' ? 'admin.cultural-objects' : 'admin.map-manager';

        return redirect()->route($route)->with('success', $message);
    }

    /**
     * Update the specified cultural object in storage.
     */
    public function update(CulturalObjectRequest $request, int $id): RedirectResponse
    {
        $this->persist($request, CulturalObject::findOrFail($id));

        return $this->redirectAfterSave($request, 'Objek budaya berhasil diperbarui.');
    }

    private function persist(CulturalObjectRequest $request, ?CulturalObject $object = null): CulturalObject
    {
        $newPaths = [];
        try {
            return DB::transaction(function () use ($request, $object, &$newPaths) {
                $object = $object
                    ? CulturalObject::whereKey($object->id)->lockForUpdate()->firstOrFail()
                    : new CulturalObject;
                $data = $request->safe()->only([
                    'name', 'short_description', 'description', 'category', 'parent_id', 'place_type', 'is_detail',
                ]);
                $data['slug'] = $object->generateSlug(slugFromTranslatable($data['name']));
                if (! $object->exists || array_key_exists('description', $data)) {
                    $data['description'] ??= ['id' => '', 'en' => ''];
                }
                $removedImages = $request->validated('removed_historical_images') ?? [];
                $images = array_values(array_diff($object->historical_images ?? [], $removedImages));
                $uploads = $request->file('historical_images', []);
                if (count($images) + count($uploads) > 20) {
                    throw ValidationException::withMessages(['historical_images' => 'Maksimal 20 foto per objek. Hapus beberapa foto sebelum menambahkan foto baru.']);
                }
                foreach ($uploads as $image) {
                    $path = app(ImageOptimizerService::class)->optimizeAndStore($image, 'images');
                    $newPaths[] = $path;
                    $images[] = $path;
                }
                $data['historical_images'] = $images;
                $audioPaths = $object->audio_narration_paths ?? [];
                $oldAudio = [];
                foreach (['id', 'en'] as $locale) {
                    if ($request->hasFile('cultural_audio_file.'.$locale)) {
                        $oldAudio[] = $audioPaths[$locale] ?? null;
                        $audioPaths[$locale] = $request->file('cultural_audio_file.'.$locale)->store('audio', 'public');
                        $newPaths[] = $audioPaths[$locale];
                    }
                }
                $data['audio_narration_paths'] = $audioPaths ?: null;
                $object->fill($data)->save();

                if ($request->filled('latitude') && $request->filled('longitude')) {
                    $object->syncMapLocation([
                        'category' => 'cultural',
                        'latitude' => $request->input('latitude'),
                        'longitude' => $request->input('longitude'),
                        'is_accessible' => $request->boolean('is_accessible'),
                        'accessibility_notes' => $request->input('accessibility_notes') ?? ['id' => 'Akses jalan datar ramah kursi roda dan stroller bayi.'],
                    ], isUpdate: $object->mapLocation()->exists());
                }

                if ($request->boolean('remove_ar_model')) {
                    if ($model = $object->arModel()->first()) {
                        $this->assets->delete($model);
                    }
                } elseif ($object->arModel()->exists()
                    || $request->hasFile('model_3d_file') || $request->filled('tmp_model_3d_path')) {
                    $this->assets->save($object, $request);
                }
                DB::afterCommit(fn () => $this->cleaner->deleteUnused(array_merge($oldAudio, $removedImages)));

                return $object;
            });
        } catch (\Throwable $exception) {
            $this->cleaner->deleteUnused($newPaths);
            throw $exception;
        }
    }

    /**
     * Remove the specified cultural object from storage.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $object = CulturalObject::findOrFail($id);

        // FK-nya nullOnDelete, jadi tanpa penjagaan ini anak-anaknya jadi objek root
        // tanpa peringatan. Tolak dulu supaya admin sadar memindahkan atau menghapusnya.
        if ($childCount = $object->children()->count()) {
            $route = $request->input('redirect_to') === 'cultural-objects' ? 'admin.cultural-objects' : 'admin.map-manager';

            return redirect()->route($route)->withErrors([
                'destroy' => "Objek ini masih berisi {$childCount} objek. Pindahkan atau hapus objek di dalamnya dulu.",
            ]);
        }

        if ($object->arModel()->exists()) {
            return back()->withErrors(['destroy' => 'Objek budaya ini masih memiliki model 3D. Hapus model 3D melalui halaman edit terlebih dahulu.']);
        }

        $object->delete();

        return $this->redirectAfterSave($request, 'Objek budaya berhasil dihapus.');
    }

    /**
     * Upload an image from TipTap editor and return its public URL.
     */
    public function uploadEditorImage(UploadEditorImageRequest $request): JsonResponse
    {
        $request->validated();

        if ($request->hasFile('image')) {
            $path = app(ImageOptimizerService::class)->optimizeAndStore(
                $request->file('image'),
                'editor-images',
                1200,
                1200,
                80,
            );

            return response()->json([
                'url' => asset('storage/'.$path),
            ]);
        }

        return response()->json(['error' => __('Gagal mengunggah gambar.')], 400);
    }

    /**
     * Download the XLSX template for bulk importing cultural objects.
     */
    public function downloadImportTemplate()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Nama (ID)',
            'Nama (EN)',
            'Kategori (parahyangan/pawongan/palemahan)',
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

        // Write headers
        foreach ($headers as $colIndex => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter.'1', $header);
        }

        // Write a sample row
        $sampleRow = [
            'Pura Penataran Agung',
            'Penataran Agung Temple',
            'parahyangan',
            'Jantung spiritual Desa Penglipuran',
            'Spiritual heart of Penglipuran Village',
            'Pura ini terletak di bagian paling utara desa...',
            'This temple is located at the northernmost part...',
            '-8.43169720',
            '115.35246720',
            'Y',
            'Akses jalan datar ramah kursi roda.',
            'Flat road access, wheelchair friendly.',
            'MARKER_PURA_01',
        ];

        foreach ($sampleRow as $colIndex => $value) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter.'2', $value);
        }

        // Auto-fit column width
        foreach (range(1, count($headers)) as $col) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Write instructions to the right (Column O)
        $sheet->setCellValue('O1', 'PETUNJUK PENGISIAN IMPORT EXCEL');
        $sheet->getStyle('O1')->getFont()->setBold(true)->setSize(11);

        $instructions = [
            '1. Kolom Nama (ID) & (EN) wajib diisi.',
            '2. Kolom Kategori hanya menerima nilai: parahyangan (Hub. Tuhan), pawongan (Hub. Manusia), atau palemahan (Hub. Alam).',
            '3. Latitude & Longitude harus berupa angka koordinat desimal (contoh: -8.43169, 115.35246).',
            '4. Akses Disabilitas diisi dengan "Y" (Ya) atau "N" (Tidak).',
            '5. Catatan Aksesibilitas menjelaskan detail kemudahan akses (contoh: Pintu masuk landai, ramah kursi roda).',
            '6. Marker ID (Opsional) diisi jika lokasi ini terhubung dengan Augmented Reality (AR) marker.',
            '7. Berkas media biner (seperti gambar, file 3D GLB/USDZ, audio) diunggah manual setelah import selesai via tombol edit.',
        ];

        foreach ($instructions as $idx => $inst) {
            $sheet->setCellValue('O'.($idx + 2), $inst);
        }

        $sheet->getColumnDimension('O')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'template_import_objek_budaya.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import cultural objects from an uploaded XLSX file.
     */
    public function importXlsx(ImportXlsxRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $importedCount = 0;

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue; // Skip header row
                }

                // Check if the row is empty (specifically name columns)
                if (empty($row[0]) || empty($row[1])) {
                    continue;
                }

                $nameId = trim($row[0]);
                $nameEn = trim($row[1]);
                $category = trim($row[2] ?? 'parahyangan');

                // Validate category
                if (! in_array($category, ['parahyangan', 'pawongan', 'palemahan'])) {
                    $category = 'parahyangan';
                }

                $shortDescId = trim($row[3] ?? '');
                $shortDescEn = trim($row[4] ?? $shortDescId);

                $descId = trim($row[5] ?? '');
                $descEn = trim($row[6] ?? $descId);

                $latitude = is_numeric($row[7]) ? (float) $row[7] : config('services.penglipuran.latitude');
                $longitude = is_numeric($row[8]) ? (float) $row[8] : config('services.penglipuran.longitude');

                $isAccessible = in_array(strtoupper(trim($row[9] ?? '')), ['Y', 'YES', '1', 'TRUE']);

                $accNotesId = trim($row[10] ?? 'Akses jalan datar ramah kursi roda dan stroller bayi.');
                $accNotesEn = trim($row[11] ?? 'Flat road access, friendly for wheelchairs and baby strollers.');

                $markerId = trim($row[12] ?? '');

                $slug = Str::slug($nameEn);

                // Create or update CulturalObject
                $culturalObject = CulturalObject::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => ['id' => $nameId, 'en' => $nameEn],
                        'category' => $category,
                        'short_description' => ['id' => $shortDescId, 'en' => $shortDescEn],
                        'description' => ['id' => $descId, 'en' => $descEn],
                    ]
                );

                // Create or update MapLocation
                $culturalObject->mapLocation()->updateOrCreate(
                    [],
                    [
                        'name' => $nameId,
                        'category' => 'cultural',
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'is_accessible' => $isAccessible,
                        'accessibility_notes' => ['id' => $accNotesId, 'en' => $accNotesEn],
                    ]
                );

                // If marker ID is provided, create/link ArModel
                if (! empty($markerId)) {
                    ArModel::updateOrCreate(
                        ['cultural_object_id' => $culturalObject->id],
                        [
                            'ar_marker_id' => $markerId,
                        ]
                    );
                }

                $importedCount++;
            }

            // Clear cache to reflect new locations
            Cache::tags(['cultural'])->flush();

            return redirect()->route('admin.map-manager')->with('success', __(':count objek budaya berhasil di-import.', ['count' => $importedCount]));

        } catch (\Exception $e) {
            return redirect()->route('admin.map-manager')->with('error', __('Gagal mengimport data Excel: :error', ['error' => $e->getMessage()]));
        }
    }
}
