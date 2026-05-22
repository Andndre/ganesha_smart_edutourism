<?php

namespace Database\Seeders;

use App\Models\UmkmProfile;
use Illuminate\Database\Seeder;

class UmkmProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baseLat = (float) env('PENGLIPURAN_LAT', -8.422303596762355);
        $baseLon = (float) env('PENGLIPURAN_LON', 115.35948833933173);

        $umkms = [
            [
                'owner_name' => 'Wayan Sudira',
                'business_name' => 'Warung Dedari',
                'slug' => 'warung-dedari',
                'description' => 'Traditional Balinese restaurant serving authentic local cuisine. Our grandmother recipes passed down for generations.',
                'category' => 'culinary',
                'latitude' => $baseLat + 0.0000,
                'longitude' => $baseLon + 0.0000,
                'ar_marker_id' => 'UMKM_DEDARI_01',
                'rating' => 4.5,
                'is_active' => true,
            ],
            [
                'owner_name' => 'Made Sucandra',
                'business_name' => 'Penglipuran Craft',
                'slug' => 'penglipuran-craft',
                'description' => 'Handmade bamboo and coconut crafts. Every piece is handcrafted by local artisans using traditional techniques.',
                'category' => 'craft',
                'latitude' => $baseLat + 0.0008,
                'longitude' => $baseLon - 0.0007,
                'ar_marker_id' => 'UMKM_CRAFT_01',
                'rating' => 4.8,
                'is_active' => true,
            ],
            [
                'owner_name' => 'Ketut Rai',
                'business_name' => 'Souvenir Collection',
                'slug' => 'souvenir-collection',
                'description' => 'Unique souvenirs and gifts inspired by Penglipuran culture.',
                'category' => 'souvenir',
                'latitude' => $baseLat - 0.0008,
                'longitude' => $baseLon + 0.0007,
                'ar_marker_id' => 'UMKM_SOUVENIR_01',
                'rating' => 4.2,
                'is_active' => true,
            ],
            [
                'owner_name' => 'Putu Artawa',
                'business_name' => 'Traditional Massage',
                'slug' => 'traditional-massage',
                'description' => 'Authentic Balinese traditional massage and spa services.',
                'category' => 'service',
                'latitude' => $baseLat - 0.0015,
                'longitude' => $baseLon + 0.0015,
                'ar_marker_id' => 'UMKM_MASSAGE_01',
                'rating' => 4.6,
                'is_active' => true,
            ],
            [
                'owner_name' => 'Komang Sari',
                'business_name' => 'Balinese Cooking Class',
                'slug' => 'balinese-cooking-class',
                'description' => 'Learn to cook authentic Balinese dishes in our traditional kitchen.',
                'category' => 'service',
                'latitude' => $baseLat + 0.0002,
                'longitude' => $baseLon - 0.0003,
                'ar_marker_id' => 'UMKM_COOKING_01',
                'rating' => 4.9,
                'is_active' => true,
            ],
        ];

        foreach ($umkms as $umkm) {
            $lat = $umkm['latitude'];
            $lon = $umkm['longitude'];
            unset($umkm['latitude'], $umkm['longitude']);

            $model = UmkmProfile::updateOrCreate(
                ['slug' => $umkm['slug']],
                $umkm
            );

            $model->mapLocation()->updateOrCreate(
                [],
                [
                    'name' => $model->business_name,
                    'category' => 'umkm',
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'is_accessible' => true,
                    'accessibility_notes' => 'Pintu masuk landai, staf siap membantu akses disabilitas.',
                ]
            );
        }
    }
}
