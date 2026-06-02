<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CulturalObject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CulturalObjectController extends Controller
{
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
            'model_3d_path' => ['nullable', 'string', 'max:255'],
            'model_3d_usdz_path' => ['nullable', 'string', 'max:255'],
            'audio_narration_path' => ['nullable', 'string', 'max:255'],
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
        ]);

        if ($request->hasFile('model_3d_file')) {
            $validated['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
        }

        if ($request->hasFile('model_3d_usdz_file')) {
            $validated['model_3d_usdz_path'] = $request->file('model_3d_usdz_file')->store('models_usdz', 'public');
        }

        if ($request->hasFile('audio_narration_file')) {
            $validated['audio_narration_path'] = $request->file('audio_narration_file')->store('audio', 'public');
        }

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
        if (empty($validated['ar_marker_id'])) {
            $validated['ar_marker_id'] = 'MARKER_'.strtoupper(Str::random(8));
        }

        if ($request->filled('ar_marker_patt_content')) {
            $pattPath = 'ar-markers/'.$validated['ar_marker_id'].'.patt';
            Storage::disk('public')->put($pattPath, $request->input('ar_marker_patt_content'));
            $validated['ar_marker_patt_path'] = $pattPath;
        }

        $latitude = $validated['latitude'] ?? -8.4217504;
        $longitude = $validated['longitude'] ?? 115.3590021;

        // Clean up temporary variables not in DB schema
        unset($validated['model_3d_file'], $validated['audio_narration_file'], $validated['latitude'], $validated['longitude'], $validated['has_quiz'], $validated['quiz_question'], $validated['quiz_option_a'], $validated['quiz_option_b'], $validated['quiz_option_c'], $validated['quiz_option_d'], $validated['quiz_correct_option'], $validated['ar_marker_patt_content']);

        $object = CulturalObject::create($validated);

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

        $object->mapLocation()->create([
            'name' => $object->name,
            'category' => 'cultural',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_accessible' => $request->has('is_accessible'),
            'accessibility_notes' => $request->input('accessibility_notes') ?? 'Akses jalan datar ramah kursi roda dan stroller bayi.',
        ]);

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
            'model_3d_path' => ['nullable', 'string', 'max:255'],
            'model_3d_usdz_path' => ['nullable', 'string', 'max:255'],
            'audio_narration_path' => ['nullable', 'string', 'max:255'],
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
        ]);

        if ($request->hasFile('model_3d_file')) {
            $validated['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
        } elseif (! isset($validated['model_3d_path'])) {
            $validated['model_3d_path'] = $object->model_3d_path;
        }

        if ($request->hasFile('model_3d_usdz_file')) {
            $validated['model_3d_usdz_path'] = $request->file('model_3d_usdz_file')->store('models_usdz', 'public');
        } elseif (! isset($validated['model_3d_usdz_path'])) {
            $validated['model_3d_usdz_path'] = $object->model_3d_usdz_path;
        }

        if ($request->hasFile('audio_narration_file')) {
            $validated['audio_narration_path'] = $request->file('audio_narration_file')->store('audio', 'public');
        } elseif (! isset($validated['audio_narration_path'])) {
            $validated['audio_narration_path'] = $object->audio_narration_path;
        }

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
        if (empty($validated['ar_marker_id'])) {
            $validated['ar_marker_id'] = 'MARKER_'.strtoupper(Str::random(8));
        }

        if ($request->filled('ar_marker_patt_content')) {
            $pattPath = 'ar-markers/'.$validated['ar_marker_id'].'.patt';
            Storage::disk('public')->put($pattPath, $request->input('ar_marker_patt_content'));
            $validated['ar_marker_patt_path'] = $pattPath;
        } elseif (! isset($validated['ar_marker_patt_path'])) {
            $validated['ar_marker_patt_path'] = $object->ar_marker_patt_path;
        }

        $latitude = $validated['latitude'] ?? -8.4217504;
        $longitude = $validated['longitude'] ?? 115.3590021;

        // Clean up temporary variables not in DB schema
        unset($validated['model_3d_file'], $validated['audio_narration_file'], $validated['latitude'], $validated['longitude'], $validated['has_quiz'], $validated['quiz_question'], $validated['quiz_option_a'], $validated['quiz_option_b'], $validated['quiz_option_c'], $validated['quiz_option_d'], $validated['quiz_correct_option'], $validated['ar_marker_patt_content']);

        $object->update($validated);

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

        $object->mapLocation()->updateOrCreate(
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
}
