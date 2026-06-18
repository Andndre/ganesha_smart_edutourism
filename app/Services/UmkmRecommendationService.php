<?php

namespace App\Services;

use App\Models\UmkmProfile;

class UmkmRecommendationService
{
    /**
     * Get the fairest UMKM recommendation for a list of desired product categories.
     *
     * @param  array<int>  $categoryIds
     */
    public function recommendForCategories(array $categoryIds): ?UmkmProfile
    {
        if (empty($categoryIds)) {
            return null;
        }

        $query = UmkmProfile::active();

        // Ensure the UMKM has an active, in-stock product for EVERY requested category
        foreach ($categoryIds as $categoryId) {
            $query->whereHas('products', function ($q) use ($categoryId) {
                $q->active()
                    ->where('umkm_product_category_id', $categoryId)
                    ->where(function ($subQ) {
                        $subQ->whereNull('stock')->orWhere('stock', '>', 0);
                    });
            });
        }

        // Order by recommendation_count (lowest first) for fair distribution.
        // We use inRandomOrder as a secondary sort to randomly pick among UMKMs with the same count.
        $umkm = $query->orderBy('recommendation_count', 'asc')
            ->inRandomOrder()
            ->first();

        if ($umkm) {
            // Increment the recommendation count to ensure fair rotation next time
            $umkm->increment('recommendation_count');
        }

        return $umkm;
    }

    /**
     * Get a multi-stop UMKM recommendation using Greedy Set Cover algorithm.
     *
     * @param  array<int>  $categoryIds
     * @return array<int, array>|null An array of stops: [['umkm' => UmkmProfile, 'categories' => [id1, id2]]]
     */
    public function recommendMultipleForCategories(array $categoryIds): ?array
    {
        if (empty($categoryIds)) {
            return null;
        }

        $remainingCategories = collect($categoryIds);
        $route = [];
        $lastUmkm = null;

        // Pre-fetch all active UMKMs with active, in-stock products
        $allUmkms = UmkmProfile::active()
            ->withCoordinates()
            ->with(['products' => function ($q) {
                $q->active()->inStock();
            }, 'mapLocation'])
            ->get();

        if ($allUmkms->isEmpty()) {
            return null;
        }

        while ($remainingCategories->isNotEmpty()) {
            $bestUmkm = null;
            $bestCover = collect();
            $bestScore = -1; // Higher is better

            foreach ($allUmkms as $umkm) {
                if (in_array($umkm->id, array_column($route, 'umkm_id'))) {
                    continue; // Skip already visited UMKMs
                }

                // Which of the remaining categories does this UMKM have?
                $umkmCategories = $umkm->products->pluck('umkm_product_category_id')->unique();
                $cover = $remainingCategories->intersect($umkmCategories);

                if ($cover->isEmpty()) {
                    continue;
                }

                // Calculate score: number of covered categories is primary.
                // Distance from last UMKM is a secondary penalty.
                // Recommendation count is a tertiary penalty (for fairness).
                $coverCount = $cover->count();
                $distancePenalty = 0;
                $fairnessPenalty = ($umkm->recommendation_count * 0.001);

                if ($lastUmkm && $lastUmkm->mapLocation && $umkm->mapLocation) {
                    $distance = $this->calculateDistance(
                        $lastUmkm->mapLocation->latitude,
                        $lastUmkm->mapLocation->longitude,
                        $umkm->mapLocation->latitude,
                        $umkm->mapLocation->longitude
                    );
                    $distancePenalty = $distance * 0.1; // Weight distance
                }

                $score = $coverCount - $distancePenalty - $fairnessPenalty;

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestUmkm = $umkm;
                    $bestCover = $cover;
                }
            }

            // If we couldn't find any UMKM that has ANY of the remaining categories, stop.
            if (! $bestUmkm) {
                break;
            }

            // Add to route
            $route[] = [
                'umkm_id' => $bestUmkm->id,
                'umkm' => $bestUmkm,
                'categories' => $bestCover->values()->all(),
            ];

            // Remove covered categories
            $remainingCategories = $remainingCategories->diff($bestCover);
            $lastUmkm = $bestUmkm;

            // Increment recommendation count
            $bestUmkm->increment('recommendation_count');
        }

        // If we found a route (even partial), return it with info about missing categories
        return empty($route) ? null : [
            'route' => $route,
            'missing' => $remainingCategories->values()->all(),
        ];
    }

    /**
     * Calculate Haversine distance between two coordinates in kilometers.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
