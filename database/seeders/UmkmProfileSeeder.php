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
        $umkms = [
            [
                'owner_name' => 'Wayan Sudira',
                'business_name' => 'Warung Dedari',
                'slug' => 'warung-dedari',
                'description' => 'Traditional Balinese restaurant serving authentic local cuisine. Our grandmother recipes passed down for generations.',
                'category' => 'culinary',
                'latitude' => -8.5880,
                'longitude' => 115.1625,
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
                'latitude' => -8.5872,
                'longitude' => 115.1618,
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
                'latitude' => -8.5888,
                'longitude' => 115.1632,
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
                'latitude' => -8.5895,
                'longitude' => 115.1640,
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
                'latitude' => -8.5878,
                'longitude' => 115.1622,
                'ar_marker_id' => 'UMKM_COOKING_01',
                'rating' => 4.9,
                'is_active' => true,
            ],
        ];

        foreach ($umkms as $umkm) {
            UmkmProfile::create($umkm);
        }
    }
}
