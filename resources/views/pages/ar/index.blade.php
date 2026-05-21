@extends('layouts.app')
@section('title', 'Kamera AR - Penglipuran')

@push('styles')
    <!-- A-Frame and AR.js -->
    <script src="https://aframe.io/releases/1.4.2/aframe.min.js"></script>
    <script src="https://raw.githack.com/AR-js-org/AR.js/master/aframe/build/aframe-ar.js"></script>
    
    <style>
        /* Sembunyikan elemen bawaan AR.js yang tidak perlu */
        .a-enter-vr {
            display: none !important;
        }
        
        /* Pastikan canvas video menutupi layar sepenuhnya */
        #arjs-video {
            object-fit: cover !important;
            width: 100vw !important;
            height: 100vh !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        /* Animasi target reticle */
        @keyframes scan {
            0% { transform: translateY(-50%); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(150%); opacity: 0; }
        }
        
        .scanner-line {
            animation: scan 2s infinite linear;
        }
    </style>
@endpush

@section('content')
<!-- AR Container -->
<div class="fixed inset-0 z-50 bg-black overflow-hidden flex flex-col">
    
    <!-- A-Frame Scene (z-index 0) -->
    <div class="absolute inset-0 z-0">
        <a-scene embedded arjs="sourceType: webcam; debugUIEnabled: false; trackingMethod: best; detectionMode: mono_and_matrix; matrixCodeType: 3x3;" vr-mode-ui="enabled: false">
            <a-assets>
                <!-- Preload 3D model placeholder (can be .glb) -->
                <a-asset-item id="placeholder-model" src=""></a-asset-item>
            </a-assets>
            
            <a-marker preset="hiro" id="ar-marker">
                <!-- A basic 3D box as placeholder -->
                <a-box position="0 0.5 0" material="color: #D4AF37; opacity: 0.8;" animation="property: rotation; to: 0 360 0; loop: true; dur: 4000"></a-box>
            </a-marker>
            
            <a-entity camera></a-entity>
        </a-scene>
    </div>

    <!-- HUD Overlay (z-index 10) -->
    <div class="relative z-10 flex-1 flex flex-col pointer-events-none">
        <!-- Top Glassmorphism Bar -->
        <div class="p-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white pointer-events-auto active:scale-95 transition-all">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </a>
            
            <div class="bg-black/40 backdrop-blur-md px-4 py-2 rounded-full text-white text-sm font-medium border border-white/10">
                Arahkan ke Marker Budaya
            </div>
            
            <button class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white pointer-events-auto active:scale-95 transition-all">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </button>
        </div>

        <!-- Center Target Reticle -->
        <div class="flex-1 flex items-center justify-center">
            <div class="w-64 h-64 border-2 border-white/50 rounded-3xl relative">
                <!-- Corners -->
                <div class="absolute -top-1 -left-1 w-8 h-8 border-t-4 border-l-4 border-primary rounded-tl-3xl"></div>
                <div class="absolute -top-1 -right-1 w-8 h-8 border-t-4 border-r-4 border-primary rounded-tr-3xl"></div>
                <div class="absolute -bottom-1 -left-1 w-8 h-8 border-b-4 border-l-4 border-primary rounded-bl-3xl"></div>
                <div class="absolute -bottom-1 -right-1 w-8 h-8 border-b-4 border-r-4 border-primary rounded-br-3xl"></div>
                
                <!-- Scanning line -->
                <div class="absolute left-4 right-4 h-0.5 bg-primary/70 shadow-[0_0_8px_rgba(30,81,40,0.8)] scanner-line"></div>
            </div>
        </div>

        <!-- Bottom Controls -->
        <div class="pb-sab p-6 flex flex-col items-center gap-4">
            <!-- Loading Indicator (Hidden initially) -->
            <div id="loading-indicator" class="hidden bg-black/60 backdrop-blur-md px-5 py-3 rounded-2xl flex items-center gap-3 border border-white/10 pointer-events-auto">
                <div class="w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                <span class="text-white text-sm font-medium">Memuat Model 3D...</span>
            </div>
            
            <!-- Snapshot Button -->
            <button class="w-16 h-16 rounded-full border-4 border-white/30 p-1 pointer-events-auto active:scale-95 transition-all mb-4">
                <div class="w-full h-full bg-white rounded-full"></div>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const marker = document.querySelector('#ar-marker');
        const loadingIndicator = document.querySelector('#loading-indicator');
        
        if (marker) {
            marker.addEventListener('markerFound', function() {
                // Get haptic feedback on marker found
                if(navigator.vibrate) navigator.vibrate(100);
                
                // Show loading briefly then hide (simulating model download)
                loadingIndicator.classList.remove('hidden');
                setTimeout(() => {
                    loadingIndicator.classList.add('hidden');
                }, 1500);
            });

            marker.addEventListener('markerLost', function() {
                loadingIndicator.classList.add('hidden');
            });
        }
    });
</script>
@endpush
