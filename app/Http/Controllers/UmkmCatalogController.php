<?php

namespace App\Http\Controllers;

use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Services\UmkmRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class UmkmCatalogController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $activeTab = in_array($request->query('tab'), ['smart-route', 'direktori'], true)
            ? $request->query('tab')
            : 'smart-route';
        $q = trim((string) $request->query('q'));

        $categories = Cache::tags(['umkm'])->flexible("umkm_categories_array_{$locale}", [86400, 172800], function () {
            $models = UmkmProductCategory::all();

            return $models->map(function ($model) {
                $data = $model->toArray();
                $locale = app()->getLocale();
                foreach (['name', 'description'] as $field) {
                    if (isset($data[$field]) && \is_array($data[$field])) {
                        $data[$field] = $data[$field][$locale] ?? $data[$field][config('app.fallback_locale')] ?? reset($data[$field]) ?? '';
                    }
                }

                return $data;
            })->values()->toArray();
        });

        $umkmListQuery = UmkmProfile::active()
            ->with(['mapLocation', 'activeProducts.category', 'user']);

        if (mb_strlen($q) >= 2) {
            $likePattern = '%'.addcslashes($q, '%_').'%';

            $umkmListQuery->where(function ($query) use ($likePattern, $locale) {
                $query->whereRaw("business_name->>'$.\"{$locale}\"' LIKE ?", [$likePattern])
                    ->orWhereHas('activeProducts', function ($productQuery) use ($likePattern, $locale) {
                        $productQuery->whereRaw("name->>'$.\"{$locale}\"' LIKE ?", [$likePattern]);
                    });
            });
        }

        $umkmList = $umkmListQuery->paginate(12)->withQueryString();

        return view('user.umkm.index', compact('categories', 'umkmList', 'activeTab', 'q'));
    }

    public function recommend(Request $request, UmkmRecommendationService $recommendationService)
    {
        $request->validate([
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:umkm_product_categories,id',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        // Clear any previous multi-stop result so a new search never shows stale data.
        session()->forget(['multi_stop_recommendations', 'missing_categories']);

        $recommendedUmkm = $recommendationService->recommendForCategories($request->category_ids);

        if ($recommendedUmkm) {
            return redirect()->route('umkm.recommended', ['id' => $recommendedUmkm->id])
                ->with('success', __('Kami telah menemukan UMKM terbaik untuk pesanan Anda!'));
        }

        // Fallback: Multi-Stop Recommendation
        $lat = $request->filled('lat') ? (float) $request->input('lat') : null;
        $lng = $request->filled('lng') ? (float) $request->input('lng') : null;

        $multiStopData = $recommendationService->recommendMultipleForCategories($request->category_ids, $lat, $lng);

        if ($multiStopData && ! empty($multiStopData['route'])) {
            // ponytail: plain session (not flash) — flash data ages out after any
            // unrelated page view, which broke "Lihat Rute Belanja" for users who
            // browsed elsewhere before clicking it. Cleared explicitly on next search.
            session()->put('multi_stop_recommendations', $multiStopData['route']);

            if (! empty($multiStopData['missing'])) {
                // Fetch category names for the missing categories to display in UI
                $missingNames = UmkmProductCategory::whereIn('id', $multiStopData['missing'])->pluck('name')->toArray();
                session()->put('missing_categories', $missingNames);
            }

            // Flash a one-shot trigger so the modal only pops right after a search,
            // not on every later visit to /umkm (route data itself stays in session).
            return back()->with('show_multi_stop_modal', true);
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
