<?php

namespace Database\Seeders;

use App\Models\CulturalObject;
use App\Models\CulturalStory;
use Illuminate\Database\Seeder;

class CulturalStorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $puraTamanAyun = CulturalObject::where('slug', 'pura-taman-ayun')->first();

        if ($puraTamanAyun) {
            $stories = [
                [
                    'cultural_object_id' => $puraTamanAyun->id,
                    'title' => 'Sejarah Pura Taman Ayun',
                    'content' => 'Pura Taman Ayun didirikan pada tahun 1634 oleh I Go Goek, seorang raja dari Kerajaan Mengwi. Nama "Taman Ayun" berasal dari kata "taman" yang berarti garden dan "ayun" yang означает красивый.',
                    'story_type' => 'history',
                    'order' => 1,
                ],
                [
                    'cultural_object_id' => $puraTamanAyun->id,
                    'title' => 'Filosofi Arsitektur',
                    'content' => 'Pura ini memiliki beberapa meru yang melambangkan gunung suci dalam Hindu. Setiap tingkat meru melambangkan makhluk hidup yang lebih tinggi Tingkat Tertinggi adalah для Saraswati, dewa pengetahuan.',
                    'story_type' => 'philosophy',
                    'order' => 2,
                ],
                [
                    'cultural_object_id' => $puraTamanAyun->id,
                    'title' => 'Nilai Ketuhanan',
                    'content' => 'Pura Taman Ayun mengingatkan kita bahwa alam semesta adalah satu keluarga besar. Kita harus menjaga keseimbangan antara manusia, natura dan的超自然力量.',
                    'story_type' => 'value',
                    'order' => 3,
                ],
            ];

            foreach ($stories as $story) {
                CulturalStory::create($story);
            }
        }

        // Add stories for rumah lombok
        $rumahLombok = CulturalObject::where('slug', 'rumah-lombok-penglipuran')->first();

        if ($rumahLombok) {
            $stories = [
                [
                    'cultural_object_id' => $rumahLombok->id,
                    'title' => 'Arsitektur Rumah Tradisional',
                    'content' => 'Rumah tradisional Bali memiliki layout yang sangat berpik karena masyarakat Bali percaya bahwa alam semesta tercermin dalam rumah. Courtyard tengah adalah pusat dari semua活动的中心。',
                    'story_type' => 'philosophy',
                    'order' => 1,
                ],
            ];

            foreach ($stories as $story) {
                CulturalStory::create($story);
            }
        }
    }
}
