<?php

namespace App\Services;

use App\Enums\UmkmProfileType;
use App\Models\MapLocation;
use App\Models\TraditionalHouse;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class HouseProfileService
{
    public function prepare(UmkmProfile $profile): void
    {
        if ($profile->user_id) {
            User::whereKey($profile->user_id)->lockForUpdate()->first();
        }
        if ($profile->exists && $profile->getRawOriginal('profile_type') === 'house' && ! $profile->isHouse()) {
            throw ValidationException::withMessages(['profile_type' => 'Profil Rumah tidak dapat diubah menjadi Usaha.']);
        }
        if ($profile->user_id && UmkmProfile::where('user_id', $profile->user_id)->whereKeyNot($profile->getKey() ?? 0)->exists()) {
            throw ValidationException::withMessages(['user_id' => 'Akun ini sudah memiliki profil UMKM.']);
        }
        if (! $profile->isHouse()) {
            return;
        }

        $house = TraditionalHouse::whereKey($profile->traditional_house_id)->lockForUpdate()->first();
        if (! $house) {
            throw ValidationException::withMessages(['traditional_house_id' => 'Pilih rumah untuk profil Rumah.']);
        }
        if ($house->umkmProfiles()->where('profile_type', 'house')->whereKeyNot($profile->getKey() ?? 0)->lockForUpdate()->first()) {
            throw ValidationException::withMessages(['traditional_house_id' => 'Rumah ini sudah memiliki profil UMKM Rumah.']);
        }
        if ($profile->user_id) {
            $user = User::find($profile->user_id);
            if (! $user?->isUmkmOwner() || TraditionalHouse::where('owner_user_id', $user->id)->whereKeyNot($house->id)->exists()) {
                throw ValidationException::withMessages(['user_id' => 'Pilih akun pemilik UMKM yang belum mengelola rumah lain.']);
            }
            if ($house->owner_user_id && (int) $house->owner_user_id !== (int) $profile->user_id && ! $profile->exists) {
                throw ValidationException::withMessages(['user_id' => 'Rumah ini sudah memiliki pengelola.']);
            }
        }
        $profile->business_name = self::names($house->number);
        if ($profile->exists) {
            $profile->slug = $profile->getRawOriginal('slug');
        } elseif (! $profile->slug) {
            $profile->slug = $profile->generateCollisionFreeSlug('house-no-'.$house->number);
        }
    }

    /** @return array{id: string, en: string} */
    public static function names(int $number): array
    {
        return ['id' => 'Rumah No. '.$number, 'en' => 'House No. '.$number];
    }

    public function synchronizeOwner(UmkmProfile $profile): void
    {
        if ($profile->isHouse()) {
            if ($profile->getRawOriginal('user_id')) {
                TraditionalHouse::where('owner_user_id', $profile->getRawOriginal('user_id'))
                    ->whereKeyNot($profile->traditional_house_id)->update(['owner_user_id' => null]);
            }
            TraditionalHouse::whereKey($profile->traditional_house_id)->update(['owner_user_id' => $profile->user_id]);
            $location = $profile->traditionalHouse()->first()?->mapLocation;
            if ($location) {
                $this->synchronizeLocation($location);
            }
            $profile->mapLocation()->first()?->update(['name' => $profile->getMapDisplayName()]);
        }
        $this->flushCache();
    }

    public function revokeOwner(UmkmProfile $profile): void
    {
        if ($profile->isHouse()) {
            $house = $profile->traditionalHouse()->first();
            if ($house) {
                $house->owner_user_id = null;
                if (! $house->umkmProfiles()->active()->exists()) {
                    $house->is_published = false;
                }
                $house->save();
            }
        }
        $this->flushCache();
    }

    public function synchronizeLocation(MapLocation $location): void
    {
        $target = null;
        if ($location->locationable_type === TraditionalHouse::class) {
            $target = UmkmProfile::where('traditional_house_id', $location->locationable_id)->where('profile_type', 'house')->first();
        } elseif ($location->locationable_type === UmkmProfile::class) {
            $profile = UmkmProfile::find($location->locationable_id);
            $target = $profile?->isHouse() ? $profile->traditionalHouse()->first() : null;
        }
        if (! $target) {
            return;
        }

        $data = [
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'is_accessible' => (bool) $location->is_accessible,
            'accessibility_notes' => $location->getTranslations('accessibility_notes'),
        ];
        $mirror = $target->mapLocation()->first();
        if ($mirror && (float) $mirror->latitude === (float) $data['latitude']
            && (float) $mirror->longitude === (float) $data['longitude']
            && (bool) $mirror->is_accessible === $data['is_accessible']
            && $mirror->getTranslations('accessibility_notes') === $data['accessibility_notes']) {
            return;
        }
        $target->syncMapLocation(['category' => $target instanceof TraditionalHouse ? 'cultural' : 'umkm', ...$data], true);
    }

    public function assignOwner(TraditionalHouse $house, ?int $userId): void
    {
        $profile = $house->umkmProfiles()->where('profile_type', 'house')->lockForUpdate()->first();
        if ($userId === null) {
            if ($profile) {
                $profile->user_id = null;
                $profile->save();
            }
            $house->owner_user_id = null;
            $house->save();

            return;
        }

        $user = User::whereKey($userId)->lockForUpdate()->firstOrFail();
        $existing = $user->umkmProfile()->first();
        if ($profile && $existing && $existing->id !== $profile->id) {
            throw ValidationException::withMessages(['owner_user_id' => 'Akun ini sudah memiliki UMKM lain. Pilih akun tanpa profil atau pengelola saat ini.']);
        }
        $profile ??= $existing ?? new UmkmProfile;
        if ($profile->isHouse() && $profile->traditional_house_id !== $house->id) {
            throw ValidationException::withMessages(['owner_user_id' => 'Akun ini sudah mengelola rumah lain.']);
        }
        $profile->fill([
            'profile_type' => UmkmProfileType::House,
            'traditional_house_id' => $house->id,
            'user_id' => $user->id,
            'owner_name' => $user->name,
        ]);
        if (! $profile->exists) {
            $profile->is_active = true;
            $profile->business_name = self::names($house->number);
        }
        $profile->save();
        $house->owner_user_id = $user->id;
        $house->save();
    }

    public function flushCache(): void
    {
        if (Cache::supportsTags()) {
            Cache::tags(['umkm', 'explore', 'cultural'])->flush();
        }
    }
}
