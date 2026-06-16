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
            <!-- Overlay Reticle -->
            <div class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center">
                <div class="relative h-64 w-64 rounded-3xl border-2 border-white/50">
                    <div class="border-primary absolute -left-1 -top-1 h-8 w-8 rounded-tl-3xl border-l-4 border-t-4"></div>
                    <div class="border-primary absolute -right-1 -top-1 h-8 w-8 rounded-tr-3xl border-r-4 border-t-4"></div>
                    <div class="border-primary absolute -bottom-1 -left-1 h-8 w-8 rounded-bl-3xl border-b-4 border-l-4">
                    </div>
                    <div class="border-primary absolute -bottom-1 -right-1 h-8 w-8 rounded-br-3xl border-b-4 border-r-4">
                    </div>
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

                    // Check if secure context and mediaDevices is supported
                    const isSecure = window.isSecureContext;
                    const hasMediaDevices = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

                    if (!isSecure || !hasMediaDevices) {
                        showInsecureOrUnsupportedError(!isSecure);
                        return;
                    }

                    // Use Html5Qrcode's native method to get cameras and prompt for permission
                    Html5Qrcode.getCameras().then(devices => {
                        if (devices && devices.length) {
                            console.info('Cameras found: ' + devices.length);
                            const isMobile =
                                /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
                                    navigator.userAgent);
                            // Default: first camera on PC (usually main webcam), last camera on Mobile (usually back camera)
                            let cameraId = isMobile ? devices[devices.length - 1].id : devices[0].id;

                            for (let i = 0; i < devices.length; i++) {
                                let label = devices[i].label.toLowerCase();
                                console.info('  Camera ' + i + ': ' + (devices[i].label || '(no label)') +
                                    ' id=' + devices[i].id.substring(0, 8) + '...');
                                if (label.includes('back') || label.includes('environment') || label
                                    .includes('rear') || label.includes('kamera belakang')) {
                                    cameraId = devices[i].id;
                                    break;
                                }
                            }
                            initScanner(cameraId, devices);
                        } else {
                            showCameraError();
                        }
                    }).catch(err => {
                        console.error("Camera permission request failed:", err);
                        showCameraPermissionDeniedError();
                    });

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

            function initScanner(cameraId, devices = []) {
                console.log('initScanner() called with cameraId: ' + cameraId);
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
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        }
                    };
                }

                html5QrcodeScanner.start(
                    cameraId,
                    config,
                    onScanSuccess,
                    onScanFailure
                ).then(() => {
                    console.log('✅ Scanner started SUCCESSFULLY');
                    startHeartbeat();
                }).catch(err => {
                    console.warn("❌ Failed to start scanner:", err);

                    const isPermissionDenied = err.toString().includes("NotAllowedError") ||
                        err.toString().includes("Permission denied") ||
                        (err.name && err.name === "NotAllowedError");

                    if (isPermissionDenied) {
                        showCameraPermissionDeniedError();
                    } else {
                        // Fallback: try starting with the first available camera if this one failed (e.g. OBS Virtual Camera NotReadableError)
                        const fallbackCameraId = (devices && devices.length > 0 && devices[0].id !== cameraId) ?
                            devices[0].id : null;

                        const fallbackConfig = {
                            ...config
                        };
                        delete fallbackConfig.videoConstraints;

                        if (fallbackCameraId) {
                            console.log('Trying fallback camera: ' + fallbackCameraId);
                            html5QrcodeScanner.start(
                                fallbackCameraId,
                                fallbackConfig,
                                onScanSuccess,
                                onScanFailure
                            ).then(() => {
                                console.log('✅ Scanner started SUCCESSFULLY (fallback camera)');
                                startHeartbeat();
                            }).catch(fallbackErr => {
                                console.error('❌ Fallback camera failed too:', fallbackErr);
                                showCameraError();
                            });
                        } else {
                            showCameraError();
                        }
                    }
                });
            }
            window.retryCameraInit = function() {
                // Restore reticles
                const reticles = document.querySelectorAll('#scanner-view .pointer-events-none');
                reticles.forEach(r => r.style.display = 'block');

                // Clear the reader content so Html5Qrcode can mount again
                document.getElementById('reader').innerHTML = '';

                // Re-run initialization
                initAr();
            };

            function showCameraPermissionDeniedError() {
                console.error("❌ Camera permission was denied.");
                const badge = document.getElementById('status-badge');
                if (badge) {
                    badge.innerText = 'Izin kamera ditolak / Tertahan';
                    badge.classList.replace('bg-black/40', 'bg-red-500/80');
                }

                // Hide the scanner reticle overlay
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
                            <button onclick="navigator.clipboard.writeText(window.location.href); Swal.fire({icon:'success', title:'Tautan Disalin', text:'Silakan tempel di Google Chrome atau Safari.', confirmButtonColor: '#1E5128'})" class="w-full bg-white/10 hover:bg-white/20 text-white text-sm font-semibold py-3 rounded-xl transition-all active:scale-95 pointer-events-auto border border-white/10">
                                Salin Tautan Halaman
                            </button>
                        </div>
                    </div>
                `;
            }

            function showCameraError() {
                console.error("❌ All scanner start attempts FAILED.");
                const badge = document.getElementById('status-badge');
                if (badge) {
                    badge.innerText = 'Kamera tidak ditemukan';
                    badge.classList.replace('bg-black/40', 'bg-red-500/80');
                }

                // Hide the scanner reticle overlay
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
                            Pastikan perangkat Anda memiliki kamera belakang yang aktif. Jika Anda menggunakan in-app browser dari aplikasi sosial media, silakan buka langsung di aplikasi <b>Google Chrome</b> atau <b>Safari</b> utama.
                        </p>
                        <div class="flex flex-col gap-3 w-full max-w-xs">
                            <button onclick="window.location.reload()" class="w-full bg-green-700 hover:bg-green-600 text-white text-sm font-semibold py-3 rounded-xl transition-all active:scale-95 shadow-lg shadow-green-700/20 pointer-events-auto">
                                Coba Lagi
                            </button>
                            <button onclick="navigator.clipboard.writeText(window.location.href); Swal.fire({icon:'success', title:'Tautan Disalin', text:'Silakan tempel di Google Chrome atau Safari.', confirmButtonColor: '#1E5128'})" class="w-full bg-white/10 hover:bg-white/20 text-white text-sm font-semibold py-3 rounded-xl transition-all active:scale-95 pointer-events-auto border border-white/10">
                                Salin Tautan Halaman
                            </button>
                        </div>
                    </div>
                `;
            }

            function showInsecureOrUnsupportedError(insecure) {
                const badge = document.getElementById('status-badge');
                if (badge) {
                    badge.innerText = insecure ? 'Koneksi HTTP' : 'Browser Tidak Didukung';
                    badge.classList.replace('bg-black/40', 'bg-red-500/80');
                }

                // Hide the scanner reticle overlay
                const reticles = document.querySelectorAll('#scanner-view .pointer-events-none');
                reticles.forEach(r => r.style.display = 'none');

                let title = 'Browser Tidak Didukung';
                let desc =
                    'Browser ini tidak mendukung pemindaian kamera. Harap salin tautan di bawah dan buka menggunakan Google Chrome atau Safari utama Anda.';
                if (insecure) {
                    title = 'Koneksi Tidak Aman';
                    desc = 'Fitur kamera memerlukan koneksi HTTPS (SSL) yang aman. Silakan hubungi pengelola sistem.';
                }

                document.getElementById('reader').innerHTML = `
                    <div class="flex flex-col items-center justify-center min-h-screen w-full p-8 text-center text-white bg-black/95">
                        <div class="w-16 h-16 mb-4 rounded-full bg-red-500/20 flex items-center justify-center text-red-500">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-lg text-white mb-2">${title}</h3>
                        <p class="text-sm text-gray-400 mb-6 max-w-xs">${desc}</p>
                        <button onclick="navigator.clipboard.writeText(window.location.href); Swal.fire({icon:'success', title:'Tautan Disalin', text:'Silakan tempel di Google Chrome atau Safari.', confirmButtonColor: '#1E5128'})" class="bg-green-700 hover:bg-green-600 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all active:scale-95 shadow-lg shadow-green-700/20 pointer-events-auto">
                            Salin Tautan Halaman
                        </button>
                    </div>
                `;
            }

            function startHeartbeat() {
                heartbeatInterval = setInterval(() => {
                    if (!isProcessing && html5QrcodeScanner) {
                        try {
                            const state = html5QrcodeScanner.getState();
                            console.log('Scanner heartbeat - state: ' + state + ', failScans: ' +
                                scanFailCount);
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
                    } catch (e) {
                        console.error("URL parse error:", e.message);
                    }
                } else if (decodedText.startsWith('MARKER_')) {
                    marker = decodedText;
                }

                if (slug || marker) {
                    isProcessing = true;
                    if (navigator.vibrate) navigator.vibrate(50);

                    if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
                        html5QrcodeScanner.pause();
                        console.log("Scanner paused");
                    }

                    fetchModel(slug, marker);
                } else {
                    console.warn("Format QR Code tidak dikenali:", decodedText);
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

            function onScanFailure(error) {
                scanFailCount++;
            }

            function fetchModel(slug, marker) {
                const loadingOverlay = document.getElementById('loading-overlay');
                const statusBadge = document.getElementById('status-badge');

                if (loadingOverlay) {
                    loadingOverlay.classList.remove('hidden');
                    loadingOverlay.classList.add('flex');
                }
                if (statusBadge) statusBadge.innerText = 'Mengunduh Model...';

                let query = '';
                if (slug) {
                    query = `slug=${encodeURIComponent(slug)}`;
                } else if (marker) {
                    query = `marker=${encodeURIComponent(marker)}`;
                }

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
                        }).then(() => {
                            showScanner();
                        });
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

                if (html5QrcodeScanner && html5QrcodeScanner.getState() === 2) {
                    html5QrcodeScanner.resume();
                }
            }

            // Run immediately
            initAr();

            // Clean up scanner camera stream and heartbeat on Livewire navigation
            document.addEventListener('livewire:navigating', function cleanup(e) {
                if (heartbeatInterval) {
                    clearInterval(heartbeatInterval);
                    heartbeatInterval = null;
                }
                if (html5QrcodeScanner) {
                    if (html5QrcodeScanner.isScanning) {
                        html5QrcodeScanner.stop().then(() => {
                            console.log("Scanner stopped successfully");
                            html5QrcodeScanner = null;
                        }).catch(err => {
                            console.error("Error stopping scanner:", err);
                        });
                    } else {
                        html5QrcodeScanner = null;
                    }
                }
                document.removeEventListener('livewire:navigating', cleanup);
            });
        })();
    </script>
@endsection
