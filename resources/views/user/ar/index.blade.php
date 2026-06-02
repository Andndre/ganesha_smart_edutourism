@extends('layouts.app')
@section('title', 'AR Scanner - Penglipuran')

@push('styles')
    <style>
        /* Sembunyikan pesan html5-qrcode bawaan */
        #reader__dashboard_section_csr span {
            color: white;
            font-size: 14px;
        }

        #reader {
            width: 100%;
            height: 100%;
            border: none;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #reader video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
            min-width: 100% !important;
            min-height: 100% !important;
            display: block !important;
        }

        /* Model Viewer Styles */
        model-viewer {
            width: 100%;
            height: 100%;
            background-color: transparent;
            --poster-color: transparent;
        }
    </style>
@endpush

@section('content')
    <!-- Container Utama -->
    <div class="fixed inset-0 z-50 flex flex-col overflow-hidden bg-black">

        <!-- Header -->
        <div class="pointer-events-none absolute left-0 right-0 top-0 z-20 flex items-center justify-between p-4 pt-[max(env(safe-area-inset-top),1rem)]">
            <a href="{{ route('home') }}"
                class="pointer-events-auto flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-md transition-all active:scale-95">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <div id="status-badge"
                class="rounded-full border border-white/10 bg-black/40 px-4 py-2 text-sm font-medium text-white backdrop-blur-md transition-all">
                Arahkan ke Marker QR
            </div>

            <div class="w-10"></div>
        </div>

        <!-- 1. Scanner View -->
        <div id="scanner-view" class="absolute inset-0 z-0">
            <div id="reader"></div>
            <!-- Overlay Reticle -->
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                <div class="relative h-64 w-64 rounded-3xl border-2 border-white/50">
                    <div class="border-primary absolute -left-1 -top-1 h-8 w-8 rounded-tl-3xl border-l-4 border-t-4"></div>
                    <div class="border-primary absolute -right-1 -top-1 h-8 w-8 rounded-tr-3xl border-r-4 border-t-4"></div>
                    <div class="border-primary absolute -bottom-1 -left-1 h-8 w-8 rounded-bl-3xl border-b-4 border-l-4"></div>
                    <div class="border-primary absolute -bottom-1 -right-1 h-8 w-8 rounded-br-3xl border-b-4 border-r-4"></div>
                </div>
            </div>
        </div>

        <!-- 2. 3D Model View (Hidden Initially) -->
        <div id="model-view" class="absolute inset-0 z-10 hidden bg-charcoal">
            <model-viewer id="ar-model-viewer" 
                src="" 
                alt="3D Model"
                camera-controls 
                auto-rotate 
                ar 
                ar-scale="auto"
                ar-placement="floor"
                bounds="tight"
                ar-modes="scene-viewer quick-look webxr"
                environment-image="neutral" 
                exposure="1" 
                shadow-intensity="1"
                shadow-softness="1">
                
                <!-- Custom AR Button -->
                <button slot="ar-button" class="pointer-events-auto absolute bottom-12 left-1/2 -translate-x-1/2 flex items-center gap-2 rounded-2xl bg-primary px-6 py-4 font-bold text-white shadow-xl shadow-primary/30 transition-all active:scale-95">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Letakkan di Lantai (AR)
                </button>
            </model-viewer>

            <!-- Metadata Overlay -->
            <div class="pointer-events-none absolute bottom-32 left-0 right-0 p-6 text-center">
                <h2 id="model-title" class="text-2xl font-bold text-white drop-shadow-md">Memuat...</h2>
                <p id="model-desc" class="text-sm text-gray-300 mt-2 line-clamp-2 drop-shadow-md"></p>
            </div>

            <!-- Back to Scanner Button -->
            <button id="btn-back-scanner" class="pointer-events-auto absolute bottom-8 left-6 flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur-md transition-all active:scale-95 border border-white/20">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>

        <!-- Loading Overlay -->
        <div id="loading-overlay" class="absolute inset-0 z-30 hidden items-center justify-center bg-black/80 backdrop-blur-sm transition-all">
            <div class="flex flex-col items-center gap-4">
                <div class="border-primary h-12 w-12 animate-spin rounded-full border-4 border-t-transparent shadow-lg shadow-primary/20"></div>
                <span class="text-base font-medium text-white tracking-wide">Mengunduh Model 3D...</span>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <!-- HTML5 QR Code -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <!-- Model Viewer & Meshopt Decoder -->
    <script>
        self.ModelViewerElement = self.ModelViewerElement || {};
        self.ModelViewerElement.meshoptDecoderLocation = 'https://cdn.jsdelivr.net/npm/meshoptimizer/meshopt_decoder.js';
    </script>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.4.0/model-viewer.min.js"></script>

    <script>
        // ========================================
        // ON-SCREEN DEBUG PANEL (for iOS Safari)
        // ========================================
        (function() {
            const panel = document.createElement('div');
            panel.id = 'debug-panel';
            panel.style.cssText = 'position:fixed;bottom:0;left:0;right:0;z-index:9999;max-height:35vh;overflow-y:auto;background:rgba(0,0,0,0.85);color:#0f0;font:11px/1.4 monospace;padding:8px 10px;pointer-events:auto;-webkit-overflow-scrolling:touch;';
            
            const header = document.createElement('div');
            header.style.cssText = 'display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;position:sticky;top:0;background:rgba(0,0,0,0.95);padding:2px 0;';
            header.innerHTML = '<b style="color:#0ff;">🐛 DEBUG LOG (iOS)</b>';
            
            const clearBtn = document.createElement('button');
            clearBtn.textContent = 'Clear';
            clearBtn.style.cssText = 'background:#333;color:#fff;border:1px solid #555;border-radius:4px;padding:2px 8px;font-size:10px;';
            clearBtn.onclick = () => { logContainer.innerHTML = ''; };
            header.appendChild(clearBtn);
            
            const logContainer = document.createElement('div');
            logContainer.id = 'debug-log';
            
            panel.appendChild(header);
            panel.appendChild(logContainer);
            document.body.appendChild(panel);
            
            let logCount = 0;
            
            function addLog(type, args) {
                logCount++;
                const line = document.createElement('div');
                line.style.cssText = 'border-bottom:1px solid #222;padding:2px 0;word-break:break-all;';
                
                const colors = { log: '#0f0', warn: '#ff0', error: '#f44', info: '#0af' };
                const prefix = { log: '📗', warn: '⚠️', error: '❌', info: 'ℹ️' };
                
                const timestamp = new Date().toLocaleTimeString('en-GB');
                const message = Array.from(args).map(a => {
                    if (a === null) return 'null';
                    if (a === undefined) return 'undefined';
                    if (typeof a === 'object') {
                        try { return JSON.stringify(a, null, 0).substring(0, 300); }
                        catch(e) { return String(a); }
                    }
                    return String(a);
                }).join(' ');
                
                line.innerHTML = `<span style="color:#888;">${logCount} [${timestamp}]</span> ${prefix[type] || ''} <span style="color:${colors[type] || '#fff'};">${message}</span>`;
                logContainer.appendChild(line);
                panel.scrollTop = panel.scrollHeight;
            }
            
            // Intercept console methods
            const origLog = console.log;
            const origWarn = console.warn;
            const origError = console.error;
            const origInfo = console.info;
            
            console.log = function() { origLog.apply(console, arguments); addLog('log', arguments); };
            console.warn = function() { origWarn.apply(console, arguments); addLog('warn', arguments); };
            console.error = function() { origError.apply(console, arguments); addLog('error', arguments); };
            console.info = function() { origInfo.apply(console, arguments); addLog('info', arguments); };
            
            // Catch unhandled errors
            window.addEventListener('error', (e) => {
                addLog('error', ['UNCAUGHT:', e.message, 'at', e.filename + ':' + e.lineno]);
            });
            window.addEventListener('unhandledrejection', (e) => {
                addLog('error', ['UNHANDLED PROMISE:', e.reason]);
            });
        })();

        // ========================================
        // AR SCANNER LOGIC
        // ========================================
        let html5QrcodeScanner = null;
        let isProcessing = false;
        let scanFailCount = 0;

        console.info('=== AR Scanner Page Loaded ===');
        console.info('User Agent: ' + navigator.userAgent);
        console.info('Protocol: ' + window.location.protocol);
        console.info('Host: ' + window.location.host);
        console.info('Secure Context: ' + window.isSecureContext);
        console.info('getUserMedia support: ' + !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia));

        document.addEventListener('DOMContentLoaded', function () {
            console.log('DOM Ready - starting init...');
            
            // Log available camera devices
            if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
                navigator.mediaDevices.enumerateDevices()
                    .then(devices => {
                        const cameras = devices.filter(d => d.kind === 'videoinput');
                        console.info('Cameras found: ' + cameras.length);
                        cameras.forEach((cam, i) => {
                            console.info('  Camera ' + i + ': ' + (cam.label || '(no label - permission pending)') + ' id=' + cam.deviceId.substring(0, 8) + '...');
                        });
                    })
                    .catch(err => console.error('enumerateDevices error:', err.message));
            } else {
                console.warn('enumerateDevices NOT available');
            }

            initScanner();

            document.getElementById('btn-back-scanner').addEventListener('click', () => {
                showScanner();
            });

            // Tangani error dari model-viewer
            const viewer = document.getElementById('ar-model-viewer');
            viewer.addEventListener('error', (event) => {
                console.error("ModelViewer Error:", event);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Model',
                    text: 'Terjadi kesalahan saat memuat model 3D. Format tidak didukung atau file tidak ditemukan.',
                    confirmButtonColor: '#1E5128'
                }).then(() => {
                    showScanner();
                });
            });
        });

        function initScanner() {
            console.log('initScanner() called');
            
            // Check native BarcodeDetector support
            const hasBarcodeDetector = 'BarcodeDetector' in window;
            console.info('Native BarcodeDetector API: ' + (hasBarcodeDetector ? 'AVAILABLE ✅' : 'NOT available (will use JS fallback)'));
            
            if (hasBarcodeDetector) {
                BarcodeDetector.getSupportedFormats().then(formats => {
                    console.info('Supported barcode formats: ' + formats.join(', '));
                }).catch(e => console.warn('getSupportedFormats error:', e.message));
            }
            
            try {
                console.log('Creating Html5Qrcode instance...');
                html5QrcodeScanner = new Html5Qrcode("reader");
                console.log('Html5Qrcode instance created OK');
            } catch (e) {
                console.error('FAILED to create Html5Qrcode:', e.message);
                return;
            }
            
            const config = { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: false // Disabled: causes black screen on some Androids
                }
            };
            
            console.log('Scanner config:', config);
            console.log('Using decoder: ' + (hasBarcodeDetector ? 'NATIVE BarcodeDetector' : 'JS ZXing fallback'));
            console.log('Calling html5QrcodeScanner.start() with facingMode=environment...');

            html5QrcodeScanner.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanFailure
            ).then(() => {
                console.log('✅ Scanner started SUCCESSFULLY');
                console.info('Scanner state: ' + html5QrcodeScanner.getState());
                
                // Log periodic scan status
                setInterval(() => {
                    if (!isProcessing && html5QrcodeScanner) {
                        try {
                            const state = html5QrcodeScanner.getState();
                            console.log('Scanner heartbeat - state: ' + state + ', failScans: ' + scanFailCount);
                            scanFailCount = 0;
                        } catch(e) {}
                    }
                }, 10000);
            }).catch(err => {
                console.error("❌ Scanner start FAILED:", err);
                console.error("Error name:", err.name);
                console.error("Error message:", err.message);
                document.getElementById('status-badge').innerText = 'Kamera tidak diizinkan';
                document.getElementById('status-badge').classList.replace('bg-black/40', 'bg-red-500/80');
            });
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            
            console.log("🎯 QR Terdeteksi:", decodedText);
            console.log("Format:", decodedResult?.result?.format?.formatName || 'unknown');
            
            let slug = '';
            let marker = '';

            if (decodedText.includes('/cultural/')) {
                const parts = decodedText.split('/cultural/');
                slug = parts[1].split('?')[0].replace(/\/$/, "");
                console.log("Extracted slug:", slug);
            } else if (decodedText.includes('marker=')) {
                try {
                    const urlObj = new URL(decodedText);
                    marker = urlObj.searchParams.get('marker') || '';
                    console.log("Extracted marker:", marker);
                } catch (e) {
                    console.error("URL parse error:", e.message);
                }
            } else if (decodedText.startsWith('MARKER_')) {
                marker = decodedText;
                console.log("Raw marker:", marker);
            }

            if (slug || marker) {
                isProcessing = true;
                if (navigator.vibrate) navigator.vibrate(50);
                
                if (html5QrcodeScanner.isScanning) {
                    html5QrcodeScanner.pause();
                    console.log("Scanner paused");
                }
                
                fetchModel(slug, marker);
            } else {
                console.warn("Format QR Code tidak dikenali:", decodedText);
                const statusBadge = document.getElementById('status-badge');
                statusBadge.innerText = 'QR Tidak Dikenali!';
                statusBadge.classList.replace('bg-black/40', 'bg-red-500/80');
                
                setTimeout(() => {
                    if (!isProcessing) {
                        statusBadge.innerText = 'Arahkan ke Marker QR';
                        statusBadge.classList.replace('bg-red-500/80', 'bg-black/40');
                    }
                }, 2000);
            }
        }

        function onScanFailure(error) {
            // Runs continuously while no QR is detected - count silently
            scanFailCount++;
        }

        function fetchModel(slug, marker) {
            console.log("fetchModel() slug=" + slug + " marker=" + marker);
            
            const loadingOverlay = document.getElementById('loading-overlay');
            const statusBadge = document.getElementById('status-badge');
            
            loadingOverlay.classList.remove('hidden');
            loadingOverlay.classList.add('flex');
            statusBadge.innerText = 'Mengunduh Model...';

            let query = '';
            if (slug) {
                query = `slug=${encodeURIComponent(slug)}`;
            } else if (marker) {
                query = `marker=${encodeURIComponent(marker)}`;
            }

            const fetchUrl = `/api/ar/model?${query}`;
            console.log("Fetching:", fetchUrl);

            fetch(fetchUrl)
                .then(res => {
                    console.log("Fetch response status:", res.status);
                    return res.json();
                })
                .then(data => {
                    console.log("API response:", data);
                    if (data.success && data.model_url) {
                        showModel(data.model_url, data.usdz_url, data.name, data.description);
                    } else {
                        throw new Error(data.error || 'Model tidak ditemukan');
                    }
                })
                .catch(err => {
                    console.error("fetchModel error:", err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: err.message,
                        confirmButtonColor: '#1E5128'
                    }).then(() => {
                        showScanner();
                    });
                })
                .finally(() => {
                    loadingOverlay.classList.add('hidden');
                    loadingOverlay.classList.remove('flex');
                });
        }

        function showModel(url, usdzUrl, name, desc) {
            console.log("showModel() url=" + url + " usdzUrl=" + usdzUrl);
            
            document.getElementById('scanner-view').classList.add('hidden');
            document.getElementById('model-view').classList.remove('hidden');
            document.getElementById('status-badge').innerText = 'Sentuh untuk memutar/zoom';
            
            const viewer = document.getElementById('ar-model-viewer');
            const absoluteUrl = new URL(url, window.location.href).href;
            viewer.src = absoluteUrl;
            console.log("model-viewer src set to:", absoluteUrl);
            
            if (usdzUrl) {
                const absoluteUsdzUrl = new URL(usdzUrl, window.location.href).href;
                viewer.setAttribute('ios-src', absoluteUsdzUrl);
                viewer.iosSrc = absoluteUsdzUrl;
                console.log("ios-src set to:", absoluteUsdzUrl);
            } else {
                viewer.removeAttribute('ios-src');
                console.warn("No USDZ URL provided - iOS AR will not work");
            }
            
            document.getElementById('model-title').innerText = name || '';
            document.getElementById('model-desc').innerText = desc || '';
        }

        function showScanner() {
            console.log("showScanner() called");
            
            document.getElementById('model-view').classList.add('hidden');
            document.getElementById('scanner-view').classList.remove('hidden');
            document.getElementById('status-badge').innerText = 'Arahkan ke Marker QR';
            document.getElementById('status-badge').classList.replace('bg-red-500/80', 'bg-black/40');
            
            isProcessing = false;
            
            document.getElementById('ar-model-viewer').src = '';
            
            if (html5QrcodeScanner && html5QrcodeScanner.getState() === Html5QrcodeScannerState.PAUSED) {
                html5QrcodeScanner.resume();
                console.log("Scanner resumed");
            }
        }
    </script>
@endpush