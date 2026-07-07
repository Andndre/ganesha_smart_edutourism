<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CulturalObject;
use Illuminate\View\View;

class CulturalObjectRatingController extends Controller
{
    public function index(): View
    {
        $objects = CulturalObject::withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->whereHas('ratings')
            ->with(['ratings' => fn ($q) => $q->with('user')->latest()])
            ->orderByDesc('ratings_count')
            ->paginate(10);

        return view('admin.cultural-object-ratings.index', compact('objects'));
    }
}
