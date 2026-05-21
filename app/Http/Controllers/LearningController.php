<?php

namespace App\Http\Controllers;

use App\Models\LearningModule;
use App\Models\LearningContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LearningController extends Controller
{
    /**
     * Display a listing of active learning modules.
     */
    public function index()
    {
        $modules = LearningModule::active()->orderBy('order')->get();
        return view('pages.learning.index', compact('modules'));
    }

    /**
     * Show a specific learning module and its contents.
     */
    public function show(string $slug)
    {
        $module = LearningModule::where('slug', $slug)->with(['contents' => function ($q) {
            $q->ordered();
        }])->firstOrFail();

        return view('pages.learning.show', compact('module'));
    }
}
