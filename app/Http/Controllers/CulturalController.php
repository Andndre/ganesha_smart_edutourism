<?php

namespace App\Http\Controllers;

use App\Models\CulturalObject;
use Illuminate\View\View;

class CulturalController extends Controller
{
    /**
     * Display a listing of the cultural objects.
     */
    public function index(): View
    {
        $objects = CulturalObject::all();

        return view('user.cultural.index', compact('objects'));
    }

    /**
     * Display the specified cultural object with its stories.
     */
    public function show(int $id): View
    {
        $object = CulturalObject::with('stories')->findOrFail($id);

        return view('user.cultural.show', compact('object'));
    }
}
