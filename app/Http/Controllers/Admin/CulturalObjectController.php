<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArMarker;
use App\Models\ArModel;
use App\Models\CulturalObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CulturalObjectController extends Controller
{
    /**
     * Store a newly created cultural object in storage.
     */
    /**
     * Store a newly created cultural object in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:cultural_objects'],
            'category' => ['required', 'string', 'in:temple,house,craft,tradition'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ar_marker_id' => ['nullable', 'string', 'max:255'],
            'ar_marker_patt_content' => ['nullable', 'string'],
            'ar_model_id' => ['nullable', 'string'],
            'new_model_name' => ['nullable', 'string', 'max:255'],
            'new_model_description' => ['nullable', 'string'],
            'model_3d_file' => ['nullable', 'file', 'max:20480'],
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'],
            'audio_narration_file' => ['nullable', 'file', 'max:10240'],
            'historical_images' => ['nullable', 'array'],
            'historical_images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'has_quiz' => ['nullable', 'boolean'],
            'quiz_question' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_option_a' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_option_b' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_option_c' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_option_d' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_correct_option' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'has_story' => ['nullable', 'boolean'],
            'story_title' => ['required_if:has_story,1', 'nullable', 'array'],
            'story_content' => ['required_if:has_story,1', 'nullable', 'array'],
            'story_type' => ['required_if:has_story,1', 'nullable', 'array'],
            'story_type.*' => ['in:history,philosophy,value'],
        ]);

        if ($request->hasFile('historical_images')) {
            $images = [];
            foreach ($request->file('historical_images') as $file) {
                $images[] = $file->store('images', 'public');
            }
            $validated['historical_images'] = $images;
        }

        $validated['slug'] = Str::slug($validated['name']);

        // Null safety for database constraints
        if (empty($validated['description'])) {
            $validated['description'] = 'Deskripsi untuk '.$validated['name'];
        }

        $latitude = $validated['latitude'] ?? -8.4217504;
        $longitude = $validated['longitude'] ?? 115.3590021;

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
            $validated['audio_narration_file']
        );

        $object = CulturalObject::create($validated);

        if ($request->has('has_story') && $request->has('story_title')) {
            $titles = $request->input('story_title');
            $contents = $request->input('story_content');
            $types = $request->input('story_type');

            foreach ($titles as $index => $title) {
                if (! empty($title)) {
                    $object->stories()->create([
                        'title' => $title,
                        'content' => $contents[$index] ?? '',
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
                if (! empty($question)) {
                    $object->quizzes()->create([
                        'question' => $question,
                        'option_a' => $optionA[$index] ?? '',
                        'option_b' => $optionB[$index] ?? '',
                        'option_c' => $optionC[$index] ?? '',
                        'option_d' => $optionD[$index] ?? '',
                        'correct_option' => $correctOptions[$index] ?? 'A',
                    ]);
                }
            }
        }

        $mapLocation = $object->mapLocation()->create([
            'name' => $object->name,
            'category' => 'cultural',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_accessible' => $request->has('is_accessible'),
            'accessibility_notes' => $request->input('accessibility_notes') ?? 'Akses jalan datar ramah kursi roda dan stroller bayi.',
        ]);

        // Decoupled AR marker & model logic
        $arMarkerId = $request->input('ar_marker_id');
        $arModelId = $request->input('ar_model_id');

        $finalModelId = null;
        $shouldCreateNewModel = $arModelId === 'new' ||
            (empty($arModelId) && ($request->hasFile('model_3d_file') || $request->hasFile('model_3d_usdz_file') || $request->hasFile('audio_narration_file')));

        if ($shouldCreateNewModel) {
            $modelData = [
                'name' => $request->input('new_model_name') ?: $object->name.' Model',
                'description' => $request->input('new_model_description') ?: $object->short_description ?? null,
            ];

            if ($request->hasFile('model_3d_file')) {
                $modelData['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
            }

            if ($request->hasFile('model_3d_usdz_file')) {
                $file = $request->file('model_3d_usdz_file');
                $filename = Str::random(40).'.usdz';
                $modelData['model_3d_usdz_path'] = $file->storeAs('models_usdz', $filename, 'public');
            }

            if ($request->hasFile('audio_narration_file')) {
                $modelData['audio_narration_path'] = $request->file('audio_narration_file')->store('audio', 'public');
            }

            $newModel = ArModel::create($modelData);
            $finalModelId = $newModel->id;
        } elseif (is_numeric($arModelId)) {
            $finalModelId = (int) $arModelId;
        }

        if (! empty($arMarkerId)) {
            $markerData = [
                'ar_marker_id' => $arMarkerId,
                'ar_model_id' => $finalModelId,
                'map_location_id' => $mapLocation->id,
            ];

            if ($request->filled('ar_marker_patt_content')) {
                $pattPath = 'ar-markers/'.$arMarkerId.'.patt';
                Storage::disk('public')->put($pattPath, $request->input('ar_marker_patt_content'));
                $markerData['ar_marker_patt_path'] = $pattPath;
            }

            ArMarker::create($markerData);
        }

        return redirect()->route('admin.map-manager')->with('success', 'Objek budaya berhasil ditambahkan.');
    }

    /**
     * Update the specified cultural object in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $object = CulturalObject::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:cultural_objects,name,'.$id],
            'category' => ['required', 'string', 'in:temple,house,craft,tradition'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ar_marker_id' => ['nullable', 'string', 'max:255'],
            'ar_marker_patt_content' => ['nullable', 'string'],
            'ar_model_id' => ['nullable', 'string'],
            'new_model_name' => ['nullable', 'string', 'max:255'],
            'new_model_description' => ['nullable', 'string'],
            'model_3d_file' => ['nullable', 'file', 'max:20480'],
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'],
            'audio_narration_file' => ['nullable', 'file', 'max:10240'],
            'historical_images' => ['nullable', 'array'],
            'historical_images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'has_quiz' => ['nullable', 'boolean'],
            'quiz_question' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_option_a' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_option_b' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_option_c' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_option_d' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_correct_option' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'has_story' => ['nullable', 'boolean'],
            'story_title' => ['required_if:has_story,1', 'nullable', 'array'],
            'story_content' => ['required_if:has_story,1', 'nullable', 'array'],
            'story_type' => ['required_if:has_story,1', 'nullable', 'array'],
            'story_type.*' => ['in:history,philosophy,value'],
        ]);

        if ($request->hasFile('historical_images')) {
            $images = [];
            foreach ($request->file('historical_images') as $file) {
                $images[] = $file->store('images', 'public');
            }
            $validated['historical_images'] = $images;
        } else {
            $validated['historical_images'] = $object->historical_images;
        }

        $validated['slug'] = Str::slug($validated['name']);

        // Null safety for database constraints
        if (empty($validated['description'])) {
            $validated['description'] = 'Deskripsi untuk '.$validated['name'];
        }

        $latitude = $validated['latitude'] ?? -8.4217504;
        $longitude = $validated['longitude'] ?? 115.3590021;

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
            $validated['audio_narration_file']
        );

        $object->update($validated);

        $object->stories()->delete();

        if ($request->has('has_story') && $request->has('story_title')) {
            $titles = $request->input('story_title');
            $contents = $request->input('story_content');
            $types = $request->input('story_type');

            foreach ($titles as $index => $title) {
                if (! empty($title)) {
                    $object->stories()->create([
                        'title' => $title,
                        'content' => $contents[$index] ?? '',
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
                if (! empty($question)) {
                    $object->quizzes()->create([
                        'question' => $question,
                        'option_a' => $optionA[$index] ?? '',
                        'option_b' => $optionB[$index] ?? '',
                        'option_c' => $optionC[$index] ?? '',
                        'option_d' => $optionD[$index] ?? '',
                        'correct_option' => $correctOptions[$index] ?? 'A',
                    ]);
                }
            }
        }

        $mapLocation = $object->mapLocation()->updateOrCreate(
            [],
            [
                'name' => $object->name,
                'category' => 'cultural',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'is_accessible' => $request->has('is_accessible'),
                'accessibility_notes' => $request->input('accessibility_notes') ?? 'Akses jalan datar ramah kursi roda dan stroller bayi.',
            ]
        );

        // Sync Decoupled AR marker & model logic
        $arMarkerId = $request->input('ar_marker_id');
        $arModelId = $request->input('ar_model_id');

        $finalModelId = null;
        $shouldCreateNewModel = $arModelId === 'new' ||
            ($arModelId !== 'none' && empty($arModelId) && ($request->hasFile('model_3d_file') || $request->hasFile('model_3d_usdz_file') || $request->hasFile('audio_narration_file')));

        if ($shouldCreateNewModel) {
            $modelData = [
                'name' => $request->input('new_model_name') ?: $object->name.' Model',
                'description' => $request->input('new_model_description') ?: $object->short_description ?? null,
            ];

            if ($request->hasFile('model_3d_file')) {
                $modelData['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
            }

            if ($request->hasFile('model_3d_usdz_file')) {
                $file = $request->file('model_3d_usdz_file');
                $filename = Str::random(40).'.usdz';
                $modelData['model_3d_usdz_path'] = $file->storeAs('models_usdz', $filename, 'public');
            }

            if ($request->hasFile('audio_narration_file')) {
                $modelData['audio_narration_path'] = $request->file('audio_narration_file')->store('audio', 'public');
            }

            $newModel = ArModel::create($modelData);
            $finalModelId = $newModel->id;
        } elseif (is_numeric($arModelId)) {
            $finalModelId = (int) $arModelId;
        } else {
            // If none or not provided, but we had a marker before, keep the old model
            $currentMarker = $mapLocation->arMarker;
            if ($currentMarker) {
                $finalModelId = $currentMarker->ar_model_id;
            }
        }

        if (! empty($arMarkerId)) {
            $markerData = [
                'ar_marker_id' => $arMarkerId,
                'ar_model_id' => $finalModelId,
            ];

            if ($request->filled('ar_marker_patt_content')) {
                $pattPath = 'ar-markers/'.$arMarkerId.'.patt';
                Storage::disk('public')->put($pattPath, $request->input('ar_marker_patt_content'));
                $markerData['ar_marker_patt_path'] = $pattPath;
            }

            $mapLocation->arMarker()->updateOrCreate([], $markerData);
        } else {
            // Delete marker if cleared
            $mapLocation->arMarker()->delete();
        }

        return redirect()->route('admin.map-manager')->with('success', 'Objek budaya berhasil diperbarui.');
    }

    /**
     * Remove the specified cultural object from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $object = CulturalObject::findOrFail($id);
        $object->delete();

        return redirect()->route('admin.map-manager')->with('success', 'Objek budaya berhasil dihapus.');
    }

    /**
     * Upload an image from TipTap editor and return its public URL.
     */
    public function uploadEditorImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('editor-images', 'public');

            return response()->json([
                'url' => asset('storage/'.$path),
            ]);
        }

        return response()->json(['error' => 'Gagal mengunggah gambar.'], 400);
    }
}
