@extends('layouts.app')
@section('title', 'Kalender Event & Budaya')
@section('header_title', 'Kalender Event')

@section('content')
<div class="px-4 py-6">
    <!-- Category Tabs/Pills -->
    <div class="flex overflow-x-auto no-scrollbar gap-2 mb-6 pb-2">
        <button class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-full shrink-0">Semua</button>
        <button class="px-4 py-2 bg-white text-charcoal border border-gray-200 text-sm font-medium rounded-full shrink-0 transition-colors active:bg-gray-50">Upacara Adat</button>
        <button class="px-4 py-2 bg-white text-charcoal border border-gray-200 text-sm font-medium rounded-full shrink-0 transition-colors active:bg-gray-50">Festival</button>
        <button class="px-4 py-2 bg-white text-charcoal border border-gray-200 text-sm font-medium rounded-full shrink-0 transition-colors active:bg-gray-50">Workshop</button>
    </div>

    <div class="mb-4">
        <h2 class="text-lg font-bold text-charcoal">Acara Mendatang</h2>
        <p class="text-xs text-gray-500 mt-1">Jangan lewatkan momen budaya yang spesial.</p>
    </div>

    <!-- Timeline Container -->
    <div class="relative pl-5 border-l-2 border-gray-200 space-y-8 mt-6">
        
        <!-- Timeline Item 1 (Upcoming) -->
        <div class="relative">
            <!-- Timeline Dot -->
            <div class="absolute -left-[27px] top-1 h-5 w-5 rounded-full bg-accent border-4 border-[#E5E3DF] shadow-sm z-10"></div>
            
            <!-- Date Highlight -->
            <div class="mb-3">
                <span class="text-sm font-bold text-accent">Besok, 15 Agustus 2026</span>
            </div>
            
            <!-- Event Card -->
            <a href="#" class="block bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-4 active:scale-[0.98] transition-transform">
                <div class="flex justify-between items-start mb-2">
                    <span class="px-2.5 py-1 bg-amber-50 text-amber-600 text-xs font-bold rounded-lg border border-amber-100">Upacara Adat</span>
                    <span class="text-xs font-bold text-primary bg-green-50 px-2 py-1 rounded-md">Gratis</span>
                </div>
                <h3 class="font-bold text-charcoal text-base mb-1">Ngusaba Kadasa</h3>
                <p class="text-sm text-gray-500 line-clamp-2 mb-4 leading-relaxed">
                    Upacara persembahan agung yang diadakan di Pura Penataran untuk memohon kesejahteraan alam, hasil panen melimpah, dan ketenteraman warga desa.
                </p>
                <div class="flex items-center text-xs text-gray-500 gap-4">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        08:00 - Selesai
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Pura Penataran
                    </div>
                </div>
            </a>
        </div>

        <!-- Timeline Item 2 -->
        <div class="relative">
            <!-- Timeline Dot -->
            <div class="absolute -left-[26px] top-1 h-4 w-4 rounded-full bg-gray-300 border-4 border-[#E5E3DF] z-10"></div>
            
            <div class="mb-3">
                <span class="text-sm font-bold text-gray-600">Sabtu, 22 Agustus 2026</span>
            </div>
            
            <!-- Event Card -->
            <a href="#" class="block bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-4 active:scale-[0.98] transition-transform">
                <div class="flex justify-between items-start mb-2">
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-lg border border-blue-100">Workshop</span>
                    <span class="text-xs font-bold text-charcoal bg-gray-100 px-2 py-1 rounded-md">Rp 50.000</span>
                </div>
                <h3 class="font-bold text-charcoal text-base mb-1">Kelas Menganyam Bambu</h3>
                <p class="text-sm text-gray-500 line-clamp-2 mb-4 leading-relaxed">
                    Pelajari teknik dasar menganyam bambu tradisional bersama para perajin lokal berpengalaman. Hasil karya bisa dibawa pulang.
                </p>
                <div class="flex items-center text-xs text-gray-500 gap-4">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        09:00 - 12:00
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Bale Banjar
                    </div>
                </div>
            </a>
        </div>
        
        <!-- Timeline Item 3 -->
        <div class="relative">
            <!-- Timeline Dot -->
            <div class="absolute -left-[26px] top-1 h-4 w-4 rounded-full bg-gray-300 border-4 border-[#E5E3DF] z-10"></div>
            
            <div class="mb-3">
                <span class="text-sm font-bold text-gray-600">Rabu, 2 September 2026</span>
            </div>
            
            <!-- Event Card -->
            <a href="#" class="block bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-4 active:scale-[0.98] transition-transform">
                <div class="flex justify-between items-start mb-2">
                    <span class="px-2.5 py-1 bg-purple-50 text-purple-600 text-xs font-bold rounded-lg border border-purple-100">Festival</span>
                    <span class="text-xs font-bold text-primary bg-green-50 px-2 py-1 rounded-md">Gratis</span>
                </div>
                <h3 class="font-bold text-charcoal text-base mb-1">Penglipuran Village Festival</h3>
                <p class="text-sm text-gray-500 line-clamp-2 mb-4 leading-relaxed">
                    Puncak acara festival budaya tahunan menampilkan parade busana adat, pameran kuliner, dan tarian sakral di sepanjang jalan utama desa.
                </p>
                <div class="flex items-center text-xs text-gray-500 gap-4">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        15:00 - 22:00
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Jalan Utama Desa
                    </div>
                </div>
            </a>
        </div>

    </div>
    
    <div class="mt-8 mb-4 text-center">
        <p class="text-xs text-gray-400">Tidak ada acara lagi di bulan ini.</p>
    </div>
</div>
@endsection
