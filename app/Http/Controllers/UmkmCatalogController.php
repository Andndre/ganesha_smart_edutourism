<?php

namespace App\Http\Controllers;

use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Services\UmkmRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UmkmCatalogController extends Controller
{
    public function index()
    {
        $categories = UmkmProductCategory::all();

        return view('user.umkm.index', compact('categories'));
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
                ->with('success', 'Kami telah menemukan UMKM terbaik untuk pesanan Anda!');
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

        return back()->with('error', 'Maaf, tidak ada UMKM yang saat ini memiliki stok untuk barang pilihan Anda.');
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
}
