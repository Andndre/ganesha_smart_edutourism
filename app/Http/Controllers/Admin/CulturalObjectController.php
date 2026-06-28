<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\NormalizesMultilingualInput;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CulturalObjectRequest;
use App\Http\Requests\Admin\ImportXlsxRequest;
use App\Http\Requests\Admin\UploadEditorImageRequest;
use App\Models\ArModel;
use App\Models\CulturalObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CulturalObjectController extends Controller
{
    use NormalizesMultilingualInput;

    /**
     * Store a newly created cultural object in storage.
     */
    public function store(CulturalObjectRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('historical_images')) {
            $images = [];
            foreach ($request->file('historical_images') as $file) {
                $images[] = $file->store('images', 'public');
            }
            $validated['historical_images'] = $images;
        }

        $defaultLocale = config('app.fallback_locale', 'en');
        $slugValue = $validated['name'][$defaultLocale] ?? $validated['name']['en'] ?? reset($validated['name']);
        $validated['slug'] = (new CulturalObject)->generateSlug($slugValue);

        // Null safety for database constraints
        if (empty($validated['description']['en']) && empty($validated['description']['id'])) {
            $nameValue = $validated['name'][$defaultLocale] ?? $validated['name']['en'] ?? reset($validated['name']);
            $validated['description'] = [
                'en' => 'Description for '.$nameValue,
                'id' => 'Deskripsi untuk '.$nameValue,
            ];
        }

        $latitude = $validated['latitude'] ?? config('services.penglipuran.latitude');
        $longitude = $validated['longitude'] ?? config('services.penglipuran.longitude');

        // Clean up temporary variables not in DB schema
        unset(
            $validated['latitude'],
            $validated['longitude'],
            $validated['has_quiz'],
            $validated['quiz_question'],
            $validated['quiz_option_a'],
            $validated['quiz_option_b'],
            $validated['quiz_option_c'],
            $validated['quiz_option_d'],
            $validated['quiz_correct_option'],
            $validated['has_story'],
            $validated['story_title'],
            $validated['story_content'],
            $validated['story_type'],
            // Decoupled AR fields
            $validated['ar_marker_id'],
            $validated['ar_marker_patt_content'],
            $validated['ar_model_id'],
            $validated['new_model_name'],
            $validated['new_model_description'],
            $validated['model_3d_file'],
            $validated['model_3d_usdz_file'],
            $validated['audio_narration_file'],
            $validated['cultural_audio_file']
        );

        // Handle locale-specific audio narration uploads
        $audioPaths = [];
        foreach (['en', 'id'] as $locale) {
            $fileKey = "cultural_audio_file.{$locale}";
            if ($request->hasFile($fileKey)) {
                $audioPaths[$locale] = $request->file($fileKey)->store('audio', 'public');
            }
        }
        if (!empty($audioPaths)) {
            $validated['audio_narration_paths'] = $audioPaths;
        }

        $object = CulturalObject::create($validated);

        if ($request->has('has_story') && $request->has('story_title')) {
            $titles = $request->input('story_title');
            $contents = $request->input('story_content');
            $types = $request->input('story_type');

            foreach ($titles as $index => $title) {
                if (! empty($title['en']) || ! empty($title['id'])) {
                    $object->stories()->create([
                        'title' => $title,
                        'content' => $contents[$index] ?? null,
                        'story_type' => $types[$index] ?? 'history',
                        'order' => $index + 1,
                    ]);
                }
            }
        }

        if ($request->has('has_quiz') && $request->has('quiz_question')) {
            $questions = $request->input('quiz_question');
            $optionA = $request->input('quiz_option_a');
            $optionB = $request->input('quiz_option_b');
            $optionC = $request->input('quiz_option_c');
            $optionD = $request->input('quiz_option_d');
            $correctOptions = $request->input('quiz_correct_option');

            foreach ($questions as $index => $question) {
                if (! empty($question['en']) || ! empty($question['id'])) {
                    $object->quizzes()->create([
                        'question' => $question,
                        'option_a' => $optionA[$index] ?? ['en' => '', 'id' => ''],
                        'option_b' => $optionB[$index] ?? ['en' => '', 'id' => ''],
                        'option_c' => $optionC[$index] ?? ['en' => '', 'id' => ''],
                        'option_d' => $optionD[$index] ?? ['en' => '', 'id' => ''],
                        'correct_option' => $correctOptions[$index] ?? 'A',
                    ]);
                }
            }
        }

        $mapLocation = $object->syncMapLocation([
            'category' => 'cultural',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_accessible' => $request->has('is_accessible'),
            'accessibility_notes' => $request->input('accessibility_notes') ?? 'Akses jalan datar ramah kursi roda dan stroller bayi.',
        ]);

        // AR model logic: create new or link existing
        $arModelId = $request->input('ar_model_id');
        $arMarkerId = $request->input('ar_marker_id');

        $shouldCreateNewModel = $arModelId === 'new' ||
            (empty($arModelId) && ($request->hasFile('model_3d_file') || $request->hasFile('model_3d_usdz_file') || $request->hasFile('audio_narration_file')));

        if ($shouldCreateNewModel) {
            $submittedName = $request->input('new_model_name', []);
            if (\is_array($submittedName) && count($submittedName) === 1) {
                if (isset($submittedName['en']) && ! isset($submittedName['id'])) {
                    $submittedName['id'] = $submittedName['en'];
                } elseif (isset($submittedName['id']) && ! isset($submittedName['en'])) {
                    $submittedName['en'] = $submittedName['id'];
                }
            }

            $modelData = [
                'name' => $submittedName ?: [$defaultLocale => ($object->name[$defaultLocale] ?? 'Model').' Model'],
                'description' => $request->input('new_model_description') ?: ($object->short_description ?? null),
                'map_location_id' => $mapLocation->id,
                'ar_marker_id' => $arMarkerId ?: null,
            ];

            if ($request->hasFile('model_3d_file')) {
                $modelData['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
            }
            if ($request->hasFile('model_3d_usdz_file')) {
                $modelData['model_3d_usdz_path'] = $request->file('model_3d_usdz_file')
                    ->storeAs('models_usdz', Str::random(40).'.usdz', 'public');
            }
            if ($request->hasFile('audio_narration_file')) {
                $modelData['audio_narration_path'] = $request->file('audio_narration_file')->store('audio', 'public');
            }
            if ($request->filled('ar_marker_patt_content') && $arMarkerId) {
                $pattPath = 'ar-markers/'.$arMarkerId.'.patt';
                Storage::disk('public')->put($pattPath, $request->input('ar_marker_patt_content'));
                $modelData['ar_marker_patt_path'] = $pattPath;
            }

            ArModel::create($modelData);
        } elseif (is_numeric($arModelId)) {
            $arModel = ArModel::find((int) $arModelId);
            if ($arModel) {
                $modelData = ['map_location_id' => $mapLocation->id];

                // Handle multilingual name with partial locale merging
                if ($request->has('new_model_name')) {
                    $existingName = $arModel->getTranslations('name');
                    $newName = array_merge($existingName, $request->input('new_model_name', []));
                    $modelData['name'] = $newName;
                }

                // Handle multilingual description with partial locale merging
                if ($request->has('new_model_description')) {
                    $existingDesc = $arModel->getTranslations('description');
                    $newDesc = array_merge($existingDesc, $request->input('new_model_description', []));
                    $modelData['description'] = $newDesc;
                }

                // Handle file replacement: upload new, THEN delete old
                if ($request->hasFile('model_3d_file')) {
                    $newPath = $request->file('model_3d_file')->store('models', 'public');
                    if ($arModel->model_3d_path) {
                        Storage::disk('public')->delete($arModel->model_3d_path);
                    }
                    $modelData['model_3d_path'] = $newPath;
                }

                if ($request->hasFile('model_3d_usdz_file')) {
                    $newPath = $request->file('model_3d_usdz_file')
                        ->storeAs('models_usdz', Str::random(40).'.usdz', 'public');
                    if ($arModel->model_3d_usdz_path) {
                        Storage::disk('public')->delete($arModel->model_3d_usdz_path);
                    }
                    $modelData['model_3d_usdz_path'] = $newPath;
                }

                if ($request->hasFile('audio_narration_file')) {
                    $newPath = $request->file('audio_narration_file')->store('audio', 'public');
                    if ($arModel->audio_narration_path) {
                        Storage::disk('public')->delete($arModel->audio_narration_path);
                    }
                    $modelData['audio_narration_path'] = $newPath;
                }

                $arModel->update($modelData);
            }
        }

        return redirect()->route('admin.map-manager')->with('success', __('Objek budaya berhasil ditambahkan.'));
    }

    /**
     * Update the specified cultural object in storage.
     */
    public function update(CulturalObjectRequest $request, int $id): RedirectResponse
    {
        $object = CulturalObject::findOrFail($id);

        $validated = $request->validated();

        if ($request->hasFile('historical_images')) {
            $images = [];
            foreach ($request->file('historical_images') as $file) {
                $images[] = $file->store('images', 'public');
            }
            $validated['historical_images'] = $images;
        } else {
            $validated['historical_images'] = $object->historical_images;
        }

        $defaultLocale = config('app.fallback_locale', 'en');
        $slugValue = $validated['name'][$defaultLocale] ?? $validated['name']['en'] ?? reset($validated['name']);
        $validated['slug'] = $object->generateSlug($slugValue);

        // Null safety for database constraints
        if (empty($validated['description']['en']) && empty($validated['description']['id'])) {
            $nameValue = $validated['name'][$defaultLocale] ?? $validated['name']['en'] ?? reset($validated['name']);
            $validated['description'] = [
                'en' => 'Description for '.$nameValue,
                'id' => 'Deskripsi untuk '.$nameValue,
            ];
        }

        $latitude = $validated['latitude'] ?? config('services.penglipuran.latitude');
        $longitude = $validated['longitude'] ?? config('services.penglipuran.longitude');

        // Clean up temporary variables not in DB schema
        unset(
            $validated['latitude'],
            $validated['longitude'],
            $validated['has_quiz'],
            $validated['quiz_question'],
            $validated['quiz_option_a'],
            $validated['quiz_option_b'],
            $validated['quiz_option_c'],
            $validated['quiz_option_d'],
            $validated['quiz_correct_option'],
            $validated['has_story'],
            $validated['story_title'],
            $validated['story_content'],
            $validated['story_type'],
            // Decoupled AR fields
            $validated['ar_marker_id'],
            $validated['ar_marker_patt_content'],
            $validated['ar_model_id'],
            $validated['new_model_name'],
            $validated['new_model_description'],
            $validated['model_3d_file'],
            $validated['model_3d_usdz_file'],
            $validated['audio_narration_file'],
            $validated['cultural_audio_file']
        );

        // Handle locale-specific audio narration uploads (merge, replace old files)
        $existingAudioPaths = $object->audio_narration_paths ?? [];
        $audioChanged = false;
        foreach (['en', 'id'] as $locale) {
            $fileKey = "cultural_audio_file.{$locale}";
            if ($request->hasFile($fileKey)) {
                $newPath = $request->file($fileKey)->store('audio', 'public');
                if (!empty($existingAudioPaths[$locale])) {
                    Storage::disk('public')->delete($existingAudioPaths[$locale]);
                }
                $existingAudioPaths[$locale] = $newPath;
                $audioChanged = true;
            }
        }
        if ($audioChanged) {
            $validated['audio_narration_paths'] = $existingAudioPaths;
        }

        $object->update($validated);

        $object->stories()->delete();

        if ($request->has('has_story') && $request->has('story_title')) {
            $titles = $request->input('story_title');
            $contents = $request->input('story_content');
            $types = $request->input('story_type');

            foreach ($titles as $index => $title) {
                if (! empty($title['en']) || ! empty($title['id'])) {
                    $object->stories()->create([
                        'title' => $title,
                        'content' => $contents[$index] ?? null,
                        'story_type' => $types[$index] ?? 'history',
                        'order' => $index + 1,
                    ]);
                }
            }
        }

        $object->quizzes()->delete();

        if ($request->has('has_quiz') && $request->has('quiz_question')) {
            $questions = $request->input('quiz_question');
            $optionA = $request->input('quiz_option_a');
            $optionB = $request->input('quiz_option_b');
            $optionC = $request->input('quiz_option_c');
            $optionD = $request->input('quiz_option_d');
            $correctOptions = $request->input('quiz_correct_option');

            foreach ($questions as $index => $question) {
                if (! empty($question['en']) || ! empty($question['id'])) {
                    $object->quizzes()->create([
                        'question' => $question,
                        'option_a' => $optionA[$index] ?? ['en' => '', 'id' => ''],
                        'option_b' => $optionB[$index] ?? ['en' => '', 'id' => ''],
                        'option_c' => $optionC[$index] ?? ['en' => '', 'id' => ''],
                        'option_d' => $optionD[$index] ?? ['en' => '', 'id' => ''],
                        'correct_option' => $correctOptions[$index] ?? 'A',
                    ]);
                }
            }
        }

        $mapLocation = $object->syncMapLocation([
            'category' => 'cultural',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_accessible' => $request->has('is_accessible'),
            'accessibility_notes' => $request->input('accessibility_notes') ?? 'Akses jalan datar ramah kursi roda dan stroller bayi.',
        ], isUpdate: true);

        // Sync AR model link
        $arModelId = $request->input('ar_model_id');
        $arMarkerId = $request->input('ar_marker_id');

        $shouldCreateNewModel = $arModelId === 'new' ||
            ($arModelId !== 'none' && empty($arModelId) && ($request->hasFile('model_3d_file') || $request->hasFile('model_3d_usdz_file') || $request->hasFile('audio_narration_file')));

        if ($shouldCreateNewModel) {
            $submittedName = $request->input('new_model_name', []);
            if (\is_array($submittedName) && count($submittedName) === 1) {
                if (isset($submittedName['en']) && ! isset($submittedName['id'])) {
                    $submittedName['id'] = $submittedName['en'];
                } elseif (isset($submittedName['id']) && ! isset($submittedName['en'])) {
                    $submittedName['en'] = $submittedName['id'];
                }
            }

            $modelData = [
                'name' => $submittedName ?: [$defaultLocale => ($object->name[$defaultLocale] ?? 'Model').' Model'],
                'description' => $request->input('new_model_description') ?: ($object->short_description ?? null),
                'map_location_id' => $mapLocation->id,
                'ar_marker_id' => $arMarkerId ?: null,
            ];

            if ($request->hasFile('model_3d_file')) {
                $modelData['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
            }
            if ($request->hasFile('model_3d_usdz_file')) {
                $modelData['model_3d_usdz_path'] = $request->file('model_3d_usdz_file')
                    ->storeAs('models_usdz', Str::random(40).'.usdz', 'public');
            }
            if ($request->hasFile('audio_narration_file')) {
                $modelData['audio_narration_path'] = $request->file('audio_narration_file')->store('audio', 'public');
            }
            if ($request->filled('ar_marker_patt_content') && $arMarkerId) {
                $pattPath = 'ar-markers/'.$arMarkerId.'.patt';
                Storage::disk('public')->put($pattPath, $request->input('ar_marker_patt_content'));
                $modelData['ar_marker_patt_path'] = $pattPath;
            }

            ArModel::create($modelData);
        } elseif (is_numeric($arModelId)) {
            $arModel = ArModel::find((int) $arModelId);
            if ($arModel) {
                $modelData = ['map_location_id' => $mapLocation->id];

                // Handle multilingual name with partial locale merging
                if ($request->has('new_model_name')) {
                    $existingName = $arModel->getTranslations('name');
                    $newName = array_merge($existingName, $request->input('new_model_name', []));
                    $modelData['name'] = $newName;
                }

                // Handle multilingual description with partial locale merging
                if ($request->has('new_model_description')) {
                    $existingDesc = $arModel->getTranslations('description');
                    $newDesc = array_merge($existingDesc, $request->input('new_model_description', []));
                    $modelData['description'] = $newDesc;
                }

                // Handle file replacement: upload new, THEN delete old
                if ($request->hasFile('model_3d_file')) {
                    $newPath = $request->file('model_3d_file')->store('models', 'public');
                    if ($arModel->model_3d_path) {
                        Storage::disk('public')->delete($arModel->model_3d_path);
                    }
                    $modelData['model_3d_path'] = $newPath;
                }

                if ($request->hasFile('model_3d_usdz_file')) {
                    $newPath = $request->file('model_3d_usdz_file')
                        ->storeAs('models_usdz', Str::random(40).'.usdz', 'public');
                    if ($arModel->model_3d_usdz_path) {
                        Storage::disk('public')->delete($arModel->model_3d_usdz_path);
                    }
                    $modelData['model_3d_usdz_path'] = $newPath;
                }

                if ($request->hasFile('audio_narration_file')) {
                    $newPath = $request->file('audio_narration_file')->store('audio', 'public');
                    if ($arModel->audio_narration_path) {
                        Storage::disk('public')->delete($arModel->audio_narration_path);
                    }
                    $modelData['audio_narration_path'] = $newPath;
                }

                $arModel->update($modelData);
            }
        } elseif ($arModelId === 'none') {
            // Detach: clear map_location_id from any model currently linked here
            ArModel::where('map_location_id', $mapLocation->id)->update(['map_location_id' => null]);
        }

        return redirect()->route('admin.map-manager')->with('success', __('Objek budaya berhasil diperbarui.'));
    }

    /**
     * Remove the specified cultural object from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $object = CulturalObject::findOrFail($id);
        $object->delete();

        return redirect()->route('admin.map-manager')->with('success', __('Objek budaya berhasil dihapus.'));
    }

    /**
     * Upload an image from TipTap editor and return its public URL.
     */
    public function uploadEditorImage(UploadEditorImageRequest $request): JsonResponse
    {
        $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('editor-images', 'public');

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

        // Write headers
        foreach ($headers as $colIndex => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter.'1', $header);
        }

        // Write a sample row
        $sampleRow = [
            'Pura Penataran Agung',
            'Penataran Agung Temple',
            'temple',
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
            '2. Kolom Kategori hanya menerima nilai: temple (Pura), house (Rumah Adat), craft (Kerajinan), atau tradition (Tradisi).',
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
                $category = trim($row[2] ?? 'temple');

                // Validate category
                if (! in_array($category, ['temple', 'house', 'craft', 'tradition'])) {
                    $category = 'temple';
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
                $mapLocation = $culturalObject->mapLocation()->updateOrCreate(
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
                        ['ar_marker_id' => $markerId],
                        [
                            'name' => ['id' => $nameId.' Model', 'en' => $nameEn.' Model'],
                            'description' => ['id' => $shortDescId, 'en' => $shortDescEn],
                            'map_location_id' => $mapLocation->id,
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
