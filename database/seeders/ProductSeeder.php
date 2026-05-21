<?php

namespace Database\Seeders;

use App\Models\UmkmProduct;
use App\Models\UmkmProfile;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Products from Penglipuran Craft
        $craft = UmkmProfile::where('slug', 'penglipuran-craft')->first();

        if ($craft) {
            $products = [
                [
                    'umkm_profile_id' => $craft->id,
                    'name' => 'Bamboo Wind Chime',
                    'slug' => 'bamboo-wind-chime',
                    'description' => 'Handcrafted bamboo wind chime with traditional design. Produces soothing natural sounds.',
                    'price' => 75000,
                    'stock' => 50,
                    'unit' => 'pcs',
                    'is_active' => true,
                ],
                [
                    'umkm_profile_id' => $craft->id,
                    'name' => 'Coconut Shell Bowl',
                    'slug' => 'coconut-shell-bowl',
                    'description' => 'Polished coconut shell bowl, perfect for fruit or decorative use.',
                    'price' => 45000,
                    'stock' => 30,
                    'unit' => 'pcs',
                    'is_active' => true,
                ],
                [
                    'umkm_profile_id' => $craft->id,
                    'name' => 'Bamboo Basket',
                    'slug' => 'bamboo-basket',
                    'description' => 'Traditional woven bamboo basket for groceries or decoration.',
                    'price' => 120000,
                    'stock' => 20,
                    'unit' => 'pcs',
                    'is_active' => true,
                ],
            ];

            foreach ($products as $product) {
                UmkmProduct::create($product);
            }
        }

        // Products from Souvenir Collection
        $souvenir = UmkmProfile::where('slug', 'souvenir-collection')->first();

        if ($souvenir) {
            $products = [
                [
                    'umkm_profile_id' => $souvenir->id,
                    'name' => 'Penglipuran Postcard Set',
                    'slug' => 'penglipuran-postcard-set',
                    'description' => 'Set of 5 beautiful postcards featuring Penglipuran cultural sites.',
                    'price' => 25000,
                    'stock' => 100,
                    'unit' => 'set',
                    'is_active' => true,
                ],
                [
                    'umkm_profile_id' => $souvenir->id,
                    'name' => 'Wooden Keychain',
                    'slug' => 'wooden-keychain',
                    'description' => 'Hand-carved wooden keychain with traditional Balinese patterns.',
                    'price' => 35000,
                    'stock' => 75,
                    'unit' => 'pcs',
                    'is_active' => true,
                ],
            ];

            foreach ($products as $product) {
                UmkmProduct::create($product);
            }
        }

        // Products from Warung Dedari
        $dedari = UmkmProfile::where('slug', 'warung-dedari')->first();

        if ($dedari) {
            $products = [
                [
                    'umkm_profile_id' => $dedari->id,
                    'name' => 'Nasi Bali Combo',
                    'slug' => 'nasi-bali-combo',
                    'description' => 'Traditional Balinese rice plate with satay, lawar, and vegetables.',
                    'price' => 35000,
                    'stock' => 0,
                    'unit' => 'porsi',
                    'is_active' => true,
                ],
                [
                    'umkm_profile_id' => $dedari->id,
                    'name' => 'Lawar Klengis',
                    'slug' => 'lawar-klengis',
                    'description' => 'TraditionalBalinese minced coconut dish with herbs.',
                    'price' => 20000,
                    'stock' => 0,
                    'unit' => 'porsi',
                    'is_active' => true,
                ],
            ];

            foreach ($products as $product) {
                UmkmProduct::create($product);
            }
        }
    }
}
