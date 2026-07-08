<?php

namespace App\Http\Controllers;

use App\Models\TourPackage;
use Illuminate\Support\Facades\Cache;

class TourPackageController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();
        $packages = Cache::tags(['packages'])->flexible("tour_packages_active_array_{$locale}", [86400, 172800], function () use ($locale) {
            return TourPackage::active()->get()->map(function ($package) use ($locale) {
                $data = $package->toArray();
                foreach (['name', 'description'] as $field) {
                    if (isset($data[$field]) && \is_array($data[$field])) {
                        $data[$field] = $data[$field][$locale] ?? $data[$field][config('app.fallback_locale')] ?? reset($data[$field]) ?? '';
                    }
                }

                return $data;
            })->values()->toArray();
        });

        // 'type' may be missing from caches written before the ticket/package split
        $tickets = array_values(array_filter($packages, fn ($p) => ($p['type'] ?? 'package') === 'ticket'));
        $tourPackages = array_values(array_filter($packages, fn ($p) => ($p['type'] ?? 'package') !== 'ticket'));

        return view('user.packages.index', compact('tickets', 'tourPackages'));
    }

    public function show($id)
    {
        $package = TourPackage::findOrFail($id);

        return view('user.packages.show', compact('package'));
    }
}
