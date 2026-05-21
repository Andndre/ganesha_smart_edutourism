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
            0% {
                transform: translateY(-50%);
                opacity: 0;
            }

            50% {
                opacity: 1;
            }

            100% {
                transform: translateY(150%);
                opacity: 0;
            }
        }

        .scanner-line {
            animation: scan 2s infinite linear;
        }
    </style>
@endpush

@section('content')
    <!-- AR Container -->
    <div class="fixed inset-0 z-50 flex flex-col overflow-hidden bg-black">

        <!-- A-Frame Scene (z-index 0) -->
        <div class="absolute inset-0 z-0">
            <a-scene embedded
                arjs="sourceType: webcam; debugUIEnabled: false; trackingMethod: best; detectionMode: mono_and_matrix; matrixCodeType: 3x3;"
                vr-mode-ui="enabled: false">
                <a-assets>
                    <!-- Preload 3D model placeholder (can be .glb) -->
                    <a-asset-item id="placeholder-model" src=""></a-asset-item>
                </a-assets>

                <a-marker preset="hiro" id="ar-marker">
                    <!-- A basic 3D box as placeholder -->
                    <a-box position="0 0.5 0" material="color: #D4AF37; opacity: 0.8;"
                        animation="property: rotation; to: 0 360 0; loop: true; dur: 4000"></a-box>
                </a-marker>

                <a-entity camera></a-entity>
            </a-scene>
        </div>

        <!-- HUD Overlay (z-index 10) -->
        <div class="pointer-events-none relative z-10 flex flex-1 flex-col">
            <!-- Top Glassmorphism Bar -->
            <div class="flex items-center justify-between p-4">
                <a href="{{ route('home') }}"
                    class="pointer-events-auto flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-md transition-all active:scale-95">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <div
                    class="rounded-full border border-white/10 bg-black/40 px-4 py-2 text-sm font-medium text-white backdrop-blur-md">
                    Arahkan ke Marker Budaya
                </div>

                <button
                    class="pointer-events-auto flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-md transition-all active:scale-95">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>

            <!-- Center Target Reticle -->
            <div class="flex flex-1 items-center justify-center">
                <div class="relative h-64 w-64 rounded-3xl border-2 border-white/50">
                    <!-- Corners -->
                    <div class="border-primary absolute -left-1 -top-1 h-8 w-8 rounded-tl-3xl border-l-4 border-t-4"></div>
                    <div class="border-primary absolute -right-1 -top-1 h-8 w-8 rounded-tr-3xl border-r-4 border-t-4"></div>
                    <div class="border-primary absolute -bottom-1 -left-1 h-8 w-8 rounded-bl-3xl border-b-4 border-l-4">
                    </div>
                    <div class="border-primary absolute -bottom-1 -right-1 h-8 w-8 rounded-br-3xl border-b-4 border-r-4">
                    </div>

                    <!-- Scanning line -->
                    <div
                        class="bg-primary/70 scanner-line absolute left-4 right-4 h-0.5 shadow-[0_0_8px_rgba(30,81,40,0.8)]">
                    </div>
                </div>
            </div>

            <!-- Bottom Controls -->
            <div class="pb-sab flex flex-col items-center gap-4 p-6">
                <!-- Loading Indicator (Hidden initially) -->
                <div id="loading-indicator"
                    class="pointer-events-auto hidden items-center gap-3 rounded-2xl border border-white/10 bg-black/60 px-5 py-3 backdrop-blur-md">
                    <div class="border-primary h-5 w-5 animate-spin rounded-full border-2 border-t-transparent"></div>
                    <span class="text-sm font-medium text-white">Memuat Model 3D...</span>
                </div>

                <!-- Snapshot Button -->
                <button
                    class="pointer-events-auto mb-4 h-16 w-16 rounded-full border-4 border-white/30 p-1 transition-all active:scale-95">
                    <div class="h-full w-full rounded-full bg-white"></div>
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
                    if (navigator.vibrate) navigator.vibrate(100);

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
