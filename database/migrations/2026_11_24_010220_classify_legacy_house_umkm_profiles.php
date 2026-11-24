<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $candidates = [];
            $houseIds = [];
            $userIds = [];
            $profiles = DB::table('umkm_profiles')->whereNotNull('traditional_house_id')->orderBy('id')->lockForUpdate()->get();
            foreach ($profiles as $profile) {
                if ($profile->profile_type === 'house') {
                    continue;
                }
                $house = DB::table('traditional_houses')->where('id', $profile->traditional_house_id)->lockForUpdate()->first();
                if (! $house || ! $this->matchesHouse($profile->business_name, $house->number, $profile->id)) {
                    continue;
                }
                if (isset($houseIds[$house->id]) || DB::table('umkm_profiles')->where('traditional_house_id', $house->id)->where('profile_type', 'house')->exists()) {
                    throw new RuntimeException("Profil Rumah ganda untuk rumah ID {$house->id}; periksa UMKM ID {$profile->id}.");
                }
                $houseIds[$house->id] = true;
                $user = $profile->user_id ? DB::table('users')->where('id', $profile->user_id)->first() : null;
                if ($user && ($user->role !== 'umkm_owner' || isset($userIds[$user->id])
                    || DB::table('umkm_profiles')->where('user_id', $user->id)->count() !== 1
                    || DB::table('traditional_houses')->where('owner_user_id', $user->id)->where('id', '!=', $house->id)->exists()
                    || ($house->owner_user_id && $house->owner_user_id !== $user->id))) {
                    throw new RuntimeException("Akun pengelola ambigu pada UMKM ID {$profile->id}, akun ID {$user->id}.");
                }
                if ($user) {
                    $userIds[$user->id] = true;
                }
                $candidates[] = [$profile, $house, $user];
            }

            foreach ($candidates as [$profile, $house, $user]) {
                DB::table('umkm_profiles')->where('id', $profile->id)->update([
                    'profile_type' => 'house',
                    'business_name' => json_encode(['id' => 'Rumah No. '.$house->number, 'en' => 'House No. '.$house->number], JSON_THROW_ON_ERROR),
                ]);
                DB::table('traditional_houses')->where('id', $house->id)->update(['owner_user_id' => $user?->id]);
                $houseLocation = $this->location('App\\Models\\TraditionalHouse', $house->id);
                $profileLocation = $this->location('App\\Models\\UmkmProfile', $profile->id);
                $source = $this->validLocation($houseLocation) ? $houseLocation : ($this->validLocation($profileLocation) ? $profileLocation : null);
                if ($source) {
                    foreach ([['App\\Models\\TraditionalHouse', $house->id, 'cultural', 'Pekarangan No. '], ['App\\Models\\UmkmProfile', $profile->id, 'umkm', 'House No. ']] as [$type, $id, $category, $prefix]) {
                        DB::table('map_locations')->updateOrInsert(['locationable_type' => $type, 'locationable_id' => $id], [
                            'category' => $category, 'name' => $prefix.$house->number,
                            'latitude' => $source->latitude, 'longitude' => $source->longitude,
                            'is_accessible' => $source->is_accessible, 'accessibility_notes' => $source->accessibility_notes,
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
        if (Cache::supportsTags()) {
            Cache::tags(['umkm', 'explore', 'cultural'])->flush();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Classification grants may have been reassigned; keep them on rollback.
    }

    private function matchesHouse(string $value, int $number, int $profileId): bool
    {
        $decoded = json_decode($value, true);
        $names = is_array($decoded) ? [$decoded['id'] ?? null, $decoded['en'] ?? null] : [is_string($decoded) ? $decoded : $value];
        $numbers = [];
        $customName = false;
        foreach ($names as $name) {
            if (! is_string($name) || trim($name) === '') {
                continue;
            }
            if (preg_match('/\A(?:House|Rumah)\s+No\.\s*(\d{1,4})\z/i', trim($name), $matches)) {
                $numbers[] = (int) $matches[1];
            } else {
                $customName = true;
            }
        }
        if (count(array_unique($numbers)) > 1 || ($numbers && ! in_array($number, $numbers, true))) {
            throw new RuntimeException("Nomor rumah tidak cocok pada UMKM ID {$profileId}.");
        }

        return ! $customName && $numbers !== [];
    }

    private function location(string $type, int $id): ?stdClass
    {
        return DB::table('map_locations')->where('locationable_type', $type)->where('locationable_id', $id)->orderBy('id')->first();
    }

    private function validLocation(?stdClass $location): bool
    {
        return $location && is_numeric($location->latitude) && is_numeric($location->longitude)
            && abs((float) $location->latitude) <= 90 && abs((float) $location->longitude) <= 180;
    }
};
