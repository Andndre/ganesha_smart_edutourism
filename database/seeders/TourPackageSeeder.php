<?php

namespace Database\Seeders;

use App\Models\TourPackage;
use Illuminate\Database\Seeder;

class TourPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Village Walking Tour',
                'slug' => 'village-walking-tour',
                'description' => 'Explore the authentic village of Penglipuran on foot. Visit temples, traditional houses, and meet local artisans.',
                'inclusions' => json_encode([
                    'Professional local guide',
                    'Traditional welcome drink',
                    'Visit to 5 cultural sites',
                    'Stories and folklore',
                    'Photo opportunities',
                ]),
                'exclusions' => json_encode([
                    'Transportation to/from village',
                    'Personal expenses',
                    'Tips',
                ]),
                'price' => 150000,
                'duration_hours' => 2.5,
                'max_capacity' => 10,
                'min_capacity' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Cultural Immersion Day',
                'slug' => 'cultural-immersion-day',
                'description' => 'Full day experience of Balinese culture. Including cooking class, temple visit, and traditional arts workshop.',
                'inclusions' => json_encode([
                    'All-day guide',
                    'Breakfast at local restaurant',
                    'Cooking class with recipe book',
                    'Temple ceremony explanation',
                    'Craft workshop',
                    'Lunch included',
                    'Transport within village',
                ]),
                'exclusions' => json_encode([
                    'Transportation to/from village',
                ]),
                'price' => 350000,
                'duration_hours' => 6.0,
                'max_capacity' => 8,
                'min_capacity' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'AR Adventure Tour',
                'slug' => 'ar-adventure-tour',
                'description' => 'Experience Penglipuran through augmented reality. See historical reconstructions come to life with your phone.',
                'inclusions' => json_encode([
                    'AR device/phone rental',
                    'Guide with AR expertise',
                    '5 cultural AR points',
                    'Historical VR experience',
                    'Digital photo album',
                ]),
                'exclusions' => json_encode([
                    'Own smartphone',
                    'Transportation',
                ]),
                'price' => 200000,
                'duration_hours' => 3.0,
                'max_capacity' => 6,
                'min_capacity' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            TourPackage::create($package);
        }
    }
}
