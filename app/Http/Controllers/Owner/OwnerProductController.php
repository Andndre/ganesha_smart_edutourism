<?php

namespace App\Http\Controllers\Owner;

use App\Http\Requests\Owner\OwnerProductRequest;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerProductController extends BaseOwnerController
{
    public function index(Request $request): View
    {
        if (! $this->profile) {
            return view('owner.products', [
                'products' => collect(),
                'categories' => collect(),
                'noProfile' => true,
            ]);
        }

        $query = UmkmProduct::where('umkm_profile_id', $this->profile->id)->with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('category', function ($q) use ($search) {
                $q->where('name->en', 'like', '%'.$search.'%')
                    ->orWhere('name->id', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('category') && $request->category !== 'Semua Kategori') {
            $categoryName = $request->category;
            $query->whereHas('category', function ($q) use ($categoryName) {
                $q->where('name', $categoryName);
            });
        }

        $products = $query->paginate(10)->withQueryString();
        $categories = UmkmProductCategory::orderBy('name->'.app()->getLocale())->get();

        return view('owner.products', compact('products', 'categories') + ['noProfile' => false]);
    }

    public function store(OwnerProductRequest $request): RedirectResponse
    {
        $profile = $this->requireProfile('owner.products');

        $validated = $request->validated();
        $validated['umkm_profile_id'] = $profile->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        UmkmProduct::create($validated);

        return redirect()->route('owner.products')->with('success', __('Produk berhasil ditambahkan.'));
    }

    public function update(OwnerProductRequest $request, int $id): RedirectResponse
    {
        $profile = $this->requireProfile('owner.products');

        $product = UmkmProduct::where('umkm_profile_id', $profile->id)->findOrFail($id);

        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        $product->update($validated);

        return redirect()->route('owner.products')->with('success', __('Produk berhasil diperbarui.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $profile = $this->requireProfile('owner.products');

        $product = UmkmProduct::where('umkm_profile_id', $profile->id)->findOrFail($id);
        $product->delete();

        return redirect()->route('owner.products')->with('success', __('Produk berhasil dihapus.'));
    }
}
