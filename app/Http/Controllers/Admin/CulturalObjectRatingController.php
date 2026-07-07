<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CulturalObject;
use App\Models\CulturalObjectRating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CulturalObjectRatingController extends Controller
{
    public function index(Request $request): View
    {
        $query = CulturalObject::withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->whereHas('ratings')
            ->with(['ratings' => fn ($q) => $q->with('user')->latest()->limit(50)]);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name->id', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%");
            });
        }

        if ($request->filled('rating_filter')) {
            // Note: the threshold is embedded as a literal (not a `?` placeholder binding) because
            // PDO binds PHP floats as PARAM_STR, and SQLite's type-affinity rules make any
            // REAL-vs-TEXT comparison (e.g. `AVG(rating) <= '3.0'`) always evaluate true, which
            // silently matched every row. The threshold here is a hardcoded constant, not user
            // input, so there is no SQL injection risk.
            $matchingIds = $request->rating_filter === 'low'
                ? CulturalObjectRating::select('cultural_object_id')->groupBy('cultural_object_id')->havingRaw('AVG(rating) <= 3.0')->pluck('cultural_object_id')
                : CulturalObjectRating::select('cultural_object_id')->groupBy('cultural_object_id')->havingRaw('AVG(rating) >= 4.0')->pluck('cultural_object_id');

            $query->whereIn('id', $matchingIds);
        }

        match ($request->get('sort', 'most_rated')) {
            'highest' => $query->orderByDesc('ratings_avg_rating'),
            'lowest' => $query->orderBy('ratings_avg_rating'),
            default => $query->orderByDesc('ratings_count'),
        };

        $objects = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return view('admin.cultural-object-ratings.index', compact('objects'));
    }

    public function destroy(CulturalObjectRating $rating): RedirectResponse
    {
        $rating->delete();

        return back()->with('status', 'Rating berhasil dihapus.');
    }
}
