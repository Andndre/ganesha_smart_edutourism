@extends('layouts.app')
@section('title', __('Objek Budaya - Penglipuran'))
@section('header_title', __('Objek Budaya'))

@section('content')
    @php
        $categoryLabels = [
            'parahyangan' => __('Parahyangan'),
            'pawongan' => __('Pawongan'),
            'palemahan' => __('Palemahan'),
        ];
        // Only offer chips for categories that actually have objects (no empty filter states)
        $categories = array_values(array_intersect(array_keys($categoryLabels), array_column($objects, 'category')));
    @endphp

    <div class="px-4 py-6 md:px-8 md:py-8 lg:px-12" x-data="{ cat: 'all' }">

        <div class="mb-4 md:mb-6">
            <h2 class="font-playfair text-charcoal text-xl font-bold md:text-3xl">{{ __('Jelajah Warisan Budaya') }}</h2>
            <p class="mt-1 text-sm text-gray-500 md:mt-2 md:text-base">{{ __('Temukan kisah di balik setiap sudut desa') }}</p>
        </div>

        @if (count($categories) > 1)
            <div class="no-scrollbar -mx-4 mb-4 flex gap-2 overflow-x-auto px-4 pb-1 md:mx-0 md:mb-6 md:px-0">
                <button type="button" @click="cat = 'all'"
                    :class="cat === 'all' ? 'bg-primary text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200'"
                    class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition-colors">
                    {{ __('Semua') }}
                </button>
                @foreach ($categories as $category)
                    <button type="button" @click="cat = '{{ $category }}'"
                        :class="cat === '{{ $category }}' ? 'bg-primary text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200'"
                        class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition-colors">
                        {{ $categoryLabels[$category] }}
                    </button>
                @endforeach
            </div>
        @endif

        @if (!empty($objects))
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4">
                @foreach ($objects as $object)
                    <a href="{{ route('cultural-object', ['slug' => $object['slug']]) }}"
                        x-show="cat === 'all' || cat === '{{ $object['category'] ?? '' }}'"
                        class="group block h-full overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100 transition-all hover:-translate-y-1 hover:shadow-md active:scale-[0.98]">
                        <div class="relative h-32 overflow-hidden bg-gray-200 md:h-40">
                            <div class="bg-linear-to-t absolute inset-0 z-10 from-black/60 to-transparent"></div>

                            @if (!empty($object['historical_images']))
                                <img src="{{ asset('storage/' . $object['historical_images'][0]) }}" alt="{{ $object['name'] }}"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif

                            <div class="absolute bottom-2 left-2 right-2 z-20 text-white">
                                @if (!empty($object['ar_marker_id']) || !empty($object['model_3d_path']))
                                    <span class="text-primary inline-flex items-center rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-medium shadow-sm backdrop-blur-sm">
                                        {{ __('AR Tersedia') }}
                                    </span>
                                @endif
                                <h3 class="font-playfair line-clamp-2 text-sm font-bold leading-tight md:text-base">{{ $object['name'] }}</h3>
                            </div>
                        </div>
                        <div class="p-3">
                            <p class="line-clamp-2 text-xs text-gray-600 md:text-sm">{!! $object['short_description'] !!}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="py-10 text-center">
                <p class="text-gray-500">{{ __('Belum ada objek budaya yang terdaftar.') }}</p>
            </div>
        @endif

    </div>
@endsection
