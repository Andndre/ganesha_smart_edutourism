<?php

namespace App\Http\Concerns;

use Illuminate\Http\Request;

trait ExtractsGeoFields
{
    /**
     * Pull latitude/longitude/accessibility fields out of $validated (removing
     * them by reference) for passing to Model::syncMapLocation() separately.
     */
    protected function extractGeoFields(Request $request, array &$validated): array
    {
        $geo = [
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'is_accessible' => $request->has('is_accessible'),
            'accessibility_notes' => $validated['accessibility_notes'] ?? null,
        ];

        unset($validated['latitude'], $validated['longitude'], $validated['is_accessible'], $validated['accessibility_notes']);

        return $geo;
    }
}
