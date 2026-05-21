@extends('layouts.app')
@section('title', 'Paket Wisata - Penglipuran')
@section('header_title', 'Paket Wisata')

@section('content')
<div class="px-4 py-6">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-charcoal">Eksplorasi Bersama</h2>
        <p class="text-sm text-gray-500 mt-1">Pilih paket wisata yang sesuai dengan durasi dan preferensi perjalanan Anda.</p>
    </div>

    <div class="space-y-4">
        <!-- Package 1: Family -->
        <a href="{{ route('tour-package', 1) }}" class="block bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden active:scale-[0.98] transition-all">
            <div class="aspect-video bg-gray-200 relative">
                <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                    <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="absolute top-3 left-3 bg-white/90 backdrop-blur text-primary text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
                    Paket Keluarga
                </div>
            </div>
            <div class="p-4">
                <h3 class="font-bold text-charcoal text-lg mb-1">Penglipuran Family Walk</h3>
                <p class="text-sm text-gray-500 mb-3 line-clamp-2">Tour keliling desa wisata yang ramah anak, dilengkapi dengan sesi belajar membuat kerajinan janur Bali.</p>
                
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-50">
                    <div class="flex items-center gap-4 text-xs font-semibold text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            2 Jam
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Min. 3 Orang
                        </div>
                    </div>
                    <div class="text-lg font-bold text-primary">Rp 150k<span class="text-xs text-gray-400 font-normal">/pax</span></div>
                </div>
            </div>
        </a>

        <!-- Package 2: Edutourism -->
        <a href="{{ route('tour-package', 2) }}" class="block bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden active:scale-[0.98] transition-all">
            <div class="aspect-[16/9] bg-gray-200 relative">
                <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                    <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="absolute top-3 left-3 bg-white/90 backdrop-blur text-primary text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
                    Edutourism Eksklusif
                </div>
            </div>
            <div class="p-4">
                <h3 class="font-bold text-charcoal text-lg mb-1">Smart Culture & Heritage</h3>
                <p class="text-sm text-gray-500 mb-3 line-clamp-2">Paket terlengkap didampingi pemandu lokal (Prajuru Desa). Eksplorasi mendalam arsitektur dan filosofi Karang Memadu.</p>
                
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-50">
                    <div class="flex items-center gap-4 text-xs font-semibold text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            4 Jam
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Min. 2 Orang
                        </div>
                    </div>
                    <div class="text-lg font-bold text-primary">Rp 250k<span class="text-xs text-gray-400 font-normal">/pax</span></div>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
