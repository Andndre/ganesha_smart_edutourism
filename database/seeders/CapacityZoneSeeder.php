<?php

namespace Database\Seeders;

use App\Models\CapacityZone;
use Illuminate\Database\Seeder;

class CapacityZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Zona Utama (Jalan Utama)',
                'zone_identifier' => 'main_street',
                'max_capacity' => 400,
                'warning_threshold' => 70,
                'critical_threshold' => 90,
                'latitude' => -8.422303,
                'longitude' => 115.359488,
                'radius_meters' => 200,
                'is_active' => true,
            ],
            [
                'name' => 'Area UMKM & Pasar',
                'zone_identifier' => 'umkm_market',
                'max_capacity' => 300,
                'warning_threshold' => 70,
                'critical_threshold' => 90,
                'latitude' => -8.424103, // slightly south
                'longitude' => 115.359488,
                'radius_meters' => 80,
                'is_active' => true,
            ],
            [
                'name' => 'Pura Penataran Agung',
                'zone_identifier' => 'pura_penataran',
                'max_capacity' => 150,
                'warning_threshold' => 70,
                'critical_threshold' => 90,
                'latitude' => -8.420100, // north
                'longitude' => 115.359500,
                'radius_meters' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Kebun Bambu & Jalur Trekking',
                'zone_identifier' => 'bamboo_forest',
                'max_capacity' => 200,
                'warning_threshold' => 70,
                'critical_threshold' => 90,
                'latitude' => -8.420500, // north-east
                'longitude' => 115.361000,
                'radius_meters' => 150,
                'is_active' => true,
            ],
        ];

        foreach ($zones as $zone) {
            CapacityZone::updateOrCreate(
                ['zone_identifier' => $zone['zone_identifier']],
                $zone
            );
        }
    }
}
