@extends('layouts.app')
@section('title', 'Profil & Tiket Saya')
@section('header_title', 'Profil Saya')

@section('content')
<div class="px-4 pt-[calc(env(safe-area-inset-top)+6rem)] pb-24">
    
    <!-- User Info Card -->
    <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 mb-6">
        <div class="w-16 h-16 rounded-full bg-gray-200 border-2 border-white shadow-md relative overflow-hidden flex items-center justify-center text-gray-400">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-charcoal">Andre Kusuma</h2>
            <p class="text-sm text-gray-500">andre@test.com</p>
            <div class="flex items-center gap-1 mt-1">
                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                <span class="text-xs font-semibold text-green-600">Akun Terverifikasi</span>
            </div>
        </div>
    </div>

    <h3 class="font-bold text-charcoal mb-4 text-lg">Tiket Aktif Saya</h3>
    
    <!-- Active Ticket Card -->
    <div class="bg-primary rounded-3xl p-1 shadow-lg shadow-primary/20 mb-8 active:scale-[0.98] transition-transform" onclick="openQrModal()">
        <div class="bg-white rounded-[1.35rem] overflow-hidden border border-primary/20 relative">
            
            <!-- Ticket Top (Details) -->
            <div class="p-5 relative z-10 border-b-2 border-dashed border-gray-200">
                <div class="flex justify-between items-start mb-3">
                    <span class="px-2.5 py-1 bg-green-50 text-primary text-xs font-bold rounded-lg border border-green-100">Tour Keluarga</span>
                    <span class="text-xs font-bold text-gray-400">ID: GPN-2026815</span>
                </div>
                <h3 class="font-bold text-charcoal text-lg mb-4">Penglipuran Family Walk</h3>
                
                <div class="grid grid-cols-2 gap-y-4 gap-x-2">
                    <div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Tanggal</div>
                        <div class="text-sm font-bold text-charcoal">15 Agu 2026</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Jam</div>
                        <div class="text-sm font-bold text-charcoal">09:00 WITA</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Peserta</div>
                        <div class="text-sm font-bold text-charcoal">3 Orang</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Status</div>
                        <div class="text-sm font-bold text-green-600">Lunas</div>
                    </div>
                </div>
            </div>
            
            <!-- Ticket Bottom (CTA) -->
            <div class="p-5 bg-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-lg border border-gray-200 shadow-sm flex items-center justify-center text-charcoal">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-charcoal">Ketuk untuk QR Code</div>
                        <div class="text-[10px] text-gray-500">Tunjukkan di pintu masuk</div>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>

            <!-- Left and Right cutouts (Ticket effect) -->
            <div class="absolute w-6 h-6 bg-gray-50 rounded-full -left-3 top-[67%] border border-gray-200"></div>
            <div class="absolute w-6 h-6 bg-gray-50 rounded-full -right-3 top-[67%] border border-gray-200"></div>
        </div>
    </div>

    <!-- Other Menu Options -->
    <h3 class="font-bold text-charcoal mb-4 text-lg">Pengaturan</h3>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <a href="#" class="flex items-center justify-between p-4 border-b border-gray-50 active:bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <span class="text-sm font-medium text-charcoal">Riwayat Pemesanan</span>
            </div>
            <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        
        <a href="{{ route('feedback') }}" class="flex items-center justify-between p-4 border-b border-gray-50 active:bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-50 text-accent flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <span class="text-sm font-medium text-charcoal">Beri Penilaian & Ulasan</span>
            </div>
            <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>

        <a href="#" class="flex items-center justify-between p-4 active:bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </div>
                <span class="text-sm font-medium text-red-500">Keluar (Logout)</span>
            </div>
        </a>
    </div>
</div>

<!-- QR Code Modal (Hidden by default) -->
<div id="qr-modal" class="fixed inset-0 z-[60] bg-charcoal/90 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300 flex items-center justify-center p-6">
    <div class="bg-white rounded-[2rem] w-full max-w-sm overflow-hidden shadow-2xl transform scale-95 transition-transform duration-300" id="qr-card">
        
        <div class="p-6 pb-2 text-center relative">
            <button onclick="closeQrModal()" class="absolute right-4 top-4 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 active:bg-gray-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            
            <h3 class="text-xl font-bold text-charcoal mb-1">Tiket Masuk</h3>
            <p class="text-xs text-gray-500">Pindai QR ini di gerbang utama</p>
        </div>
        
        <div class="p-8 flex justify-center">
            <!-- Simulated QR Code -->
            <div class="w-full aspect-square bg-white border-8 border-white shadow-[0_0_15px_rgba(0,0,0,0.1)] rounded-2xl relative">
                <!-- Outer squares -->
                <div class="absolute top-2 left-2 w-12 h-12 border-4 border-charcoal"></div>
                <div class="absolute top-4 left-4 w-8 h-8 bg-charcoal"></div>
                
                <div class="absolute top-2 right-2 w-12 h-12 border-4 border-charcoal"></div>
                <div class="absolute top-4 right-4 w-8 h-8 bg-charcoal"></div>
                
                <div class="absolute bottom-2 left-2 w-12 h-12 border-4 border-charcoal"></div>
                <div class="absolute bottom-4 left-4 w-8 h-8 bg-charcoal"></div>
                
                <!-- Random QR blocks (simulated) -->
                <div class="absolute top-[40%] left-[30%] w-6 h-6 bg-charcoal"></div>
                <div class="absolute top-[60%] right-[30%] w-8 h-4 bg-charcoal"></div>
                <div class="absolute bottom-[20%] right-[20%] w-10 h-10 bg-charcoal"></div>
                <div class="absolute top-[20%] left-[50%] w-4 h-12 bg-charcoal"></div>
                
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-md">
                        <span class="text-primary font-bold text-[10px]">GPN</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-6 text-center border-t border-gray-100">
            <div class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">ID Pemesanan</div>
            <div class="text-lg font-mono font-bold text-charcoal tracking-[0.2em]">GPN-2026815</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openQrModal() {
        const modal = document.getElementById('qr-modal');
        const card = document.getElementById('qr-card');
        
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
        
        card.classList.remove('scale-95');
        card.classList.add('scale-100');
        
        // Maximize screen brightness hack (simulated via haptic)
        if(navigator.vibrate) navigator.vibrate(50);
    }
    
    function closeQrModal() {
        const modal = document.getElementById('qr-modal');
        const card = document.getElementById('qr-card');
        
        modal.classList.remove('opacity-100', 'pointer-events-auto');
        modal.classList.add('opacity-0', 'pointer-events-none');
        
        card.classList.remove('scale-100');
        card.classList.add('scale-95');
    }
</script>
@endpush
