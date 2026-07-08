<?php

namespace Database\Seeders;

use App\Models\TourPackage;
use Illuminate\Database\Seeder;

/**
 * Seeds entrance ticket products (tour_packages.type = 'ticket').
 *
 * Tiering follows the Lawang Sewu reference from the feature-options document:
 * plain entry ticket, and entry ticket + local guide.
 *
 * Idempotent: keyed by slug, safe to re-run.
 * NOTE: translatable fields MUST be locale-keyed arrays, never bare strings.
 */
class EntranceTicketSeeder extends Seeder
{
    public function run(): void
    {
        $tickets = [
            [
                'slug' => 'tiket-masuk-domestik',
                'name' => ['id' => 'Tiket Masuk — Wisatawan Domestik', 'en' => 'Entry Ticket — Domestic Visitor'],
                'description' => [
                    'id' => 'Tiket masuk Desa Wisata Penglipuran untuk wisatawan domestik. Berkeliling mandiri menyusuri gang tradisional, hutan bambu, dan area desa.',
                    'en' => 'Entry ticket to Penglipuran Tourism Village for domestic visitors. Explore the traditional alleys, bamboo forest, and village area at your own pace.',
                ],
                'price' => 25000,
                'inclusions' => [
                    'id' => ['Akses seluruh area desa', 'Peta interaktif digital', 'Parkir'],
                    'en' => ['Access to the whole village area', 'Interactive digital map', 'Parking'],
                ],
            ],
            [
                'slug' => 'tiket-masuk-mancanegara',
                'name' => ['id' => 'Tiket Masuk — Wisatawan Mancanegara', 'en' => 'Entry Ticket — International Visitor'],
                'description' => [
                    'id' => 'Tiket masuk Desa Wisata Penglipuran untuk wisatawan mancanegara. Berkeliling mandiri menyusuri gang tradisional, hutan bambu, dan area desa.',
                    'en' => 'Entry ticket to Penglipuran Tourism Village for international visitors. Explore the traditional alleys, bamboo forest, and village area at your own pace.',
                ],
                'price' => 50000,
                'inclusions' => [
                    'id' => ['Akses seluruh area desa', 'Peta interaktif digital', 'Parkir'],
                    'en' => ['Access to the whole village area', 'Interactive digital map', 'Parking'],
                ],
            ],
            [
                'slug' => 'tiket-masuk-pemandu-lokal',
                'name' => ['id' => 'Tiket Masuk + Pemandu Lokal', 'en' => 'Entry Ticket + Local Guide'],
                'description' => [
                    'id' => 'Tiket masuk dengan pendampingan pemandu lokal. Koordinasikan jadwal pemandu via WhatsApp sebelum melakukan pembayaran.',
                    'en' => 'Entry ticket with a local guide. Coordinate the guide schedule via WhatsApp before making payment.',
                ],
                'price' => 100000,
                'inclusions' => [
                    'id' => ['Akses seluruh area desa', 'Pemandu lokal', 'Peta interaktif digital', 'Parkir'],
                    'en' => ['Access to the whole village area', 'Local guide', 'Interactive digital map', 'Parking'],
                ],
            ],
        ];

        foreach ($tickets as $ticket) {
            TourPackage::updateOrCreate(
                ['slug' => $ticket['slug']],
                [
                    'name' => $ticket['name'],
                    'type' => 'ticket',
                    'description' => $ticket['description'],
                    'inclusions' => $ticket['inclusions'],
                    'price' => $ticket['price'],
                    'duration_hours' => 1.0,
                    'min_capacity' => 1,
                    'max_capacity' => 100,
                    'is_active' => true,
                ]
            );
        }
    }
}
