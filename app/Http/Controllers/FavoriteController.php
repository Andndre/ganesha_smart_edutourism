<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $items = auth()->user()->favoriteItems();

        return view('user.profile.favorites', compact('items'));
    }

    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'favoritable_type' => 'required|string',
            'favoritable_id' => 'required|integer',
        ]);

        $model = $validated['favoritable_type']::findOrFail($validated['favoritable_id']);

        if (auth()->user()->hasFavorited($model)) {
            auth()->user()->favorites()
                ->where('favoritable_type', $model->getMorphClass())
                ->where('favoritable_id', $model->id)
                ->delete();

            return response()->json(['status' => 'removed']);
        }

        auth()->user()->favorites()->create([
            'favoritable_type' => $model->getMorphClass(),
            'favoritable_id' => $model->id,
        ]);

        return response()->json(['status' => 'added']);
    }
}
