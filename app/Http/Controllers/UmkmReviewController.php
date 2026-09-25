<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUmkmReviewRequest;
use App\Models\UmkmProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class UmkmReviewController extends Controller
{
    public function store(StoreUmkmReviewRequest $request, UmkmProfile $umkm): RedirectResponse
    {
        $umkm->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated(),
        );

        Cache::tags(['umkm'])->flush();

        return back()->with('success', __('Ulasan Anda telah disimpan.'));
    }
}
