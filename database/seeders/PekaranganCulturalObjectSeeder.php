<?php

namespace Database\Seeders;

use App\Models\CulturalObject;
use App\Models\VillageSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PekaranganCulturalObjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $root = DB::transaction(function (): CulturalObject {
            $settings = VillageSettings::get();
            $settings = VillageSettings::whereKey($settings->id)->lockForUpdate()->firstOrFail();
            $wasConfigured = $settings->house_cultural_object_id !== null;
            $root = $settings->houseCulturalObject()->first();

            if ($root && $root->parent_id !== null) {
                throw new \RuntimeException('Induk budaya seluruh pekarangan harus berupa objek utama. Periksa Pengaturan Desa.');
            }

            if (! $root) {
                $candidates = CulturalObject::all()->filter(function (CulturalObject $object): bool {
                    $names = array_map(fn (string $name): string => mb_strtolower(trim($name)), $object->getTranslations('name'));

                    return $object->slug === 'pekarangan-rumah-adat'
                        || array_intersect($names, ['pekarangan rumah adat', 'pekarangan rumah', 'traditional courtyard', 'traditional house courtyard']) !== [];
                });
                if ($candidates->count() > 1) {
                    throw new \RuntimeException('Ada beberapa calon induk pekarangan (ID: '.$candidates->pluck('id')->implode(', ').'). Pilih satu di Pengaturan Desa sebelum menjalankan seeder.');
                }
                $root = $candidates->first();
                if ($root && $root->parent_id !== null) {
                    throw new \RuntimeException('Objek pekarangan yang ada masih menjadi objek anak. Atur sebagai objek utama terlebih dahulu.');
                }
            }

            $root ??= CulturalObject::create([
                'slug' => 'pekarangan-rumah-adat',
                'name' => ['id' => 'Pekarangan Rumah Adat', 'en' => 'Traditional Courtyard'],
                'short_description' => [
                    'id' => 'Kenali Angkul-angkul, Paon, Saka Enam, dan Merajan di pekarangan rumah adat.',
                    'en' => 'Explore the Angkul-angkul, Paon, Saka Enam, and Merajan in a traditional courtyard.',
                ],
                'description' => [
                    'id' => '<p>Kenali susunan ruang budaya di pekarangan rumah adat melalui Angkul-angkul, Paon, Saka Enam, dan Merajan. Jelajahi kisah, audio, dan model 3D pada masing-masing ruang budaya.</p>',
                    'en' => '<p>Explore the cultural spaces in a traditional courtyard: Angkul-angkul, Paon, Saka Enam, and Merajan. Discover the stories, audio, and 3D models for each space.</p>',
                ],
                'category' => 'pawongan',
                'place_type' => 'pekarangan',
                'is_detail' => false,
            ]);

            $this->attachExistingRooms($root, $settings->featured_cultural_object_id);
            $settings->house_cultural_object_id = $root->id;
            if (! $wasConfigured && $settings->featured_cultural_object_id === null) {
                $settings->featured_cultural_object_id = $root->id;
            }
            if ($settings->isDirty()) {
                $settings->save();
            }

            return $root;
        });

        $this->command?->info('Induk budaya pekarangan siap: '.$root->getTranslation('name', 'id').' (ID '.$root->id.').');
        $this->command?->info('Objek ruang yang tersedia ditautkan tanpa menyalin konten. Titik pekarangan tetap memakai data rumah.');
    }

    private function attachExistingRooms(CulturalObject $root, ?int $featuredId): void
    {
        $rooms = [
            'Angkul-angkul' => ['angkul angkul', 'angkul angkul rumah tradisional'],
            'Paon' => ['paon', 'dapur', 'dapur rumah tradisional', 'dapur rumah tradisional (paon)', 'traditional house kitchen (paon)'],
            'Saka Enam' => ['saka enam', 'seka enam', 'bale saka enam', 'bale sakenem'],
            'Merajan' => ['merajan', 'merajan rumah tradisional', 'sanggah merajan', 'traditional house merajan'],
        ];
        $candidates = CulturalObject::roots()->whereKeyNot($root->id)->withCount('children')->get();

        foreach ($rooms as $label => $names) {
            $matches = $candidates->filter(function (CulturalObject $object) use ($names): bool {
                foreach ($object->getTranslations('name') as $name) {
                    if (in_array(Str::squish(str_replace('-', ' ', mb_strtolower($name))), $names, true)) {
                        return true;
                    }
                }

                return false;
            });
            if ($matches->count() > 1) {
                throw new \RuntimeException('Ada beberapa objek '.$label.' (ID: '.$matches->pluck('id')->implode(', ').'). Tentukan objek yang menjadi anak pekarangan terlebih dahulu.');
            }
            $room = $matches->first();
            if ($room && $room->id === $featuredId) {
                $this->command?->warn($label.' sedang menjadi objek unggulan; pilihan tersebut dipertahankan.');
            } elseif ($room && $room->children_count === 0) {
                $room->update(['parent_id' => $root->id]);
            } elseif ($room) {
                $this->command?->warn($label.' sudah memiliki objek anak; susunannya dipertahankan dan perlu ditinjau secara manual.');
            }
        }
    }
}
