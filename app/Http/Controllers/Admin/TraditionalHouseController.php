<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TraditionalHouseRequest;
use App\Models\CulturalObject;
use App\Models\TraditionalHouse;
use App\Models\UmkmProfile;
use App\Models\User;
use App\Services\TraditionalHouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TraditionalHouseController extends Controller
{
    public function index(Request $request): View
    {
        $query = TraditionalHouse::with('mapLocation')->withCount('umkmProfiles');
        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(function ($query) use ($search): void {
                $query->where('head_name', 'like', '%'.$search.'%');
                if (ctype_digit($search)) {
                    $query->orWhere('number', (int) $search);
                }
            });
        }

        $houses = $query->orderBy('number')->paginate(15)->withQueryString();

        return view('admin.houses.index', compact('houses'));
    }

    public function create(): View
    {
        return $this->form(null);
    }

    public function edit(TraditionalHouse $house): View
    {
        return $this->form($house);
    }

    public function store(TraditionalHouseRequest $request): RedirectResponse
    {
        $house = app(TraditionalHouseService::class)->save($request, new TraditionalHouse);

        return redirect()->route('admin.houses.edit', $house)->with('success', 'Rumah adat berhasil disimpan.');
    }

    public function update(TraditionalHouseRequest $request, TraditionalHouse $house): RedirectResponse
    {
        app(TraditionalHouseService::class)->save($request, $house);

        return redirect()->route('admin.houses.edit', $house)->with('success', 'Rumah adat berhasil diperbarui.');
    }

    public function destroy(TraditionalHouse $house): RedirectResponse
    {
        $paths = array_filter([$house->cover_image_path, ...($house->gallery ?? [])]);
        $house->delete();
        Storage::disk('public')->delete($paths);

        return redirect()->route('admin.houses.index')->with('success', 'Rumah adat berhasil dihapus.');
    }

    private function form(?TraditionalHouse $house): View
    {
        $house?->load(['mapLocation', 'umkmProfiles', 'culturalObjects']);
        $profiles = UmkmProfile::orderBy('owner_name')->get();
        $objects = CulturalObject::orderBy('name->'.app()->getLocale())->get();

        $owners = User::where('role', 'umkm_owner')->orderBy('name')->get();

        return view('admin.houses.form', compact('house', 'profiles', 'objects', 'owners'));
    }
}
