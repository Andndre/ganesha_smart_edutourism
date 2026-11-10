<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TraditionalHouseRequest;
use App\Models\CulturalObject;
use App\Models\TraditionalHouse;
use App\Models\UmkmProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $house = $this->save($request, new TraditionalHouse);

        return redirect()->route('admin.houses.edit', $house)->with('success', 'House saved.');
    }

    public function update(TraditionalHouseRequest $request, TraditionalHouse $house): RedirectResponse
    {
        $this->save($request, $house);

        return redirect()->route('admin.houses.edit', $house)->with('success', 'House updated.');
    }

    public function destroy(TraditionalHouse $house): RedirectResponse
    {
        $paths = array_filter([$house->cover_image_path, ...($house->gallery ?? [])]);
        $house->delete();
        Storage::disk('public')->delete($paths);

        return redirect()->route('admin.houses.index')->with('success', 'House deleted.');
    }

    private function form(?TraditionalHouse $house): View
    {
        $house?->load(['mapLocation', 'umkmProfiles', 'culturalObjects']);
        $profiles = UmkmProfile::orderBy('owner_name')->get();
        $objects = CulturalObject::orderBy('name->'.app()->getLocale())->get();

        return view('admin.houses.form', compact('house', 'profiles', 'objects'));
    }

    private function save(TraditionalHouseRequest $request, TraditionalHouse $house): TraditionalHouse
    {
        $data = $request->validated();
        $umkmIds = $data['umkm_profile_ids'] ?? [];
        $objectIds = $data['cultural_object_ids'] ?? [];
        unset($data['umkm_profile_ids'], $data['cultural_object_ids'], $data['latitude'], $data['longitude'], $data['cover_image'], $data['gallery_images']);

        $data['is_open_to_visitors'] = $request->boolean('is_open_to_visitors');
        $data['is_published'] = $request->boolean('is_published');
        $data['ar_marker_id'] = ($data['ar_marker_id'] ?? null) ?: ($house->ar_marker_id ?: 'HOUSE_'.Str::ulid());
        $data['description'] ??= ['id' => '', 'en' => ''];

        $oldCover = $house->cover_image_path;
        if ($request->hasFile('cover_image')) {
            $newCover = $request->file('cover_image')->store('traditional_houses', 'public');
            $data['cover_image_path'] = $newCover;
        }

        $gallery = $house->gallery ?? [];
        foreach ($request->file('gallery_images', []) as $image) {
            $gallery[] = $image->store('traditional_houses', 'public');
        }
        $data['gallery'] = $gallery;

        $savedHouse = DB::transaction(function () use ($house, $data, $request, $umkmIds, $objectIds): TraditionalHouse {
            $house->fill($data);
            $house->save();

            if ($request->filled('latitude') && $request->filled('longitude')) {
                $house->syncMapLocation([
                    'category' => 'cultural',
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                    'is_accessible' => true,
                ], true);
            }

            UmkmProfile::where('traditional_house_id', $house->id)->whereNotIn('id', $umkmIds)->update(['traditional_house_id' => null]);
            UmkmProfile::whereIn('id', $umkmIds)->update(['traditional_house_id' => $house->id]);
            $house->culturalObjects()->sync($objectIds);

            return $house;
        });

        if ($request->hasFile('cover_image') && $oldCover) {
            Storage::disk('public')->delete($oldCover);
        }

        return $savedHouse;
    }
}
