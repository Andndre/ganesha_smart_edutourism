@extends('layouts.app')
@section('title', 'Detail Paket - Penglipuran')
@section('header_title', 'Reservasi Paket')

@section('content')
<div class="relative pb-32">
    <!-- Image Header -->
    <div class="w-full aspect-video bg-gray-200 relative">
        <div class="absolute inset-0 flex items-center justify-center text-gray-400">
            <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <!-- Back Button Overlay (if not using header, but we are using header) -->
    </div>
    
    <!-- Package Details -->
    <div class="px-5 py-6 bg-white -mt-6 rounded-t-3xl relative z-10 border-b border-gray-100 shadow-sm">
        <div class="mb-2">
            <span class="px-2.5 py-1 bg-green-50 text-primary text-xs font-bold rounded-lg border border-green-100 mb-3 inline-block">Paket Keluarga</span>
            <h1 class="text-xl font-bold text-charcoal">Penglipuran Family Walk</h1>
        </div>
        
        <p class="text-sm text-gray-500 leading-relaxed mb-4">
            Tour keliling desa wisata yang ramah anak, dilengkapi dengan sesi belajar membuat kerajinan janur Bali bersama masyarakat lokal. Sempurna untuk liburan akhir pekan.
        </p>

        <div class="grid grid-cols-2 gap-3 mb-2">
            <div class="bg-gray-50 p-3 rounded-2xl flex flex-col items-center justify-center border border-gray-100">
                <svg class="w-6 h-6 text-accent mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-xs text-gray-500 font-medium">Durasi</span>
                <span class="text-sm font-bold text-charcoal">2 Jam</span>
            </div>
            <div class="bg-gray-50 p-3 rounded-2xl flex flex-col items-center justify-center border border-gray-100">
                <svg class="w-6 h-6 text-accent mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span class="text-xs text-gray-500 font-medium">Fasilitas</span>
                <span class="text-sm font-bold text-charcoal">Guide, Janur</span>
            </div>
        </div>
    </div>
    
    <!-- Reservation Form -->
    <div class="px-5 py-6 bg-white mt-2 border-y border-gray-100">
        <h3 class="font-bold text-charcoal mb-4">Pilih Tanggal Tour</h3>
        
        <!-- Date Scroller (Horizontal Chips) -->
        <div class="flex overflow-x-auto no-scrollbar gap-3 pb-2 -mx-5 px-5">
            <!-- Active Date -->
            <label class="relative flex flex-col items-center p-3 rounded-2xl border-2 border-primary bg-green-50 min-w-[70px] shrink-0 cursor-pointer">
                <input type="radio" name="tour_date" value="today" class="absolute opacity-0" checked>
                <span class="text-xs font-bold text-primary mb-1">Hari Ini</span>
                <span class="text-xl font-bold text-charcoal">15</span>
                <span class="text-xs text-gray-500 font-medium">Agu</span>
            </label>
            
            <!-- Inactive Date 1 -->
            <label class="relative flex flex-col items-center p-3 rounded-2xl border border-gray-200 bg-white min-w-[70px] shrink-0 cursor-pointer transition-colors hover:border-gray-300">
                <input type="radio" name="tour_date" value="tomorrow" class="absolute opacity-0">
                <span class="text-xs font-semibold text-gray-400 mb-1">Besok</span>
                <span class="text-xl font-bold text-charcoal">16</span>
                <span class="text-xs text-gray-500 font-medium">Agu</span>
            </label>
            
            <!-- Inactive Date 2 -->
            <label class="relative flex flex-col items-center p-3 rounded-2xl border border-gray-200 bg-white min-w-[70px] shrink-0 cursor-pointer transition-colors hover:border-gray-300">
                <input type="radio" name="tour_date" value="next1" class="absolute opacity-0">
                <span class="text-xs font-semibold text-gray-400 mb-1">Senin</span>
                <span class="text-xl font-bold text-charcoal">17</span>
                <span class="text-xs text-gray-500 font-medium">Agu</span>
            </label>
            
            <!-- Custom Date Selector -->
            <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border border-dashed border-gray-300 bg-gray-50 min-w-[70px] shrink-0 cursor-pointer">
                <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-xs font-semibold text-gray-500">Lainnya</span>
            </label>
        </div>
        
        <!-- Party Size Stepper -->
        <h3 class="font-bold text-charcoal mt-6 mb-4">Jumlah Peserta (Party Size)</h3>
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
            <div>
                <div class="font-bold text-charcoal">Peserta Dewasa / Anak</div>
                <div class="text-xs text-gray-500 mt-0.5">Minimal 3 Orang</div>
            </div>
            
            <div class="flex items-center gap-4 bg-white px-2 py-1.5 rounded-full border border-gray-200 shadow-sm">
                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-500 active:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </button>
                <span class="font-bold text-lg text-charcoal w-4 text-center">3</span>
                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-primary/10 text-primary active:bg-primary/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>
        
        <!-- Contact Info -->
        <h3 class="font-bold text-charcoal mt-6 mb-4">Informasi Kontak Pemesan</h3>
        <div class="space-y-3">
            <input type="text" placeholder="Nama Lengkap" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
            <input type="email" placeholder="Alamat Email (Untuk E-Ticket)" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
            <input type="tel" placeholder="Nomor WhatsApp aktif" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
        </div>
    </div>
</div>

<!-- Sticky Bottom Checkout Action (Posisi di atas Bottom Nav) -->
<div class="fixed bottom-[calc(4rem+env(safe-area-inset-bottom))] inset-x-0 p-4 bg-white border-t border-gray-100 z-30 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
    <div class="flex items-center justify-between mb-3 px-1">
        <span class="text-sm text-gray-500 font-medium">Total Harga (3 Pax)</span>
        <span class="text-lg font-bold text-primary">Rp 450.000</span>
    </div>
    <button class="w-full bg-primary text-white font-bold h-14 rounded-2xl active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/30" onclick="if(navigator.vibrate) navigator.vibrate(50)">
        Lanjutkan ke Pembayaran
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
    </button>
</div>
@endsection
