<?php

namespace App\Http\Controllers;

use App\Models\TraditionalHouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TraditionalHouseController extends Controller
{
    public function index(Request $request): View
    {
        $query = TraditionalHouse::published()->with('mapLocation')->withCount(['umkmProfiles' => fn ($query) => $query->active()]);
        $search = trim((string) $request->query('q', ''));

        if ($search !== '' && ctype_digit($search)) {
            $query->where('number', 'like', $search.'%');
        } elseif ($search !== '') {
            $query->whereRaw('1 = 0');
        }

        $houses = $query->orderBy('number')->paginate(12)->withQueryString();
        $houseCount = TraditionalHouse::published()->count();

        return view('user.houses.index', compact('houses', 'houseCount', 'search'));
    }

    public function show(int $number): View
    {
        $house = TraditionalHouse::published()
            ->with(['mapLocation', 'umkmProfiles' => fn ($query) => $query->active()->with('activeProducts.category', 'activeProducts.activeVariants'), 'culturalObjects.arModel'])
            ->where('number', $number)
            ->firstOrFail();

        return view('user.houses.show', compact('house'));
    }
}
