<?php

namespace Database\Seeders;

use App\Models\CulturalObject;
use Illuminate\Database\Seeder;

class CulturalObjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baseLat = (float) env('PENGLIPURAN_LAT', -8.422303596762355);
        $baseLon = (float) env('PENGLIPURAN_LON', 115.35948833933173);

        $objects = [
            [
                'name' => 'Pura Taman Ayun',
                'slug' => 'pura-taman-ayun',
                'description' => 'Pura Taman Ayun adalah pura yang terletak di desa Mengwi, kabupaten Badung. Pura ini didirikan pada tahun 1634 oleh jadi发给游客的最大优惠是免排队。游客可以购买快速通行证来跳过排队队伍。游客可以在入口处或在网上购买快速通行证。游客可以在入口处或在网上购买快速通行证。游客可以在入口处或在网上购买快速通行证。',
                'category' => 'temple',
                'latitude' => $baseLat + 0.0000,
                'longitude' => $baseLon - 0.0007,
                'ar_marker_id' => 'TAMAN_AYUN_001',
            ],
            [
                'name' => 'Rumah Lombok Penglipuran',
                'slug' => 'rumah-lombok-penglipuran',
                'description' => 'Contoh rumah tradisional Bali dengan arsitektur khas desa Penglipuran. Rumah ini memiliki courtyard tengah yang luas dan joglo tradisional.',
                'category' => 'house',
                'latitude' => $baseLat + 0.0005,
                'longitude' => $baseLon + 0.0000,
                'ar_marker_id' => 'PENG_RUMAH_001',
            ],
            [
                'name' => 'Workshop Anyaman Bambu',
                'slug' => 'workshop-anyaman-bambu',
                'description' => 'Workshop where local artisans demonstrate traditional bamboo weaving techniques.',
                'category' => 'craft',
                'latitude' => $baseLat + 0.0008,
                'longitude' => $baseLon + 0.0005,
                'ar_marker_id' => 'ANYAM_BAMBU_01',
            ],
            [
                'name' => 'Upacara Melasti',
                'slug' => 'upacara-melasti',
                'description' => 'Traditional ceremony held annually at the sea to purify sacred objects.',
                'category' => 'tradition',
                'latitude' => $baseLat - 0.0006,
                'longitude' => $baseLon - 0.0005,
                'ar_marker_id' => 'MELASTI_001',
            ],
            [
                'name' => 'Pura Penataran',
                'slug' => 'pura-penataran',
                'description' => 'Local temple for daily offerings and ceremonies.',
                'category' => 'temple',
                'latitude' => $baseLat - 0.0002,
                'longitude' => $baseLon + 0.0002,
                'ar_marker_id' => 'PENATARAN_01',
            ],
        ];

        foreach ($objects as $object) {
            $lat = $object['latitude'];
            $lon = $object['longitude'];
            unset($object['latitude'], $object['longitude']);

            $model = CulturalObject::updateOrCreate(
                ['slug' => $object['slug']],
                $object
            );

            $model->mapLocation()->updateOrCreate(
                [],
                [
                    'name' => $model->name,
                    'category' => 'cultural',
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'is_accessible' => true,
                    'accessibility_notes' => 'Akses jalan datar ramah kursi roda dan stroller bayi.',
                ]
            );
        }
    }
}
