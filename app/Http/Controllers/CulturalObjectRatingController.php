<?php

namespace App\Http\Controllers;

use App\Models\CulturalObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CulturalObjectRatingController extends Controller
{
    public function store(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $object = CulturalObject::where('slug', $slug)->firstOrFail();
        $user = $request->user();

        abort_if(! $object->isVisitedBy($user), 403, 'Anda harus mengunjungi objek ini terlebih dahulu sebelum memberi rating.');

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $rating = $object->ratings()->updateOrCreate(
            ['user_id' => $user->id],
            $validated,
        );

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'rating' => $rating]);
        }

        return back()->with('status', 'Terima kasih atas rating Anda!');
    }
}
