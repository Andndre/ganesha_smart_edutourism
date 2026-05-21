@extends('layouts.app')
@section('title', 'Objek Budaya - Penglipuran')
@section('header_title', 'Objek Budaya')

@section('content')
<div class="px-4 py-6 space-y-5">
    
    <div class="mb-2">
        <h2 class="text-xl font-bold font-playfair text-charcoal">Jelajah Warisan Budaya</h2>
        <p class="text-sm text-gray-500 mt-1">Temukan kisah di balik setiap sudut desa</p>
    </div>

    <!-- Card 1 -->
    <a href="{{ route('cultural-object', 1) }}" class="block bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden active:scale-[0.98] transition-all">
        <div class="h-48 bg-gray-200 relative">
            <div class="absolute inset-0 bg-linear-to-t from-black/60 to-transparent"></div>
            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div class="absolute bottom-4 left-4 right-4 text-white">
                <div class="text-xs font-semibold px-2 py-1 bg-primary/80 rounded-md inline-block mb-2 backdrop-blur-sm">AR Tersedia</div>
                <h3 class="text-lg font-bold font-playfair leading-tight">Pura Penataran Desa</h3>
            </div>
        </div>
        <div class="p-4">
            <p class="text-sm text-gray-600 line-clamp-2">Pusat spiritual utama di Desa Penglipuran, tempat di mana harmoni antara manusia dan Tuhan dirawat dengan sakral sejak berabad-abad.</p>
        </div>
    </a>

    <!-- Card 2 -->
    <a href="{{ route('cultural-object', 2) }}" class="block bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden active:scale-[0.98] transition-all">
        <div class="h-48 bg-gray-200 relative">
            <div class="absolute inset-0 bg-linear-to-t from-black/60 to-transparent"></div>
            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div class="absolute bottom-4 left-4 right-4 text-white">
                <h3 class="text-lg font-bold font-playfair leading-tight">Hutan Bambu Sakral</h3>
            </div>
        </div>
        <div class="p-4">
            <p class="text-sm text-gray-600 line-clamp-2">Paru-paru desa seluas 45 hektar yang melambangkan keseimbangan alam dan etika pelestarian lingkungan masyarakat adat.</p>
        </div>
    </a>

    <!-- Card 3 -->
    <a href="{{ route('cultural-object', 3) }}" class="block bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden active:scale-[0.98] transition-all">
        <div class="h-48 bg-gray-200 relative">
            <div class="absolute inset-0 bg-linear-to-t from-black/60 to-transparent"></div>
            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div class="absolute bottom-4 left-4 right-4 text-white">
                <h3 class="text-lg font-bold font-playfair leading-tight">Arsitektur Karang Memadu</h3>
            </div>
        </div>
        <div class="p-4">
            <p class="text-sm text-gray-600 line-clamp-2">Aturan adat yang unik terkait tata letak dan struktur perumahan yang membedakan Penglipuran dari desa Bali lainnya.</p>
        </div>
    </a>

</div>
@endsection
