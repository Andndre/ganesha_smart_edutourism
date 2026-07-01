<?php

namespace Tests\Unit;

use App\Http\Concerns\ExtractsGeoFields;
use Illuminate\Http\Request;
use Tests\TestCase;

class ExtractsGeoFieldsTest extends TestCase
{
    public function test_extracts_and_removes_geo_keys_from_validated(): void
    {
        $subject = new class
        {
            use ExtractsGeoFields;

            public function run(Request $request, array &$validated): array
            {
                return $this->extractGeoFields($request, $validated);
            }
        };

        $request = Request::create('/test', 'POST', ['is_accessible' => '1']);
        $validated = [
            'name' => 'Toilet',
            'latitude' => -8.4,
            'longitude' => 115.2,
            'accessibility_notes' => 'Ramp available',
        ];

        $geo = $subject->run($request, $validated);

        $this->assertSame([
            'latitude' => -8.4,
            'longitude' => 115.2,
            'is_accessible' => true,
            'accessibility_notes' => 'Ramp available',
        ], $geo);

        $this->assertSame(['name' => 'Toilet'], $validated);
    }
}
