<?php

namespace App\Http\Controllers;

use App\Models\TourPackage;
use Illuminate\Support\Facades\Cache;

class TourPackageController extends Controller
{
    public function index()
    {
        $packages = Cache::remember('tour_packages_active_array', 86400, function () {
            return TourPackage::active()->get()->toArray();
        });

        return view('user.packages.index', compact('packages'));
    }

    public function show($id)
    {
        $package = TourPackage::findOrFail($id);

        return view('user.packages.show', compact('package'));
    }
}
