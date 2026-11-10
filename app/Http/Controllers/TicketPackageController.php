<?php

namespace App\Http\Controllers;

use App\Models\TicketRate;
use App\Models\VillageSettings;
use App\Services\TourPackageCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketPackageController extends Controller
{
    public function __construct(private readonly TourPackageCatalog $packageCatalog) {}

    /**
     * Display public educational tour packages & gate ticket rates.
     */
    public function index(Request $request): View
    {
        $rates = TicketRate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $villageSettings = VillageSettings::get();
        $whatsappNumber = config('services.tour_guide.whatsapp', env('TOUR_GUIDE_WHATSAPP_NUMBER', '6281234567890'));

        $packages = $this->packageCatalog->all();

        return view('user.packages-tickets.index', [
            'rates' => $rates,
            'packages' => $packages,
            'villageSettings' => $villageSettings,
            'whatsappNumber' => $whatsappNumber,
            'activeTab' => $request->query('tab', 'packages'),
        ]);
    }
}
