<?php

namespace App\Http\Controllers\Owner;

use App\Http\Concerns\NormalizesMultilingualInput;
use App\Models\UmkmProductCategory;
use App\Models\User;
use App\Notifications\NewUmkmCategoryCreated;
use App\Notifications\UmkmCategoryEditRequested;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class OwnerCategoryController extends BaseOwnerController
{
    use NormalizesMultilingualInput;

    public function store(Request $request): JsonResponse
    {
        $this->requireProfile('owner.products');

        $this->normalizeLocaleFields(['name', 'description'], $request);

        $validated = $request->validate($this->categoryRules());

        $validated['slug'] = (new UmkmProductCategory)->generateSlug(slugFromTranslatable($validated['name']));

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('categories', 'public');
        }
        if ($request->hasFile('model_3d_file')) {
            $validated['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
        }
        if ($request->hasFile('model_3d_usdz_file')) {
            $validated['model_3d_usdz_path'] = $request->file('model_3d_usdz_file')->store('models', 'public');
        }

        $category = UmkmProductCategory::create($validated);

        Notification::send(User::admins()->get(), new NewUmkmCategoryCreated($category, $this->user));

        return response()->json([
            'id' => $category->id,
            'name' => translateValue($category->name),
            'name_translations' => $category->getTranslations('name'),
        ]);
    }

    public function update(Request $request, UmkmProductCategory $category): JsonResponse
    {
        $profile = $this->requireProfile('owner.products');

        abort_unless($category->editableByOwner($profile), 403, __('Kategori ini berisi produk milik toko lain. Silakan ajukan permintaan edit ke admin.'));

        $this->normalizeLocaleFields(['name', 'description'], $request);

        $validated = $request->validate($this->categoryRules());

        $validated['slug'] = $category->generateSlug(slugFromTranslatable($validated['name']));

        if ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('categories', 'public');
        }
        if ($request->hasFile('model_3d_file')) {
            if ($category->model_3d_path) {
                Storage::disk('public')->delete($category->model_3d_path);
            }
            $validated['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
        }
        if ($request->hasFile('model_3d_usdz_file')) {
            if ($category->model_3d_usdz_path) {
                Storage::disk('public')->delete($category->model_3d_usdz_path);
            }
            $validated['model_3d_usdz_path'] = $request->file('model_3d_usdz_file')->store('models', 'public');
        }

        $category->update($validated);

        return response()->json([
            'id' => $category->id,
            'name' => translateValue($category->name),
        ]);
    }

    public function requestEdit(Request $request, UmkmProductCategory $category): JsonResponse
    {
        $this->requireProfile('owner.products');

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        Notification::send(
            User::admins()->get(),
            new UmkmCategoryEditRequested($category, $this->user, $validated['note'])
        );

        return response()->json(['ok' => true]);
    }

    private function categoryRules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'model_3d_file' => ['nullable', 'file', 'mimes:glb,gltf', 'max:20480'],
            'model_3d_usdz_file' => ['nullable', 'file', 'mimetypes:model/vnd.usdz+zip,application/octet-stream', 'max:51200'],
        ];
    }
}
