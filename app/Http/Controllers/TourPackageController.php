<?php

namespace App\Http\Controllers;

use App\Models\TourPackage;

class TourPackageController extends Controller
{
    public function index()
    {
        $packages = TourPackage::active()->get();

        return view('user.packages.index', compact('packages'));
    }

    public function show($id)
    {
        $package = TourPackage::findOrFail($id);

        return view('user.packages.show', compact('package'));
    }
}
