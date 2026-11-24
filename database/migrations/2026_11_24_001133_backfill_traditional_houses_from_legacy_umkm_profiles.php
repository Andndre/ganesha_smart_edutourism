<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $profiles = DB::table('umkm_profiles')->whereNull('traditional_house_id')->orderBy('id')->lockForUpdate()->get();
            $candidates = [];

            foreach ($profiles as $profile) {
                $number = $this->houseNumber($profile->business_name, $profile->id);
                if ($number === null) {
                    continue;
                }

                if (isset($candidates[$number])) {
                    throw new RuntimeException("Nomor rumah {$number} ambigu pada UMKM ID {$candidates[$number]->id} dan {$profile->id}. Periksa data sebelum menjalankan ulang migrasi.");
                }

                $candidates[$number] = $profile;
            }

            foreach ($candidates as $number => $profile) {
                $location = DB::table('map_locations')
                    ->where('locationable_type', 'App\\Models\\UmkmProfile')
                    ->where('locationable_id', $profile->id)
                    ->orderBy('id')->first();
                $hasCoordinates = $location !== null && $this->hasValidCoordinates($location);
                $house = DB::table('traditional_houses')->where('number', $number)->first();
                $timestamp = now();

                $houseId = $house?->id ?? DB::table('traditional_houses')->insertGetId([
                    'number' => $number,
                    'head_name' => trim($profile->owner_name) ?: 'Belum diisi',
                    'cover_image_path' => $profile->image_path ?: null,
                    'is_published' => (bool) $profile->is_active && $hasCoordinates,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                if ($hasCoordinates && ! DB::table('map_locations')
                    ->where('locationable_type', 'App\\Models\\TraditionalHouse')
                    ->where('locationable_id', $houseId)->exists()) {
                    DB::table('map_locations')->insert([
                        'name' => 'Pekarangan No. '.$number,
                        'category' => 'cultural',
                        'locationable_type' => 'App\\Models\\TraditionalHouse',
                        'locationable_id' => $houseId,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                        'is_accessible' => $location->is_accessible,
                        'accessibility_notes' => $location->accessibility_notes,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }

                DB::table('umkm_profiles')->where('id', $profile->id)->whereNull('traditional_house_id')->update([
                    'traditional_house_id' => $houseId,
                    'updated_at' => $timestamp,
                ]);
            }
        });

        if (Cache::supportsTags()) {
            Cache::tags(['explore', 'umkm', 'cultural'])->flush();
        }
    }

    public function down(): void
    {
        // One-way backfill: keep houses and links that administrators may have edited since migration.
    }

    private function houseNumber(string $businessName, int $profileId): ?int
    {
        $decoded = json_decode($businessName, true);
        $names = is_array($decoded)
            ? [$decoded['id'] ?? null, $decoded['en'] ?? null]
            : [is_string($decoded) ? $decoded : $businessName];
        $numbers = [];

        foreach ($names as $name) {
            if (! is_string($name) || ! preg_match('/\A(?:House|Rumah)\s+No\.\s*(\d{1,4})\z/i', trim($name), $matches)) {
                continue;
            }

            $number = (int) $matches[1];
            if ($number >= 1 && $number <= 9999) {
                $numbers[$number] = $number;
            }
        }

        if (count($numbers) > 1) {
            throw new RuntimeException("Nomor rumah berbeda antarbahasa pada UMKM ID {$profileId}. Periksa data sebelum menjalankan ulang migrasi.");
        }

        return $numbers === [] ? null : reset($numbers);
    }

    private function hasValidCoordinates(stdClass $location): bool
    {
        return is_numeric($location->latitude) && is_numeric($location->longitude)
            && (float) $location->latitude >= -90 && (float) $location->latitude <= 90
            && (float) $location->longitude >= -180 && (float) $location->longitude <= 180;
    }
};
