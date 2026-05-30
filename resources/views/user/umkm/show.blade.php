@extends('layouts.app')
@section('title', 'Detail Produk - Penglipuran')
@section('header_title', 'Detail Produk')

@section('content')
    <div class="relative pb-32">
        <!-- Image -->
        <div class="w-full aspect-square bg-gray-200 relative">
            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Info -->
        <div class="px-4 py-6 bg-white -mt-4 rounded-t-3xl relative z-10 border-b border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h1 class="text-xl font-bold text-charcoal">Kopi Luwak Penglipuran Asli</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-accent flex items-center text-sm">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            4.9
                        </span>
                        <span class="text-gray-400 text-sm">•</span>
                        <span class="text-gray-500 text-sm">Terjual 120+</span>
                    </div>
                </div>
                <button class="p-2 bg-gray-50 rounded-full text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>
            </div>
            <div class="text-2xl font-bold text-primary mt-3 mb-1">Rp 50.000</div>
        </div>

        <!-- Store Info -->
        <div class="px-4 py-4 bg-white mt-2 border-y border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-charcoal">Warung Pak Wayan</h3>
                    <p class="text-xs text-gray-500">Jalan Utama, Area Selatan</p>
                </div>
            </div>
            <button
                class="px-4 py-1.5 border border-primary text-primary text-sm font-medium rounded-full">Kunjungi</button>
        </div>

        <!-- Description -->
        <div class="px-4 py-6 bg-white mt-2 border-t border-gray-100">
            <h3 class="font-bold text-charcoal mb-3">Deskripsi Produk</h3>
            <p class="text-sm text-gray-600 leading-relaxed">
                Kopi Luwak khas Desa Wisata Penglipuran, diproses secara tradisional menggunakan metode sangrai manual.
                Menghasilkan aroma kopi yang kuat dengan tingkat keasaman rendah yang nyaman di lambung. Biji kopi dipilih
                dari perkebunan lokal Bali yang terjaga kualitasnya.
            </p>
            <p class="text-sm text-gray-600 leading-relaxed mt-2">
                Sangat cocok dinikmati saat bersantai menikmati suasana desa atau sebagai oleh-oleh eksklusif.
            </p>
        </div>
    </div>

    <!-- Sticky Bottom CTA -->
    <div
        class="fixed bottom-[calc(env(safe-area-inset-bottom)+3.5rem)] inset-x-0 p-4 bg-white border-t border-gray-100 z-30 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
        <div class="flex items-center gap-3">
            <button class="w-12 h-12 border border-gray-200 rounded-xl flex items-center justify-center text-charcoal">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </button>
            <button class="flex-1 bg-primary text-white font-bold h-12 rounded-xl active:scale-[0.98] transition-all"
                onclick="if(navigator.vibrate) navigator.vibrate(50)">
                Beli Sekarang
            </button>
        </div>
    </div>
@endsection