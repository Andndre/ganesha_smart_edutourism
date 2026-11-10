@extends('layouts.dashboard')

@section('title', $house ? 'Edit house' : 'Add house')

@section('content')
@php
    $selectedProfiles = old('umkm_profile_ids', $house?->umkmProfiles->pluck('id')->all() ?? []);
    $selectedObjects = old('cultural_object_ids', $house?->culturalObjects->pluck('id')->all() ?? []);
    $description = $house?->getTranslations('description') ?? [];
@endphp
<div class="mb-6">
    <a href="{{ route('admin.houses.index') }}" class="text-sm font-semibold text-primary">← Back to houses</a>
    <h1 class="mt-2 font-display text-3xl font-bold text-charcoal">{{ $house ? 'Edit courtyard No. '.$house->number : 'Add traditional house' }}</h1>
    <p class="mt-1 text-sm text-gray-600">Only publish a house after its location and at least one active UMKM are verified.</p>
</div>
@if($errors->any())
    <div role="alert" class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><strong>Check these fields:</strong><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if(session('success'))<div class="mb-5 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-primary">{{ session('success') }}</div>@endif

<form method="POST" action="{{ $house ? route('admin.houses.update', $house) : route('admin.houses.store') }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    @csrf
    @if($house) @method('PUT') @endif
    <div class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-charcoal">Household</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold">House number<input type="number" min="1" max="9999" name="number" value="{{ old('number', $house?->number) }}" required class="mt-1 min-h-11 w-full rounded-xl border border-gray-200 px-3"></label>
                <label class="block text-sm font-semibold">Head of household<input name="head_name" value="{{ old('head_name', $house?->head_name) }}" required maxlength="255" class="mt-1 min-h-11 w-full rounded-xl border border-gray-200 px-3"></label>
                <label class="block text-sm font-semibold">Male residents<input type="number" min="0" name="male_count" value="{{ old('male_count', $house?->male_count ?? 0) }}" required class="mt-1 min-h-11 w-full rounded-xl border border-gray-200 px-3"></label>
                <label class="block text-sm font-semibold">Female residents<input type="number" min="0" name="female_count" value="{{ old('female_count', $house?->female_count ?? 0) }}" required class="mt-1 min-h-11 w-full rounded-xl border border-gray-200 px-3"></label>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold">Description (Indonesian)<textarea name="description[id]" rows="4" class="mt-1 w-full rounded-xl border border-gray-200 p-3">{{ old('description.id', $description['id'] ?? '') }}</textarea></label>
                <label class="block text-sm font-semibold">Description (English)<textarea name="description[en]" rows="4" class="mt-1 w-full rounded-xl border border-gray-200 p-3">{{ old('description.en', $description['en'] ?? '') }}</textarea></label>
            </div>
        </section>
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-charcoal">Location and media</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold">Latitude<input type="number" step="any" name="latitude" value="{{ old('latitude', $house?->mapLocation?->latitude) }}" class="mt-1 min-h-11 w-full rounded-xl border border-gray-200 px-3"></label>
                <label class="block text-sm font-semibold">Longitude<input type="number" step="any" name="longitude" value="{{ old('longitude', $house?->mapLocation?->longitude) }}" class="mt-1 min-h-11 w-full rounded-xl border border-gray-200 px-3"></label>
                <label class="block text-sm font-semibold">Cover image<input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm"></label>
                <label class="block text-sm font-semibold">Gallery images<input type="file" name="gallery_images[]" multiple accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm"></label>
            </div>
            @if($house?->cover_image_path)<img src="{{ asset('storage/'.$house->cover_image_path) }}" alt="Current house cover" class="mt-4 h-32 w-48 rounded-xl object-cover">@endif
        </section>
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-2 text-lg font-bold text-charcoal">UMKM at this house</h2>
            <p class="mb-4 text-sm text-gray-600">A published house needs at least one active UMKM.</p>
            <div class="grid max-h-64 gap-2 overflow-y-auto sm:grid-cols-2">
                @foreach($profiles as $profile)
                    <label class="flex min-h-11 items-center gap-3 rounded-xl border border-gray-200 px-3 text-sm"><input type="checkbox" name="umkm_profile_ids[]" value="{{ $profile->id }}" @checked(in_array($profile->id, $selectedProfiles))><span>{{ translateValue($profile->business_name) }} <small class="text-gray-500">{{ $profile->is_active ? 'Active' : 'Inactive' }}</small></span></label>
                @endforeach
            </div>
            <h2 class="mb-2 mt-6 text-lg font-bold text-charcoal">Cultural spaces and objects</h2>
            <div class="grid max-h-64 gap-2 overflow-y-auto sm:grid-cols-2">
                @foreach($objects as $object)
                    <label class="flex min-h-11 items-center gap-3 rounded-xl border border-gray-200 px-3 text-sm"><input type="checkbox" name="cultural_object_ids[]" value="{{ $object->id }}" @checked(in_array($object->id, $selectedObjects))><span>{{ translateValue($object->name) }}</span></label>
                @endforeach
            </div>
        </section>
    </div>
    <div class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-charcoal">Publication</h2>
            <label class="mb-3 flex items-center gap-3 text-sm"><input type="checkbox" name="is_open_to_visitors" value="1" @checked(old('is_open_to_visitors', $house?->is_open_to_visitors))>Open to visitors</label>
            <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $house?->is_published))>Published in the public directory</label>
            @if($house?->ar_marker_id)
                <div class="mt-5 border-t border-gray-100 pt-5 text-center">
                    <p class="mb-3 text-sm font-semibold">One marker for this courtyard</p>
                    <img src="{{ qrSvgDataUri(route('ar-scan-redirect', ['arMarkerId' => $house->ar_marker_id])) }}" alt="QR marker for courtyard {{ $house->number }}" class="mx-auto h-48 w-48">
                    <p class="mt-2 break-all font-mono text-xs text-gray-500">{{ $house->ar_marker_id }}</p>
                    <p class="mt-2 text-xs text-gray-500">Print this marker at the courtyard entrance.</p>
                </div>
            @endif
        </section>
        @if(auth()->user()?->isAdmin())<button class="tap-target w-full rounded-full bg-primary px-5 text-sm font-bold text-white">Save house</button>@endif
    </div>
</form>
@if($house && auth()->user()?->isAdmin())
    <form method="POST" action="{{ route('admin.houses.destroy', $house) }}" onsubmit="return confirm('Delete this house and its marker?')" class="mt-8">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-700 underline">Delete house</button></form>
@endif
@endsection
