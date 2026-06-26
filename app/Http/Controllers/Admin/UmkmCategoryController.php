<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UmkmCategoryRequest;
use App\Models\UmkmProductCategory;
use App\Services\TusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UmkmCategoryController extends Controller
{
    /**
     * Display a listing of the product categories.
     */
    public function index(): View
    {
        $categories = UmkmProductCategory::withCount('products')->orderBy('name->'.app()->getLocale())->get();

        return view('admin.umkm.categories', compact('categories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(UmkmCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $defaultLocale = config('app.fallback_locale', 'en');
        $slugValue = $validated['name'][$defaultLocale] ?? $validated['name']['en'] ?? reset($validated['name']);
        $validated['slug'] = (new UmkmProductCategory)->generateSlug($slugValue);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('categories', 'public');
        }

        if ($tmpUuid = $request->input('tmp_model_3d_path')) {
            $validated['model_3d_path'] = TusService::moveFromTemp($tmpUuid, 'models');
        } elseif ($request->hasFile('model_3d_file')) {
            $validated['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
        }

        if ($tmpUuid = $request->input('tmp_model_3d_usdz_path')) {
            $validated['model_3d_usdz_path'] = TusService::moveFromTemp($tmpUuid, 'models', Str::random(40).'.usdz');
        } elseif ($request->hasFile('model_3d_usdz_file')) {
            $file = $request->file('model_3d_usdz_file');
            $filename = Str::random(40).'.usdz';
            $validated['model_3d_usdz_path'] = $file->storeAs('models', $filename, 'public');
        }

        UmkmProductCategory::create($validated);

        return redirect()->route('admin.umkm.categories')->with('success', __('Kategori produk berhasil ditambahkan.'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UmkmCategoryRequest $request, int $id): RedirectResponse
    {
        $category = UmkmProductCategory::findOrFail($id);

        $validated = $request->validated();

        $defaultLocale = config('app.fallback_locale', 'en');
        $slugValue = $validated['name'][$defaultLocale] ?? $validated['name']['en'] ?? reset($validated['name']);
        $validated['slug'] = $category->generateSlug($slugValue);

        if ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('categories', 'public');
        }

        if ($tmpUuid = $request->input('tmp_model_3d_path')) {
            if ($category->model_3d_path) {
                Storage::disk('public')->delete($category->model_3d_path);
            }
            $validated['model_3d_path'] = TusService::moveFromTemp($tmpUuid, 'models');
        } elseif ($request->hasFile('model_3d_file')) {
            if ($category->model_3d_path) {
                Storage::disk('public')->delete($category->model_3d_path);
            }
            $validated['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
        }

        if ($tmpUuid = $request->input('tmp_model_3d_usdz_path')) {
            if ($category->model_3d_usdz_path) {
                Storage::disk('public')->delete($category->model_3d_usdz_path);
            }
            $validated['model_3d_usdz_path'] = TusService::moveFromTemp($tmpUuid, 'models', Str::random(40).'.usdz');
        } elseif ($request->hasFile('model_3d_usdz_file')) {
            if ($category->model_3d_usdz_path) {
                Storage::disk('public')->delete($category->model_3d_usdz_path);
            }
            $file = $request->file('model_3d_usdz_file');
            $filename = Str::random(40).'.usdz';
            $validated['model_3d_usdz_path'] = $file->storeAs('models', $filename, 'public');
        }

        $category->update($validated);

        return redirect()->route('admin.umkm.categories')->with('success', __('Kategori produk berhasil diperbarui.'));
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $category = UmkmProductCategory::findOrFail($id);

        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }

        if ($category->model_3d_path) {
            Storage::disk('public')->delete($category->model_3d_path);
        }

        if ($category->model_3d_usdz_path) {
            Storage::disk('public')->delete($category->model_3d_usdz_path);
        }

        $category->delete();

        return redirect()->route('admin.umkm.categories')->with('success', __('Kategori produk berhasil dihapus.'));
    }
}
