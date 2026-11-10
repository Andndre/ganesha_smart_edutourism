<?php

namespace App\Http\Controllers;

use App\Models\CapacityZone;
use App\Models\CulturalObject;
use App\Models\TourRoute;
use App\Models\TraditionalHouse;
use App\Models\UmkmProfile;
use App\Models\WeatherReport;
use App\Services\TourPackageCatalog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly TourPackageCatalog $packageCatalog) {}

    /**
     * Display the tourist homepage dashboard.
     */
    public function index(): View
    {
        $zones = Cache::tags(['capacity'])->flexible('capacity_zones_active_array', [60, 300], function () {
            return CapacityZone::where('is_active', true)->get()->append('occupancy_percentage')->toArray();
        });
        $zones = CapacityZone::withLiveCounts($zones);
        $masterZone = collect($zones)->firstWhere('zone_identifier', 'desa_penglipuran');

        $totalCurrent = collect($zones)->sum('current_count');
        $totalMax = collect($zones)->sum('max_capacity');
        $densityPercent = $totalMax > 0 ? ($totalCurrent / $totalMax) * 100 : 0;

        $warningThreshold = $masterZone['warning_threshold'] ?? 60;
        $criticalThreshold = $masterZone['critical_threshold'] ?? 80;
        $densityStatus = CapacityZone::statusFor($densityPercent, $warningThreshold, $criticalThreshold);

        $densityMap = [
            'safe' => ['class' => 'text-green-500', 'bg' => 'bg-green-50'],
            'medium' => ['class' => 'text-warning', 'bg' => 'bg-orange-50'],
            'full' => ['class' => 'text-red-500', 'bg' => 'bg-red-50'],
        ][$densityStatus['key']];

        $densityText = $densityStatus['label'].' ('.round($densityPercent).'%)';
        $densityClass = $densityMap['class'];
        $densityBg = $densityMap['bg'];

        // Fetch cached weather, fallback to first-time update if empty
        $weather = WeatherReport::cached();
        if (! $weather) {
            try {
                Artisan::call('app:update-weather');
                $weather = WeatherReport::cached();
            } catch (\Exception $e) {
                // Ignored fallback
            }
        }

        // ponytail: rotasi acak yang di-cache per jam, bukan UmkmRecommendationService —
        // service itu butuh categoryIds dan menulis last_recommended_at, yang tidak pantas
        // dijalankan pada setiap kunjungan beranda.
        $locale = app()->getLocale();
        $featuredUmkm = Cache::tags(['explore', 'umkm'])->flexible(
            "home_featured_umkm_cards_array_v2_{$locale}",
            [600, 1800],
            fn () => UmkmProfile::active()
                ->with([
                    'activeProducts.category',
                    'activeProducts.activeVariants',
                    'traditionalHouse:id,number',
                ])
                ->inRandomOrder()
                ->limit(8)
                ->get()
                ->map(function (UmkmProfile $profile): array {
                    $product = $profile->activeProducts->first();
                    $priceRange = $product?->price_range;

                    return [
                        'id' => $profile->id,
                        'business_name' => $profile->getTranslations('business_name'),
                        'owner_name' => $profile->owner_name,
                        'image_path' => $profile->image_path,
                        'product_name' => $product?->display_name,
                        'product_image_path' => $product?->display_image,
                        'product_price' => $priceRange['min'] ?? null,
                        'house_number' => $profile->traditionalHouse?->number,
                    ];
                })
                ->all()
        );

        $houseCount = TraditionalHouse::published()->count();
        $culturalCount = CulturalObject::roots()->count();
        $routeCount = TourRoute::where('is_active', true)->count();
        $featuredCultural = CulturalObject::roots()->with('children')->first();
        $featuredPackages = $this->packageCatalog->homepageSlides();
        $umkmCount = UmkmProfile::active()->count();

        return view('home', \compact(
            'densityText', 'densityClass', 'densityBg', 'weather',
            'totalCurrent', 'totalMax', 'densityStatus', 'densityPercent', 'warningThreshold', 'criticalThreshold',
            'featuredUmkm', 'houseCount', 'culturalCount', 'routeCount', 'featuredCultural',
            'featuredPackages', 'umkmCount'
        ));
    }
}
