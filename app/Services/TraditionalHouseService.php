<?php

namespace App\Services;

use App\Http\Requests\Owner\OwnerHouseRequest;
use App\Models\TraditionalHouse;
use App\Models\UmkmProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TraditionalHouseService
{
    public function save(FormRequest $request, TraditionalHouse $house): TraditionalHouse
    {
        $data = $request->validated();
        $isOwner = $request instanceof OwnerHouseRequest;
        $umkmIds = $isOwner ? $house->umkmProfiles()->pluck('id')->all() : ($data['umkm_profile_ids'] ?? []);
        $assignOwner = ! $isOwner && $request->exists('owner_user_id');
        $ownerId = $request->filled('owner_user_id') ? $request->integer('owner_user_id') : null;
        unset($data['owner_user_id']);
        $objectIds = $data['cultural_object_ids'] ?? [];
        unset($data['umkm_profile_ids'], $data['cultural_object_ids'], $data['latitude'], $data['longitude'], $data['cover_image'], $data['gallery_images'], $data['remove_cover_image'], $data['remove_gallery_images'], $data['is_accessible'], $data['accessibility_notes']);

        $data['is_open_to_visitors'] = $request->boolean('is_open_to_visitors');
        $data['is_published'] = $request->boolean('is_published');
        $data['ar_marker_id'] = ($data['ar_marker_id'] ?? null) ?: ($house->ar_marker_id ?: 'HOUSE_'.Str::ulid());
        $data['description'] ??= ['id' => '', 'en' => ''];

        $oldCover = $house->cover_image_path;
        if ($request->hasFile('cover_image')) {
            $newCover = $request->file('cover_image')->store('traditional_houses', 'public');
            $data['cover_image_path'] = $newCover;
        } elseif ($request->boolean('remove_cover_image')) {
            $data['cover_image_path'] = null;
        }

        $removedGallery = array_intersect($house->gallery ?? [], $request->input('remove_gallery_images', []));
        $gallery = array_values(array_diff($house->gallery ?? [], $removedGallery));
        foreach ($request->file('gallery_images', []) as $image) {
            $gallery[] = $image->store('traditional_houses', 'public');
        }
        $data['gallery'] = $gallery;

        $savedHouse = DB::transaction(function () use ($house, $data, $request, $umkmIds, $objectIds, $assignOwner, $ownerId): TraditionalHouse {
            if ($house->exists) {
                $lockedHouse = TraditionalHouse::whereKey($house->id)->lockForUpdate()->firstOrFail();
                if ($request instanceof OwnerHouseRequest) {
                    $actingProfile = $request->user()?->isAdmin()
                        ? UmkmProfile::find($request->query('umkm_profile_id') ?? $request->session()->get('admin_view_umkm_profile_id'))
                        : $request->user()?->umkmProfile()->first();
                    abort_unless($actingProfile?->isHouse() && $actingProfile->traditional_house_id === $lockedHouse->id && (int) $actingProfile->user_id === (int) $lockedHouse->owner_user_id, 403);
                }
            }
            $house->fill($data);
            $house->save();

            if ($request->filled('latitude') && $request->filled('longitude')) {
                $house->syncMapLocation([
                    'category' => 'cultural',
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                    'is_accessible' => $request->exists('is_accessible') ? $request->boolean('is_accessible') : ($house->mapLocation?->is_accessible ?? true),
                    'accessibility_notes' => $request->input('accessibility_notes', $house->mapLocation?->getTranslations('accessibility_notes') ?? []),
                ], true);
            }

            if ($assignOwner) {
                app(HouseProfileService::class)->assignOwner($house, $ownerId);
            }
            $primaryIds = $house->umkmProfiles()->where('profile_type', 'house')->pluck('id')->all();
            $umkmIds = array_unique([...$umkmIds, ...$primaryIds]);
            UmkmProfile::where('traditional_house_id', $house->id)->whereNotIn('id', $umkmIds)->get()->each(function (UmkmProfile $profile): void {
                $profile->update(['traditional_house_id' => null]);
            });
            foreach (UmkmProfile::whereIn('id', $umkmIds)->lockForUpdate()->get() as $profile) {
                if ($profile->isHouse() && $profile->traditional_house_id !== $house->id) {
                    throw ValidationException::withMessages(['umkm_profile_ids' => 'Profil Rumah tidak dapat dipindahkan melalui daftar UMKM.']);
                }
                $profile->update(['traditional_house_id' => $house->id]);
            }
            $house->culturalObjects()->sync($objectIds);

            return $house;
        });

        if (($request->hasFile('cover_image') || $request->boolean('remove_cover_image')) && $oldCover) {
            if (str_starts_with($oldCover, 'traditional_houses/')) {
                Storage::disk('public')->delete($oldCover);
            }
        }
        Storage::disk('public')->delete(array_values(array_filter($removedGallery, fn (string $path): bool => str_starts_with($path, 'traditional_houses/'))));

        return $savedHouse;
    }
}
