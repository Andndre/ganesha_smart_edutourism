<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CulturalObject;
use App\Models\CulturalObjectRating;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CulturalObjectRatingController extends Controller
{
    public function index(): View
    {
        $objects = CulturalObject::withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->whereHas('ratings')
            ->with(['ratings' => fn ($q) => $q->with('user')->latest()->limit(50)])
            ->orderByDesc('ratings_count')
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.cultural-object-ratings.index', compact('objects'));
    }

    public function destroy(CulturalObjectRating $rating): RedirectResponse
    {
        $rating->delete();

        return back()->with('status', 'Rating berhasil dihapus.');
    }
}
