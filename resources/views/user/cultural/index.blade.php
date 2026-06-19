@extends('layouts.app')
@section('title', 'Objek Budaya - Penglipuran')
@section('header_title', 'Objek Budaya')

@section('content')
    <div class="px-4 py-6 md:px-8 md:py-8 lg:px-12">

        <div class="mb-6 md:mb-8">
            <h2 class="font-playfair text-charcoal text-xl font-bold md:text-3xl">Jelajah Warisan Budaya</h2>
            <p class="mt-1 text-sm text-gray-500 md:mt-2 md:text-base">Temukan kisah di balik setiap sudut desa</p>
        </div>

        @forelse($objects as $object)
            @if ($loop->first)
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
            @endif

            <a href="{{ route('cultural-object', ['slug' => $object->slug]) }}"
                class="group block overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5 active:scale-[0.98]">
                <div class="relative h-48 overflow-hidden bg-gray-200 md:h-56">
                    <div class="bg-linear-to-t absolute inset-0 z-10 from-black/60 to-transparent"></div>

                    @if ($object->historical_images && count($object->historical_images) > 0)
                        <img src="{{ asset('storage/' . $object->historical_images[0]) }}" alt="{{ $object->name }}"
                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif

                    <div class="absolute bottom-4 left-4 right-4 z-20 text-white">
                        @if ($object->ar_marker_id || $object->model_3d_path)
                            <div
                                class="bg-primary/80 mb-2 inline-block rounded-md px-2 py-1 text-xs font-semibold backdrop-blur-sm">
                                AR Tersedia
                            </div>
                        @endif
                        <h3 class="font-playfair text-lg font-bold leading-tight md:text-xl">{{ $object->name }}</h3>
                    </div>
                </div>
                <div class="p-4 md:p-5">
                    <p class="line-clamp-2 text-sm text-gray-600 md:text-base">{!! $object->short_description !!}</p>
                </div>
            </a>

            @if ($loop->last)
                </div>
            @endif
        @empty
            <div class="py-10 text-center">
                <p class="text-gray-500">Belum ada objek budaya yang terdaftar.</p>
            </div>
        @endforelse

    </div>
@endsection
