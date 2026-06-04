<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UmkmProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $categories = UmkmProductCategory::withCount('products')->orderBy('name')->get();

        return view('admin.umkm.categories', compact('categories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:umkm_product_categories,name'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'model_3d_file' => ['nullable', 'file', 'max:20480'],
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('categories', 'public');
        }

        if ($request->hasFile('model_3d_file')) {
            $validated['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
        }

        if ($request->hasFile('model_3d_usdz_file')) {
            $file = $request->file('model_3d_usdz_file');
            $filename = Str::random(40).'.usdz';
            $validated['model_3d_usdz_path'] = $file->storeAs('models', $filename, 'public');
        }

        UmkmProductCategory::create($validated);

        return redirect()->route('admin.umkm.categories')->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $category = UmkmProductCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:umkm_product_categories,name,'.$id],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'model_3d_file' => ['nullable', 'file', 'max:20480'],
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

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
            $file = $request->file('model_3d_usdz_file');
            $filename = Str::random(40).'.usdz';
            $validated['model_3d_usdz_path'] = $file->storeAs('models', $filename, 'public');
        }

        $category->update($validated);

        return redirect()->route('admin.umkm.categories')->with('success', 'Kategori produk berhasil diperbarui.');
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

        return redirect()->route('admin.umkm.categories')->with('success', 'Kategori produk berhasil dihapus.');
    }
}
