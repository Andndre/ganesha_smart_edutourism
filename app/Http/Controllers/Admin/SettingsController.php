<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateVillageSettingsRequest;
use App\Models\CulturalObject;
use App\Models\VillageSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => VillageSettings::get(),
            'culturalRoots' => CulturalObject::roots()->withCount('children')->orderBy('name->id')->get(),
        ]);
    }

    public function update(UpdateVillageSettingsRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $validated = $request->validated();
            $selected = array_filter($request->safe()->only(['house_cultural_object_id', 'featured_cultural_object_id']));
            $roots = CulturalObject::roots()->whereKey(array_values($selected))->orderBy('id')->lockForUpdate()->pluck('id');
            foreach ($selected as $field => $id) {
                if (! $roots->contains($id)) {
                    throw ValidationException::withMessages([$field => 'Objek ini sudah berubah. Pilih kembali objek budaya induk yang tersedia.']);
                }
            }
            VillageSettings::get()->update($validated);
        });

        return back()->with('success', 'Pengaturan desa berhasil diperbarui.');
    }
}
