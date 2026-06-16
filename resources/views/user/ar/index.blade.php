@extends('layouts.app')
@section('title', 'AR Scanner - Penglipuran')

@section('content')
    <style>
        /* Sembunyikan pesan html5-qrcode bawaan */
        #reader__dashboard_section_csr span {
            color: white;
            font-size: 14px;
        }

        #reader {
            width: 100%;
            border: none;
        }

        #reader video {
            object-fit: cover !important;
            width: 100vw !important;
            height: 100vh !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            z-index: 1 !important;
        }

        /* Model Viewer Styles */
        model-viewer {
            width: 100%;
            height: 100%;
            background-color: transparent;
            --poster-color: transparent;
        }
    </style>
    <!-- Container Utama -->
    <div class="fixed inset-0 z-50 flex flex-col overflow-hidden bg-black">

        <!-- Header -->
        <div
            class="pointer-events-none absolute left-0 right-0 top-0 z-20 flex items-center justify-between p-4 pt-[max(env(safe-area-inset-top),1rem)]">
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

            <div id="start-camera-overlay" class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-black/80 p-6 backdrop-blur-md transition-all">
                <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-white/10">
                    <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="mb-2 text-xl font-bold text-white">Mulai Pemindaian AR</h3>
                <p class="mb-8 max-w-xs text-center text-sm text-gray-300">Ketuk tombol di bawah untuk mengaktifkan kamera dan mulai memindai marker QR.</p>
                <button id="btn-start-camera" class="rounded-xl bg-[#1E5128] px-8 py-3.5 font-semibold text-white shadow-lg shadow-[#1E5128]/30 transition-all active:scale-95">
                    Buka Kamera
                </button>
            </div>

            <div class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center">
                <div class="relative h-64 w-64 rounded-3xl border-2 border-white/50">
                    <div class="border-primary absolute -left-1 -top-1 h-8 w-8 rounded-tl-3xl border-l-4 border-t-4"></div>
                    <div class="border-primary absolute -right-1 -top-1 h-8 w-8 rounded-tr-3xl border-r-4 border-t-4"></div>
                    <div class="border-primary absolute -bottom-1 -left-1 h-8 w-8 rounded-bl-3xl border-b-4 border-l-4"></div>
                    <div class="border-primary absolute -bottom-1 -right-1 h-8 w-8 rounded-br-3xl border-b-4 border-r-4"></div>
                </div>
            </div>
        </div>

        <!-- 2. 3D Model View (Hidden Initially) -->
        <div id="model-view" class="bg-charcoal absolute inset-0 z-10 hidden">
            <model-viewer id="ar-model-viewer" src="" alt="3D Model" camera-controls auto-rotate ar ar-scale="auto"
                ar-placement="floor" bounds="tight" ar-modes="scene-viewer quick-look webxr"
                quick-look-browsers="safari chrome" environment-image="neutral" exposure="1" shadow-intensity="1"
                shadow-softness="1">

                <!-- Custom AR Button -->
                <button slot="ar-button"
                    class="bg-primary shadow-primary/30 pointer-events-auto absolute bottom-12 left-1/2 flex -translate-x-1/2 items-center gap-2 rounded-2xl px-6 py-4 font-bold text-white shadow-xl transition-all active:scale-95">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Letakkan di Lantai (AR)
                </button>
            </model-viewer>

            <!-- Metadata Overlay -->
            <div class="pointer-events-none absolute bottom-32 left-0 right-0 p-6 text-center">
                <h2 id="model-title" class="text-2xl font-bold text-white drop-shadow-md">Memuat...</h2>
                <p id="model-desc" class="mt-2 line-clamp-2 text-sm text-gray-300 drop-shadow-md"></p>
            </div>

            <!-- Back to Scanner Button -->
            <button id="btn-back-scanner"
                class="pointer-events-auto absolute bottom-8 left-6 flex h-12 w-12 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white backdrop-blur-md transition-all active:scale-95">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>

        <!-- Loading Overlay -->
        <div id="loading-overlay"
            class="absolute inset-0 z-30 hidden items-center justify-center bg-black/80 backdrop-blur-sm transition-all">
            <div class="flex flex-col items-center gap-4">
                <div
                    class="border-primary shadow-primary/20 h-12 w-12 animate-spin rounded-full border-4 border-t-transparent shadow-lg">
                </div>
                <span class="text-base font-medium tracking-wide text-white">Mengunduh Model 3D...</span>
            </div>
        </div>

    </div>
    <!-- HTML5 QR Code -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <!-- Model Viewer & Meshopt Decoder -->
    <script>
        self.ModelViewerElement = self.ModelViewerElement || {};
        self.ModelViewerElement.meshoptDecoderLocation = 'https://cdn.jsdelivr.net/npm/meshoptimizer/meshopt_decoder.js';
    </script>
    <script type="module" src="{{ asset('js/model-viewer.min.js') }}"></script>

    <script>
        (function() {
            let html5QrcodeScanner = null;
            let isProcessing = false;
            let scanFailCount = 0;
            let heartbeatInterval = null;

            const initAr = function() {
                const readerEl = document.getElementById('reader');

                if (readerEl && !html5QrcodeScanner) {
                    console.log('DOM Ready - starting init...');

                    const isSecure = window.isSecureContext;
                    const hasMediaDevices = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

                    if (!isSecure || !hasMediaDevices) {
                        showInsecureOrUnsupportedError(!isSecure);
                        return;
                    }

                    // KOREKSI UTAMA: Jangan panggil initScanner() otomatis di sini.
                    // Ikat ke tombol overlay agar Chrome menerima "User Gesture Token".
                    const startBtn = document.getElementById('btn-start-camera');
                    const overlay = document.getElementById('start-camera-overlay');

                    if (startBtn && overlay) {
                        startBtn.addEventListener('click', () => {
                            // Sembunyikan tombol
                            overlay.classList.add('hidden');
                            // Eksekusi kamera (Browser sekarang tahu ini diinisiasi oleh user)
                            initScanner({ facingMode: "environment" });
                        });
                    }

                    const backBtn = document.getElementById('btn-back-scanner');
                    if (backBtn) {
                        backBtn.addEventListener('click', () => {
                            showScanner();
                        });
                    }

                    const viewer = document.getElementById('ar-model-viewer');
                    if (viewer) {
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
                    }
                }
            };

            function initScanner(cameraConfig) {
                console.log('initScanner() called with config:', cameraConfig);
                try {
                    html5QrcodeScanner = new Html5Qrcode("reader");
                    console.log('Html5Qrcode instance created OK');
                } catch (e) {
                    console.error('FAILED to create Html5Qrcode:', e.message);
                    return;
                }

                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                    (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

                const config = {
                    fps: 10,
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: false
                    }
                };

                if (isIOS) {
                    config.videoConstraints = {
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    };
                }

                // Coba gunakan kamera belakang utama (Environment)
                html5QrcodeScanner.start(
                    cameraConfig,
                    config,
                    onScanSuccess,
                    onScanFailure
                ).then(() => {
                    console.log('✅ Scanner started SUCCESSFULLY (Environment Mode)');
                    startHeartbeat();
                }).catch(err => {
                    console.warn("❌ Failed with environment mode, trying fallback...", err);

                    const isPermissionDenied = err.toString().includes("NotAllowedError") ||
                        err.toString().includes("Permission denied") ||
                        (err.name && err.name === "NotAllowedError");

                    if (isPermissionDenied) {
                        showCameraPermissionDeniedError();
                    } else {
                        // Fallback: Jika environment gagal (misal di PC/Laptop), cari kamera pertama yang tersedia
                        Html5Qrcode.getCameras().then(devices => {
                            if (devices && devices.length > 0) {
                                const fallbackConfig = { ...config };
                                delete fallbackConfig.videoConstraints; // Hapus constraint agar tidak bentrok
                                
                                html5QrcodeScanner.start(
                                    devices[0].id,
                                    fallbackConfig,
                                    onScanSuccess,
                                    onScanFailure
                                ).then(() => {
                                    console.log('✅ Scanner started SUCCESSFULLY (Fallback Camera)');
                                    startHeartbeat();
                                }).catch(fallbackErr => {
                                    console.error('❌ Fallback camera failed too:', fallbackErr);
                                    showCameraError();
                                });
                            } else {
                                showCameraError();
                            }
                        }).catch(e => {
                            showCameraPermissionDeniedError();
                        });
                    }
                });
            }

            window.retryCameraInit = function() {
                const reticles = document.querySelectorAll('#scanner-view .pointer-events-none');
                reticles.forEach(r => r.style.display = 'block');
                document.getElementById('reader').innerHTML = '';
                initAr();
            };

            function showCameraPermissionDeniedError() {
                const badge = document.getElementById('status-badge');
                if (badge) {
                    badge.innerText = 'Izin kamera ditolak / Tertahan';
                    badge.classList.replace('bg-black/40', 'bg-red-500/80');
                }
                const reticles = document.querySelectorAll('#scanner-view .pointer-events-none');
                reticles.forEach(r => r.style.display = 'none');
                document.getElementById('reader').innerHTML = `
                    <div class="flex flex-col items-center justify-center min-h-screen w-full p-8 text-center text-white bg-black/95">
                        <div class="w-16 h-16 mb-4 rounded-full bg-red-500/20 flex items-center justify-center text-red-500">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-lg text-white mb-2">Akses Kamera Tertahan / Ditolak</h3>
                        <p class="text-sm text-gray-400 mb-6 max-w-xs leading-relaxed">
                            Browser membutuhkan izin Anda untuk mengaktifkan kamera. Ketuk tombol di bawah untuk meminta ulang izin akses kamera.<br><br>
                            Jika tetap tidak bisa, pastikan Anda telah memberikan Izin Kamera di pengaturan <b>Google Chrome</b> atau <b>Safari</b> Anda.
                        </p>
                        <div class="flex flex-col gap-3 w-full max-w-xs">
                            <button onclick="window.retryCameraInit()" class="w-full bg-green-700 hover:bg-green-600 text-white text-sm font-semibold py-3 rounded-xl transition-all active:scale-95 shadow-lg shadow-green-700/20 pointer-events-auto">
                                Izinkan Kamera & Coba Lagi
                            </button>
                        </div>
                    </div>
                `;
            }

            function showCameraError() {
                const badge = document.getElementById('status-badge');
                if (badge) {
                    badge.innerText = 'Kamera tidak ditemukan';
                    badge.classList.replace('bg-black/40', 'bg-red-500/80');
                }
                const reticles = document.querySelectorAll('#scanner-view .pointer-events-none');
                reticles.forEach(r => r.style.display = 'none');
                document.getElementById('reader').innerHTML = `
                    <div class="flex flex-col items-center justify-center min-h-screen w-full p-8 text-center text-white bg-black/95">
                        <div class="w-16 h-16 mb-4 rounded-full bg-yellow-500/20 flex items-center justify-center text-yellow-500">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-lg text-white mb-2">Kamera Tidak Tersedia</h3>
                        <p class="text-sm text-gray-400 mb-6 max-w-xs leading-relaxed">
                            Pastikan perangkat Anda memiliki kamera belakang yang aktif.
                        </p>
                        <button onclick="window.location.reload()" class="w-full bg-green-700 hover:bg-green-600 text-white text-sm font-semibold py-3 rounded-xl transition-all active:scale-95 shadow-lg shadow-green-700/20 pointer-events-auto max-w-xs">
                            Coba Lagi
                        </button>
                    </div>
                `;
            }

            function showInsecureOrUnsupportedError(insecure) {
                const badge = document.getElementById('status-badge');
                if (badge) {
                    badge.innerText = insecure ? 'Koneksi HTTP' : 'Browser Tidak Didukung';
                    badge.classList.replace('bg-black/40', 'bg-red-500/80');
                }
                const reticles = document.querySelectorAll('#scanner-view .pointer-events-none');
                reticles.forEach(r => r.style.display = 'none');
                let title = insecure ? 'Koneksi Tidak Aman' : 'Browser Tidak Didukung';
                let desc = insecure 
                    ? 'Fitur kamera memerlukan koneksi HTTPS (SSL) yang aman.' 
                    : 'Browser ini tidak mendukung pemindaian kamera.';
                
                document.getElementById('reader').innerHTML = `
                    <div class="flex flex-col items-center justify-center min-h-screen w-full p-8 text-center text-white bg-black/95">
                        <div class="w-16 h-16 mb-4 rounded-full bg-red-500/20 flex items-center justify-center text-red-500">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-lg text-white mb-2">${title}</h3>
                        <p class="text-sm text-gray-400 mb-6 max-w-xs">${desc}</p>
                    </div>
                `;
            }

            function startHeartbeat() {
                heartbeatInterval = setInterval(() => {
                    if (!isProcessing && html5QrcodeScanner) {
                        try {
                            const state = html5QrcodeScanner.getState();
                            scanFailCount = 0;
                        } catch (e) {}
                    }
                }, 10000);
            }

            function onScanSuccess(decodedText, decodedResult) {
                if (isProcessing) return;
                console.log("🎯 QR Terdeteksi:", decodedText);

                let slug = '';
                let marker = '';

                if (decodedText.includes('/cultural/')) {
                    const parts = decodedText.split('/cultural/');
                    slug = parts[1].split('?')[0].replace(/\/$/, "");
                } else if (decodedText.includes('marker=')) {
                    try {
                        const urlObj = new URL(decodedText);
                        marker = urlObj.searchParams.get('marker') || '';
                    } catch (e) {}
                } else if (decodedText.startsWith('MARKER_')) {
                    marker = decodedText;
                }

                if (slug || marker) {
                    isProcessing = true;
                    if (navigator.vibrate) navigator.vibrate(50);

                    // BENAR: Menggunakan getState()
                    if (html5QrcodeScanner && html5QrcodeScanner.getState() === 2) {
                        html5QrcodeScanner.pause();
                    }

                    fetchModel(slug, marker);
                } else {
                    const statusBadge = document.getElementById('status-badge');
                    if (statusBadge) {
                        statusBadge.innerText = 'QR Tidak Dikenali!';
                        statusBadge.classList.replace('bg-black/40', 'bg-red-500/80');
                    }
                    setTimeout(() => {
                        if (!isProcessing && statusBadge) {
                            statusBadge.innerText = 'Arahkan ke Marker QR';
                            statusBadge.classList.replace('bg-red-500/80', 'bg-black/40');
                        }
                    }, 2000);
                }
            }

            function onScanFailure(error) { scanFailCount++; }

            function fetchModel(slug, marker) {
                const loadingOverlay = document.getElementById('loading-overlay');
                const statusBadge = document.getElementById('status-badge');

                if (loadingOverlay) {
                    loadingOverlay.classList.remove('hidden');
                    loadingOverlay.classList.add('flex');
                }
                if (statusBadge) statusBadge.innerText = 'Mengunduh Model...';

                let query = slug ? `slug=${encodeURIComponent(slug)}` : `marker=${encodeURIComponent(marker)}`;

                fetch(`/api/ar/model?${query}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.model_url) {
                            showModel(data.model_url, data.usdz_url, data.name, data.short_description);
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
                        }).then(() => { showScanner(); });
                    })
                    .finally(() => {
                        if (loadingOverlay) {
                            loadingOverlay.classList.add('hidden');
                            loadingOverlay.classList.remove('flex');
                        }
                    });
            }

            function showModel(url, usdzUrl, name, desc) {
                const scanView = document.getElementById('scanner-view');
                const modelView = document.getElementById('model-view');
                const badge = document.getElementById('status-badge');
                
                if (scanView) scanView.classList.add('hidden');
                if (modelView) modelView.classList.remove('hidden');
                if (badge) badge.innerText = 'Sentuh untuk memutar/zoom';

                const viewer = document.getElementById('ar-model-viewer');
                if (viewer) {
                    const absoluteUrl = new URL(url, window.location.href).href;
                    viewer.src = absoluteUrl;
                    if (usdzUrl) {
                        const absoluteUsdzUrl = new URL(usdzUrl, window.location.href).href;
                        viewer.setAttribute('ios-src', absoluteUsdzUrl);
                    } else {
                        viewer.removeAttribute('ios-src');
                    }
                }

                const mTitle = document.getElementById('model-title');
                const mDesc = document.getElementById('model-desc');
                if (mTitle) mTitle.innerText = name || '';
                if (mDesc) mDesc.innerText = desc || '';
            }

            function showScanner() {
                const scanView = document.getElementById('scanner-view');
                const modelView = document.getElementById('model-view');
                const badge = document.getElementById('status-badge');

                if (modelView) modelView.classList.add('hidden');
                if (scanView) scanView.classList.remove('hidden');
                if (badge) {
                    badge.innerText = 'Arahkan ke Marker QR';
                    badge.classList.replace('bg-red-500/80', 'bg-black/40');
                }

                isProcessing = false;
                const viewer = document.getElementById('ar-model-viewer');
                if (viewer) viewer.src = '';

                if (html5QrcodeScanner && html5QrcodeScanner.getState() === 3) {
                    html5QrcodeScanner.resume();
                }
            }

            // Eksekusi AR Scanner
            initAr();

            // PENTING: Perbaikan Cleanup Livewire agar kamera tidak nyangkut (Layar Hitam) saat pindah halaman
            document.addEventListener('livewire:navigating', function cleanup(e) {
                if (heartbeatInterval) {
                    clearInterval(heartbeatInterval);
                    heartbeatInterval = null;
                }
                
                if (html5QrcodeScanner) {
                    try {
                        // KOREKSI: Gunakan getState() bukan isScanning
                        const currentState = html5QrcodeScanner.getState();
                        
                        // Jika kamera sedang menyala (2) atau di-pause (3), matikan secara hardware
                        if (currentState === 2 || currentState === 3) {
                            html5QrcodeScanner.stop().then(() => {
                                console.log("✅ Hardware camera released.");
                                html5QrcodeScanner.clear();
                                html5QrcodeScanner = null;
                            }).catch(err => {
                                console.error("Error stopping scanner:", err);
                                html5QrcodeScanner.clear();
                                html5QrcodeScanner = null;
                            });
                        } else {
                            html5QrcodeScanner.clear();
                            html5QrcodeScanner = null;
                        }
                    } catch (error) {
                        html5QrcodeScanner = null;
                    }
                }
                document.removeEventListener('livewire:navigating', cleanup);
            });
        })();
    </script>
@endsection
