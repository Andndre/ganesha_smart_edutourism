@extends('layouts.scanner')

@section('title', 'View 3D Model')

@section('content')
    <!-- Simple Back Button (no exit confirmation) -->
    <button id="btn-back-viewer"
        class="absolute left-4 top-6 z-50 flex h-10 w-10 items-center justify-center rounded-full bg-black/40 text-white shadow-lg backdrop-blur-md transition-transform hover:bg-black/60 active:scale-95"
        onclick="history.back()">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </button>

    <!-- 3D Model Viewer (always visible, no scanner step) -->
    @include('user.ar.partials.model-view')

    <!-- Loading Overlay -->
    @include('user.ar.partials.loading-overlay')
@endsection

@push('scripts')
    <!-- Model data from server (embedded JSON) -->
    <script id="model-data" type="application/json">{{ json_encode($model) }}</script>

    <!-- Model Viewer Configuration -->
    <!-- Resolve Blade routes to JS vars before inline script -->
    @php
        $arScanUrl = route('ar-scan');
        $modelData = json_decode(json_encode($model), true);
        $usdzPath = $modelData['model_3d_usdz_path'] ?? null;
        $usdzUrl = $usdzPath ? route('usdz.serve', ['path' => basename($usdzPath)]) : null;
    @endphp
    <script>
        window.AR_MESSAGES = {
            touchToRotate: "{{ __('Sentuh untuk memutar/zoom') }}",
            downloadingModel: "{{ __('Mengunduh Model...') }}"
        };
        self.ModelViewerElement = self.ModelViewerElement || {};
        self.ModelViewerElement.meshoptDecoderLocation = 'https://cdn.jsdelivr.net/npm/meshoptimizer/meshopt_decoder.js';
    </script>
    <script type="module" src="{{ asset('js/model-viewer.min.js') }}"></script>

    <!-- Viewer Initialization -->
    <script>
        (function() {
            'use strict';

            var arScanUrl = '{{ $arScanUrl }}';
            var usdzUrl = '{{ $usdzUrl }}';
            var modelData = JSON.parse(document.getElementById('model-data').textContent);
            var modelView = document.getElementById('model-view');
            var viewer = document.getElementById('ar-model-viewer');
            var loadingOverlay = document.getElementById('loading-overlay');
            var modelTitle = document.getElementById('model-title');
            var modelDescFull = document.getElementById('model-desc-full');

            // Show model view immediately (no scanner step)
            modelView.classList.remove('hidden');

            // Show loading overlay while model loads
            if (loadingOverlay) {
                loadingOverlay.classList.remove('hidden');
                loadingOverlay.classList.add('flex');
            }

            // Set 3D model source
            if (modelData.model_3d_path) {
                viewer.setAttribute('src', '/storage/' + modelData.model_3d_path);
            }

            // Set USDZ source for iOS AR Quick Look
            if (usdzUrl && modelData.model_3d_usdz_path) {
                viewer.setAttribute('ios-src', usdzUrl);
            }

            // Update bottom sheet with model info
            if (modelTitle) {
                modelTitle.textContent = modelData.resolved_name || modelData.name || '';
            }

            if (modelDescFull) {
                var desc = modelData.resolved_description || modelData.description || '';
                if (typeof desc === 'object') {
                    desc = desc.en || desc.id || '';
                }
                modelDescFull.innerHTML = desc;
            }

            // Audio narration setup
            var audioEl = document.getElementById('ar-audio');
            var btnAudio = document.getElementById('btn-audio-toggle');
            var iconPlay = document.getElementById('audio-icon-play');
            var iconPause = document.getElementById('audio-icon-pause');

            if (modelData.audio_narration_path && audioEl && btnAudio) {
                audioEl.src = '/storage/' + modelData.audio_narration_path;
                audioEl.preload = 'auto';
                btnAudio.classList.remove('hidden');
                btnAudio.classList.add('flex');

                audioEl.onended = function() {
                    iconPlay.classList.remove('hidden');
                    iconPause.classList.add('hidden');
                };

                btnAudio.onclick = function() {
                    if (audioEl.paused) {
                        audioEl.play().catch(function() {});
                        iconPlay.classList.add('hidden');
                        iconPause.classList.remove('hidden');
                    } else {
                        audioEl.pause();
                        iconPlay.classList.remove('hidden');
                        iconPause.classList.add('hidden');
                    }
                };

                // Attempt autoplay (may be blocked by browser policy)
                audioEl.play().then(function() {
                    iconPlay.classList.add('hidden');
                    iconPause.classList.remove('hidden');
                }).catch(function() {});
            }

            // Hide loading overlay when model finishes loading
            viewer.addEventListener('load', function() {
                if (loadingOverlay) {
                    loadingOverlay.classList.add('hidden');
                    loadingOverlay.classList.remove('flex');
                }
                onModelReady();
            });

            // Fallback: hide loading after timeout
            setTimeout(function() {
                if (loadingOverlay && !loadingOverlay.classList.contains('hidden')) {
                    loadingOverlay.classList.add('hidden');
                    loadingOverlay.classList.remove('flex');
                    onModelReady();
                }
            }, 10000);

            // "Scan Again" button navigates to AR scan page
            var scanAgainBtn = document.getElementById('btn-scan-again');
            if (scanAgainBtn) {
                scanAgainBtn.addEventListener('click', function() {
                    window.location.href = arScanUrl;
                });
            }

            // Bottom sheet toggle
            var sheet = document.getElementById('desc-bottom-sheet');
            var sheetHeader = document.getElementById('sheet-header');
            var sheetBackdrop = document.getElementById('sheet-backdrop');
            var sheetArrow = document.getElementById('sheet-arrow');
            var sheetExpanded = false;

            function toggleSheet() {
                sheetExpanded = !sheetExpanded;

                if (sheetExpanded) {
                    sheet.classList.remove('translate-y-[calc(100%-100px)]');
                    sheet.classList.add('translate-y-0');

                    sheetBackdrop.style.display = 'block';
                    void sheetBackdrop.offsetWidth;
                    sheetBackdrop.classList.remove('opacity-0');
                    sheetBackdrop.classList.add('opacity-100');

                    if (sheetArrow) sheetArrow.classList.add('rotate-180');
                } else {
                    sheet.classList.add('translate-y-[calc(100%-100px)]');
                    sheet.classList.remove('translate-y-0');

                    sheetBackdrop.classList.remove('opacity-100');
                    sheetBackdrop.classList.add('opacity-0');

                    if (sheetArrow) sheetArrow.classList.remove('rotate-180');

                    setTimeout(function() {
                        if (!sheetExpanded) {
                            sheetBackdrop.style.display = 'none';
                        }
                    }, 300);
                }
            }

            if (sheetHeader) {
                sheetHeader.addEventListener('click', toggleSheet);
            }
            if (sheetBackdrop) {
                sheetBackdrop.addEventListener('click', toggleSheet);
            }

            window.closeBottomSheet = function() {
                if (sheetExpanded) toggleSheet();
            };

            function onModelReady() {
                checkIOSInAppBrowser();
            }

            function checkIOSInAppBrowser() {
                var ua = navigator.userAgent || navigator.vendor || window.opera;
                var isIOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
                if (!isIOS) return;

                var inAppRules = ['FBAV', 'FBAN', 'Instagram', 'Line', 'WhatsApp', 'Snapchat', 'TikTok'];
                var isIAB = inAppRules.some(function(rule) {
                    return new RegExp(rule, 'i').test(ua);
                });

                var isSafari = /Safari/i.test(ua) && !/Chrome|CriOS/i.test(ua);

                if (isIAB || !isSafari) {
                    var warningEl = document.getElementById('iab-warning');
                    if (warningEl) {
                        warningEl.classList.remove('hidden');
                        warningEl.classList.add('flex');
                    }
                }
            }
        })();
    </script>
@endpush
