@extends('layouts.app')

@section('title', __('Pekarangan No.') .' '. $house->number)
@section('header_title', __('Pekarangan No.') .' '. $house->number)

@section('content')
<div class="mx-auto max-w-7xl px-4 pb-12 pt-6 md:px-8 lg:px-12">
    <nav class="mb-5 text-xs text-gray-500"><a href="{{ route('houses.index') }}" class="hover:text-primary">{{ __('Rumah Adat') }}</a> / {{ __('Pekarangan') }} #{{ $house->number }}</nav>
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div>
            @if($house->cover_image_path)<img src="{{ asset('storage/'.$house->cover_image_path) }}" alt="{{ __('Pekarangan No.') }} {{ $house->number }}" class="aspect-[16/9] w-full rounded-3xl object-cover">@endif
            <div class="mt-6 flex flex-wrap items-center gap-3"><span class="rounded-full bg-[#eaf3ec] px-3 py-1 text-xs font-bold text-primary">{{ __('Pekarangan') }} #{{ $house->number }}</span><span class="text-xs font-semibold {{ $house->is_open_to_visitors ? 'text-primary' : 'text-amber-700' }}">{{ $house->is_open_to_visitors ? __('Terbuka untuk Wisatawan') : __('Belum terbuka untuk kunjungan') }}</span></div>
            <h1 class="mt-3 font-display text-3xl font-bold text-charcoal md:text-5xl">{{ __('Rumah Adat') }} #{{ $house->number }}</h1>
            <p class="mt-3 max-w-3xl text-base leading-relaxed text-gray-700">{{ translateValue($house->description) }}</p>
            @if($house->gallery)
                <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-3">@foreach($house->gallery as $image)<img src="{{ asset('storage/'.$image) }}" alt="{{ __('Foto pekarangan') }} {{ $house->number }}" loading="lazy" class="aspect-[4/3] w-full rounded-xl object-cover">@endforeach</div>
            @endif

            <section class="mt-10"><h2 class="font-display text-2xl font-bold text-charcoal">{{ __('Ruang Budaya di Pekarangan') }}</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">
                @forelse($house->culturalObjects as $object)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><a href="{{ route('cultural-object', ['slug' => $object->slug]) }}" class="block"><h3 class="font-display text-lg font-bold">{{ translateValue($object->name) }}</h3><p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ translateValue($object->short_description) }}</p></a>@if($object->arModel?->model_3d_path)<a href="{{ route('ar-scan', ['model_id' => $object->arModel->id]) }}" class="tap-target mt-3 inline-flex rounded-full bg-[#eaf3ec] px-4 text-xs font-bold text-primary">{{ __('Lihat model 3D') }} →</a>@endif</div>
                @empty
                    <p class="col-span-full rounded-2xl bg-white p-5 text-sm text-gray-600">{{ __('Informasi ruang budaya sedang dilengkapi.') }}</p>
                @endforelse
            </div></section>

            <section class="mt-10"><h2 class="font-display text-2xl font-bold text-charcoal">{{ __('UMKM di Pekarangan Ini') }}</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach($house->umkmProfiles as $profile)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <a href="{{ route('umkm.store', ['id' => $profile->id]) }}" class="block"><h3 class="font-display text-lg font-bold">{{ translateValue($profile->business_name) }}</h3><p class="mt-1 text-sm text-gray-600">{{ $profile->owner_name }}</p></a>
                        <div class="mt-4 space-y-2">
                            @forelse($profile->activeProducts->take(3) as $product)
                                <div class="flex items-center gap-3 rounded-xl bg-surface p-2">
                                    @if($product->display_image)<img src="{{ asset('storage/'.$product->display_image) }}" alt="{{ $product->display_name }}" loading="lazy" class="h-10 w-10 rounded-lg object-cover">@endif
                                    <span class="min-w-0 flex-1 truncate text-xs font-semibold text-charcoal">{{ $product->display_name }}</span>
                                    @if($product->price_range)<span class="shrink-0 text-xs font-bold text-primary">Rp {{ number_format($product->price_range['min'], 0, ',', '.') }}</span>@endif
                                </div>
                            @empty
                                <p class="text-xs text-gray-500">{{ __('UMKM ini tidak memiliki produk aktif saat ini.') }}</p>
                            @endforelse
                        </div>
                        <a href="{{ route('umkm.store', ['id' => $profile->id]) }}" class="tap-target mt-3 inline-flex rounded-full bg-[#eaf3ec] px-4 text-xs font-bold text-primary">{{ __('Kunjungi Warung') }} →</a>
                    </div>
                @endforeach
            </div></section>
        </div>
        <aside class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"><h2 class="font-display text-xl font-bold">{{ __('Penghuni Pekarangan') }}</h2><p class="mt-4 text-sm text-gray-500">{{ __('Kepala keluarga') }}</p><p class="font-semibold text-charcoal">{{ $house->head_name }}</p><div class="mt-5 grid grid-cols-2 gap-3"><div class="rounded-xl bg-surface p-3"><strong class="text-2xl text-primary">{{ $house->male_count }}</strong><p class="text-xs text-gray-600">{{ __('Laki-laki') }}</p></div><div class="rounded-xl bg-surface p-3"><strong class="text-2xl text-primary">{{ $house->female_count }}</strong><p class="text-xs text-gray-600">{{ __('Perempuan') }}</p></div></div></div>
            @if($house->mapLocation)
                <a href="{{ route('explore', ['action' => 'route', 'id' => $house->mapLocation->id, 'lat' => $house->mapLocation->latitude, 'lng' => $house->mapLocation->longitude, 'name' => $house->getMapDisplayName()]) }}" class="tap-target flex w-full rounded-full bg-primary px-5 text-center text-sm font-bold text-white">{{ __('Lihat Lokasi di Peta') }} →</a>
            @endif
            @if($house->ar_marker_id)<div class="rounded-2xl border border-gray-200 bg-white p-5 text-center"><img src="{{ qrSvgDataUri(route('ar-scan-redirect', ['arMarkerId' => $house->ar_marker_id])) }}" alt="{{ __('Marker pekarangan') }} {{ $house->number }}" class="mx-auto h-32 w-32"><p class="mt-3 text-xs text-gray-600">{{ __('Satu marker untuk seluruh pekarangan ini.') }}</p></div>@endif
        </aside>
    </div>
</div>
@endsection
