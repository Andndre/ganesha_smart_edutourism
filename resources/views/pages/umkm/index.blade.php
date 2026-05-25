@extends('layouts.app')
@section('title', 'Katalog UMKM - Penglipuran')
@section('header_title', 'Katalog UMKM')

@section('content')
    <div class="px-4 pt-[calc(env(safe-area-inset-top)+6rem)] pb-6">
        <!-- Category Tabs/Pills -->
        <div class="flex overflow-x-auto no-scrollbar gap-2 mb-6 pb-2">
            <button class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-full shrink-0">Semua</button>
            <button
                class="px-4 py-2 bg-white text-charcoal border border-gray-200 text-sm font-medium rounded-full shrink-0">Kuliner</button>
            <button
                class="px-4 py-2 bg-white text-charcoal border border-gray-200 text-sm font-medium rounded-full shrink-0">Kerajinan</button>
            <button
                class="px-4 py-2 bg-white text-charcoal border border-gray-200 text-sm font-medium rounded-full shrink-0">Kopi</button>
            <button
                class="px-4 py-2 bg-white text-charcoal border border-gray-200 text-sm font-medium rounded-full shrink-0">Oleh-oleh</button>
        </div>

        <!-- Product Grid -->
        <div class="grid grid-cols-2 gap-4">
            <!-- Card 1 -->
            <a href="{{ route('umkm-product', 1) }}"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col active:scale-[0.98] transition-all">
                <div class="aspect-square bg-gray-200 relative">
                    <!-- placeholder image -->
                    <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="p-3 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-charcoal line-clamp-2">Kopi Luwak Penglipuran Asli</h3>
                        <p class="text-xs text-gray-500 mt-1">Warung Pak Wayan</p>
                    </div>
                    <div class="mt-3 font-semibold text-primary">Rp 50.000</div>
                </div>
            </a>

            <!-- Card 2 -->
            <a href="{{ route('umkm-product', 2) }}"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col active:scale-[0.98] transition-all">
                <div class="aspect-square bg-gray-200 relative">
                    <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="p-3 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-charcoal line-clamp-2">Loloh Cemcem Segar</h3>
                        <p class="text-xs text-gray-500 mt-1">Ibu Nyoman</p>
                    </div>
                    <div class="mt-3 font-semibold text-primary">Rp 15.000</div>
                </div>
            </a>

            <!-- Card 3 -->
            <a href="{{ route('umkm-product', 3) }}"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col active:scale-[0.98] transition-all">
                <div class="aspect-square bg-gray-200 relative">
                    <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="p-3 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-charcoal line-clamp-2">Anyaman Bambu Tradisional</h3>
                        <p class="text-xs text-gray-500 mt-1">Kelompok Kerajinan</p>
                    </div>
                    <div class="mt-3 font-semibold text-primary">Rp 120.000</div>
                </div>
            </a>

            <!-- Card 4 -->
            <a href="{{ route('umkm-product', 4) }}"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col active:scale-[0.98] transition-all">
                <div class="aspect-square bg-gray-200 relative">
                    <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="p-3 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-charcoal line-clamp-2">Kain Tenun Khas Bali</h3>
                        <p class="text-xs text-gray-500 mt-1">Galeri Tenun</p>
                    </div>
                    <div class="mt-3 font-semibold text-primary">Rp 350.000</div>
                </div>
            </a>
        </div>
    </div>
@endsection