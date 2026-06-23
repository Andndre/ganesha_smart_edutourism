<?php

namespace App\Http\Controllers;

use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Services\UmkmRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class UmkmCatalogController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();
        $categories = Cache::tags(['umkm'])->flexible("umkm_categories_array_{$locale}", [86400, 172800], function () use ($locale) {
            $models = UmkmProductCategory::all();
            return $models->map(function ($model) {
                $data = $model->toArray();
                $locale = app()->getLocale();
                foreach (['name', 'description'] as $field) {
                    if (isset($data[$field]) && is_array($data[$field])) {
                        $data[$field] = $data[$field][$locale] ?? $data[$field][config('app.fallback_locale')] ?? reset($data[$field]) ?? '';
                    }
                }
                return $data;
            })->values()->toArray();
        });

        $umkmList = UmkmProfile::active()
            ->with(['mapLocation', 'activeProducts.category'])
            ->paginate(12);

        if (session()->has('multi_stop_recommendations')) {
            session()->keep(['multi_stop_recommendations', 'missing_categories']);
        }

        return view('user.umkm.index', compact('categories', 'umkmList'));
    }

    public function recommend(Request $request, UmkmRecommendationService $recommendationService)
    {
        $request->validate([
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:umkm_product_categories,id',
        ]);

        $recommendedUmkm = $recommendationService->recommendForCategories($request->category_ids);

        if ($recommendedUmkm) {
            return redirect()->route('umkm.recommended', ['id' => $recommendedUmkm->id])
                ->with('success', __('Kami telah menemukan UMKM terbaik untuk pesanan Anda!'));
        }

        // Fallback: Multi-Stop Recommendation
        $multiStopData = $recommendationService->recommendMultipleForCategories($request->category_ids);

        if ($multiStopData && ! empty($multiStopData['route'])) {
            $redirect = back()->with('multi_stop_recommendations', $multiStopData['route']);

            if (! empty($multiStopData['missing'])) {
                // Fetch category names for the missing categories to display in UI
                $missingNames = UmkmProductCategory::whereIn('id', $multiStopData['missing'])->pluck('name')->toArray();
                $redirect->with('missing_categories', $missingNames);
            }

            return $redirect;
        }

        return back()->with('error', __('Maaf, tidak ada UMKM yang saat ini memiliki stok untuk barang pilihan Anda.'));
    }

    public function recommended(Request $request, $id)
    {
        $umkm = UmkmProfile::with(['user', 'activeProducts.category', 'mapLocation'])->findOrFail($id);

        return view('user.umkm.recommended', compact('umkm'));
    }

    public function multiRecommended(Request $request)
    {
        $route = session('multi_stop_recommendations');

        if (! $route) {
            return redirect()->route('umkm');
        }

        // Keep session for refresh
        Session::reflash();

        return view('user.umkm.multi_recommended', compact('route'));
    }

    public function show($id): View
    {
        $umkm = UmkmProfile::with(['user', 'activeProducts.category', 'mapLocation'])->findOrFail($id);

        return view('user.umkm.show', compact('umkm'));
    }

    public function search(Request $request): JsonResponse
    {
        $q = $request->query('q', '');

        if (mb_strlen($q) < 2) {
            return response()->json(['umkms' => [], 'products' => [], 'categories' => []]);
        }

        $locale = app()->getLocale();
        $query = addcslashes($q, '%_');
        $likePattern = "%{$query}%";

        $umkms = UmkmProfile::active()
            ->whereRaw("business_name->>'$.\"{$locale}\"' LIKE ?", [$likePattern])
            ->limit(10)
            ->get()
            ->map(function ($umkm) use ($locale) {
                $data = $umkm->toArray();
                $businessName = $data['business_name'][$locale]
                    ?? $data['business_name'][config('app.fallback_locale')]
                    ?? '';

                return [
                    'id' => $umkm->id,
                    'business_name' => $businessName,
                    'slug' => $umkm->slug,
                    'rating' => $umkm->rating,
                    'image_path' => $umkm->mapLocation?->arModel?->ar_marker_id,
                ];
            });

        $products = UmkmProduct::active()->inStock()
            ->with('umkmProfile')
            ->whereRaw("name->>'$.\"{$locale}\"' LIKE ?", [$likePattern])
            ->limit(5)
            ->get()
            ->map(function ($product) use ($locale) {
                $data = $product->toArray();
                $name = $data['name'][$locale]
                    ?? $data['name'][config('app.fallback_locale')]
                    ?? '';
                $profileData = $product->umkmProfile?->toArray() ?? [];
                $businessName = $profileData['business_name'][$locale]
                    ?? $profileData['business_name'][config('app.fallback_locale')]
                    ?? '';

                return [
                    'id' => $product->id,
                    'name' => $name,
                    'price' => $product->price,
                    'umkm_profile_id' => $product->umkm_profile_id,
                    'umkm_business_name' => $businessName,
                ];
            });

        $categories = UmkmProductCategory::whereRaw("name->>'$.\"{$locale}\"' LIKE ?", [$likePattern])
            ->limit(5)
            ->get()
            ->map(function ($category) use ($locale) {
                $data = $category->toArray();
                $name = $data['name'][$locale]
                    ?? $data['name'][config('app.fallback_locale')]
                    ?? '';

                return [
                    'id' => $category->id,
                    'name' => $name,
                    'slug' => $category->slug,
                ];
            });

        return response()->json([
            'umkms' => $umkms,
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
