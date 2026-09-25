<?php

namespace App\Http\Controllers\Owner;

use App\Http\Requests\Owner\OwnerProductRequest;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OwnerProductController extends BaseOwnerController
{
    public function index(Request $request): View
    {
        if (! $this->profile) {
            return view('owner.products', [
                'profile' => null,
                'products' => collect(),
                'categories' => collect(),
                'noProfile' => true,
            ]);
        }

        $query = UmkmProduct::where('umkm_profile_id', $this->profile->id)->with(['category', 'variants']);

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

        return view('owner.products', compact('products', 'categories') + [
            'profile' => $this->profile,
            'noProfile' => false,
        ]);
    }

    public function store(OwnerProductRequest $request): RedirectResponse
    {
        $profile = $this->requireProfile('owner.products');

        $validated = $request->validated();
        $validated['umkm_profile_id'] = $profile->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        $validated['images'] = $this->storeImages($request);
        $variants = $validated['variants'] ?? [];
        unset($validated['variants']);

        $product = UmkmProduct::create($validated);
        $this->syncVariants($product, $variants);

        return redirect()->route('owner.products')->with('success', __('Produk berhasil ditambahkan.'));
    }

    public function update(OwnerProductRequest $request, int $id): RedirectResponse
    {
        $profile = $this->requireProfile('owner.products');

        $product = UmkmProduct::where('umkm_profile_id', $profile->id)->findOrFail($id);

        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('images')) {
            foreach ($product->images ?? [] as $image) {
                Storage::disk('public')->delete($image);
            }
            $validated['images'] = $this->storeImages($request);
        }
        $variants = $validated['variants'] ?? [];
        unset($validated['variants']);

        $product->update($validated);
        $this->syncVariants($product, $variants);

        return redirect()->route('owner.products')->with('success', __('Produk berhasil diperbarui.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $profile = $this->requireProfile('owner.products');

        $product = UmkmProduct::where('umkm_profile_id', $profile->id)->findOrFail($id);
        $product->delete();

        return redirect()->route('owner.products')->with('success', __('Produk berhasil dihapus.'));
    }

    private function storeImages(OwnerProductRequest $request): array
    {
        return collect($request->file('images', []))
            ->map(fn ($file) => $file->store('umkm_products', 'public'))
            ->all();
    }

    /** @param array<int, array<string, mixed>> $variants */
    private function syncVariants(UmkmProduct $product, array $variants): void
    {
        $product->variants()->delete();
        foreach ($variants as $index => $variant) {
            $product->variants()->create([
                'label' => $variant['label'],
                'price' => $variant['price'],
                'stock' => $variant['stock'] ?? null,
                'is_active' => (bool) ($variant['is_active'] ?? false),
                'sort_order' => $variant['sort_order'] ?? $index,
            ]);
        }
    }
}
