<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today();

        $sampleEvents = [
            [
                'name' => 'Panglipuran Cultural Festival 2026',
                'description' => 'Festival tahunan utama Desa Adat Penglipuran yang menampilkan parade busana adat tempo dulu, pertunjukan gamelan gong kebyar khas daerah, pameran kriya bambu ramah lingkungan, serta stand kuliner tradisional Bali.',
                'category' => 'cultural',
                'start_datetime' => $today->copy()->addDays(5)->setTime(9, 0),
                'end_datetime' => $today->copy()->addDays(9)->setTime(18, 0),
                'location_name' => 'Halaman Utama Desa Adat Penglipuran',
                'is_free' => true,
                'price' => 0,
                'max_participants' => 500,
                'latitude' => -8.4312,
                'longitude' => 115.3521,
            ],
            [
                'name' => 'Upacara Piodalan Agung Pura Penataran',
                'description' => 'Ritual keagamaan Hindu Bali odalan agung di Pura Penataran Desa Penglipuran. Kegiatan ini diikuti oleh prosesi persembahyangan suci bersama, pementasan sakral Tari Kecak kolosal, Tari Rejang Dewa, dan kidung puji-pujian sakral di malam hari.',
                'category' => 'ceremony',
                'start_datetime' => $today->copy()->addDays(12)->setTime(8, 0),
                'end_datetime' => $today->copy()->addDays(12)->setTime(23, 0),
                'location_name' => 'Pura Penataran Agung Penglipuran',
                'is_free' => true,
                'price' => 0,
                'max_participants' => null,
                'latitude' => -8.4320,
                'longitude' => 115.3530,
            ],
            [
                'name' => 'Workshop Menganyam Bambu Penglipuran',
                'description' => 'Kelas edukasi interaktif belajar langsung dari perajin bambu lokal Desa Penglipuran. Peserta diajarkan teknik-teknik dasar memotong, membersihkan, dan menganyam bambu pilihan hingga menjadi aneka wadah kerajinan khas bernilai seni tinggi.',
                'category' => 'workshop',
                'start_datetime' => $today->copy()->subDays(3)->setTime(10, 0),
                'end_datetime' => $today->copy()->subDays(3)->setTime(13, 0),
                'location_name' => 'Bale Banjar Desa Wisata Penglipuran',
                'is_free' => false,
                'price' => 75000,
                'max_participants' => 30,
                'latitude' => -8.4305,
                'longitude' => 115.3515,
            ],
            [
                'name' => 'Festival Minuman Tradisional Loloh Cemcem',
                'description' => 'Eksplorasi warisan kuliner minuman herbal khas Bali, Loloh Cemcem. Pengunjung berkesempatan mencicipi aneka racikan loloh dari dedaunan cemcem segar berkhasiat tinggi, mempelajari resep rahasia keluarga lokal, dan membeli langsung produk premium.',
                'category' => 'culinary',
                'start_datetime' => $today->copy()->addDays(1)->setTime(10, 0),
                'end_datetime' => $today->copy()->addDays(1)->setTime(17, 0),
                'location_name' => 'Balai Pertemuan Kuliner Penglipuran',
                'is_free' => true,
                'price' => 0,
                'max_participants' => 150,
                'latitude' => -8.4298,
                'longitude' => 115.3508,
            ],
        ];

        foreach ($sampleEvents as $se) {
            // Check if the event already exists to prevent duplicate seeds
            if (! Event::where('name', $se['name'])->exists()) {
                $event = new Event;
                $event->name = $se['name'];
                $event->slug = Str::slug($se['name']).'-'.Str::random(5);
                $event->description = $se['description'];
                $event->category = $se['category'];
                $event->start_datetime = $se['start_datetime'];
                $event->end_datetime = $se['end_datetime'];
                $event->location_name = $se['location_name'];
                $event->is_free = $se['is_free'];
                $event->price = $se['price'];
                $event->max_participants = $se['max_participants'];
                $event->current_participants = 0;
                $event->save();

                if ($se['latitude'] !== null && $se['longitude'] !== null) {
                    $event->mapLocation()->create([
                        'name' => $event->name,
                        'category' => 'cultural',
                        'latitude' => $se['latitude'],
                        'longitude' => $se['longitude'],
                        'is_accessible' => true,
                    ]);
                }
            }
        }
    }
}
