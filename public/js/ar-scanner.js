(function () {
    let html5QrcodeScanner = null;
    let isProcessing = false;
    let scanFailCount = 0;
    let heartbeatInterval = null;

    const initAr = function () {
        const readerEl = document.getElementById("reader");

        if (readerEl && !html5QrcodeScanner) {
            console.log("DOM Ready - starting init...");

            const isSecure = window.isSecureContext;
            const hasMediaDevices = !!(
                navigator.mediaDevices && navigator.mediaDevices.getUserMedia
            );

            if (!isSecure || !hasMediaDevices) {
                showInsecureOrUnsupportedError(!isSecure);
                return;
            }

            // Ikat ke tombol overlay agar Chrome menerima "User Gesture Token".
            const startBtn = document.getElementById("btn-start-camera");
            const overlay = document.getElementById("start-camera-overlay");

            if (startBtn && overlay) {
                startBtn.addEventListener("click", () => {
                    // Sembunyikan tombol
                    overlay.classList.add("hidden");
                    // Eksekusi kamera (Browser sekarang tahu ini diinisiasi oleh user)
                    initScanner({
                        facingMode: "environment",
                    });
                });
            }

            // Back Button Logic & Exit Confirmation
            const backBtn = document.getElementById("btn-back-scanner");
            const exitConfirmOverlay = document.getElementById(
                "exit-confirm-overlay",
            );
            const btnConfirmExit = document.getElementById("btn-confirm-exit");
            const btnCancelExit = document.getElementById("btn-cancel-exit");

            if (backBtn && exitConfirmOverlay) {
                backBtn.addEventListener("click", () => {
                    const modelView = document.getElementById("model-view");
                    // Jika sedang melihat model, kembali ke scanner
                    if (modelView && !modelView.classList.contains("hidden")) {
                        showScanner();
                    } else {
                        // Tampilkan konfirmasi keluar
                        exitConfirmOverlay.classList.remove("hidden");
                        exitConfirmOverlay.classList.add("flex");
                    }
                });
            }

            if (btnCancelExit && exitConfirmOverlay) {
                btnCancelExit.addEventListener("click", () => {
                    exitConfirmOverlay.classList.add("hidden");
                    exitConfirmOverlay.classList.remove("flex");
                });
            }

            if (btnConfirmExit) {
                btnConfirmExit.addEventListener("click", () => {
                    cleanupAndExit();
                });
            }

            // Scan Again Button
            const btnScanAgain = document.getElementById("btn-scan-again");
            if (btnScanAgain) {
                btnScanAgain.addEventListener("click", () => {
                    showScanner();
                });
            }

            const viewer = document.getElementById("ar-model-viewer");
            if (viewer) {
                viewer.addEventListener("error", (event) => {
                    console.error("ModelViewer Error:", event);
                    Swal.fire({
                        icon: "error",
                        title: "Gagal Memuat Model",
                        text: "Terjadi kesalahan saat memuat model 3D. Format tidak didukung atau file tidak ditemukan.",
                        confirmButtonColor: "#1E5128",
                    }).then(() => {
                        showScanner();
                    });
                });
            }
        }
    };

    function cleanupAndExit() {
        if (heartbeatInterval) {
            clearInterval(heartbeatInterval);
            heartbeatInterval = null;
        }

        // 1. JALUR PINTAS: Matikan hardware kamera secara paksa (Synchronous)
        try {
            const videoEl = document.querySelector("#reader video");
            if (videoEl && videoEl.srcObject) {
                const tracks = videoEl.srcObject.getTracks();
                tracks.forEach((track) => {
                    track.stop();
                    console.log("✅ Track hardware diputus paksa.");
                });
                videoEl.srcObject = null;
            }
        } catch (err) {
            console.error("Force kill error:", err);
        }

        // 2. Bereskan sisa objek library html5-qrcode
        if (html5QrcodeScanner) {
            try {
                const currentState = html5QrcodeScanner.getState();
                if (currentState === 2 || currentState === 3) {
                    html5QrcodeScanner.stop().catch(() => {});
                }
                html5QrcodeScanner.clear();
            } catch (error) {}
            html5QrcodeScanner = null;
        }

        // Navigate back
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = "/";
        }
    }

    function initScanner(cameraConfig) {
        console.log("initScanner() called with config:", cameraConfig);
        try {
            html5QrcodeScanner = new Html5Qrcode("reader");
            console.log("Html5Qrcode instance created OK");
        } catch (e) {
            console.error("FAILED to create Html5Qrcode:", e.message);
            return;
        }

        const isIOS =
            /iPad|iPhone|iPod/.test(navigator.userAgent) ||
            (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);

        const config = {
            fps: 10,
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: false,
            },
        };

        if (isIOS) {
            config.videoConstraints = {
                width: {
                    ideal: 1280,
                },
                height: {
                    ideal: 720,
                },
            };
        }

        // Coba gunakan kamera belakang utama (Environment)
        html5QrcodeScanner
            .start(cameraConfig, config, onScanSuccess, onScanFailure)
            .then(() => {
                console.log(
                    "✅ Scanner started SUCCESSFULLY (Environment Mode)",
                );
                startHeartbeat();
            })
            .catch((err) => {
                console.warn(
                    "❌ Failed with environment mode, trying fallback...",
                    err,
                );

                const isPermissionDenied =
                    err.toString().includes("NotAllowedError") ||
                    err.toString().includes("Permission denied") ||
                    (err.name && err.name === "NotAllowedError");

                if (isPermissionDenied) {
                    showCameraPermissionDeniedError();
                } else {
                    // Fallback: Jika environment gagal (misal di PC/Laptop), cari kamera pertama yang tersedia
                    Html5Qrcode.getCameras()
                        .then((devices) => {
                            if (devices && devices.length > 0) {
                                const fallbackConfig = {
                                    ...config,
                                };
                                delete fallbackConfig.videoConstraints; // Hapus constraint agar tidak bentrok

                                html5QrcodeScanner
                                    .start(
                                        devices[0].id,
                                        fallbackConfig,
                                        onScanSuccess,
                                        onScanFailure,
                                    )
                                    .then(() => {
                                        console.log(
                                            "✅ Scanner started SUCCESSFULLY (Fallback Camera)",
                                        );
                                        startHeartbeat();
                                    })
                                    .catch((fallbackErr) => {
                                        console.error(
                                            "❌ Fallback camera failed too:",
                                            fallbackErr,
                                        );
                                        showCameraError();
                                    });
                            } else {
                                showCameraError();
                            }
                        })
                        .catch((e) => {
                            showCameraPermissionDeniedError();
                        });
                }
            });
    }

    window.retryCameraInit = function () {
        const reticles = document.querySelectorAll(
            "#scanner-view .pointer-events-none",
        );
        reticles.forEach((r) => (r.style.display = "flex"));
        document.getElementById("reader").innerHTML = "";
        initAr();
    };

    function showCameraPermissionDeniedError() {
        const badge = document.getElementById("status-badge");
        if (badge) {
            badge.innerText = "Izin kamera ditolak / Tertahan";
            badge.classList.replace("bg-black/40", "bg-red-500/80");
        }
        const reticles = document.querySelectorAll(
            "#scanner-view .pointer-events-none",
        );
        reticles.forEach((r) => (r.style.display = "none"));
        document.getElementById("reader").innerHTML = `
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
        const badge = document.getElementById("status-badge");
        if (badge) {
            badge.innerText = "Kamera tidak ditemukan";
            badge.classList.replace("bg-black/40", "bg-red-500/80");
        }
        const reticles = document.querySelectorAll(
            "#scanner-view .pointer-events-none",
        );
        reticles.forEach((r) => (r.style.display = "none"));
        document.getElementById("reader").innerHTML = `
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
        const badge = document.getElementById("status-badge");
        if (badge) {
            badge.innerText = insecure
                ? "Koneksi HTTP"
                : "Browser Tidak Didukung";
            badge.classList.replace("bg-black/40", "bg-red-500/80");
        }
        const reticles = document.querySelectorAll(
            "#scanner-view .pointer-events-none",
        );
        reticles.forEach((r) => (r.style.display = "none"));
        let title = insecure ? "Koneksi Tidak Aman" : "Browser Tidak Didukung";
        let desc = insecure
            ? "Fitur kamera memerlukan koneksi HTTPS (SSL) yang aman."
            : "Browser ini tidak mendukung pemindaian kamera.";

        document.getElementById("reader").innerHTML = `
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

        let slug = "";
        let marker = "";

        if (decodedText.includes("/cultural/")) {
            const parts = decodedText.split("/cultural/");
            slug = parts[1].split("?")[0].replace(/\/$/, "");
        } else if (decodedText.includes("marker=")) {
            try {
                const urlObj = new URL(decodedText);
                marker = urlObj.searchParams.get("marker") || "";
            } catch (e) {}
        } else if (decodedText.startsWith("MARKER_")) {
            marker = decodedText;
        }

        if (slug || marker) {
            isProcessing = true;
            if (navigator.vibrate) navigator.vibrate(50);

            if (html5QrcodeScanner && html5QrcodeScanner.getState() === 2) {
                html5QrcodeScanner.pause();
            }

            fetchModel(slug, marker);
        } else {
            const statusBadge = document.getElementById("status-badge");
            if (statusBadge) {
                statusBadge.innerText = "QR Tidak Dikenali!";
                statusBadge.classList.replace("bg-black/40", "bg-red-500/80");
            }
            setTimeout(() => {
                if (!isProcessing && statusBadge) {
                    statusBadge.innerText = "Arahkan ke Marker QR";
                    statusBadge.classList.replace(
                        "bg-red-500/80",
                        "bg-black/40",
                    );
                }
            }, 2000);
        }
    }

    function onScanFailure(error) {
        scanFailCount++;
    }

    function fetchModel(slug, marker) {
        const loadingOverlay = document.getElementById("loading-overlay");
        const statusBadge = document.getElementById("status-badge");

        if (loadingOverlay) {
            loadingOverlay.classList.remove("hidden");
            loadingOverlay.classList.add("flex");
        }
        if (statusBadge) statusBadge.innerText = "Mengunduh Model...";

        let query = slug
            ? `slug=${encodeURIComponent(slug)}`
            : `marker=${encodeURIComponent(marker)}`;

        fetch(`/api/ar/model?${query}`)
            .then((res) => res.json())
            .then((data) => {
                if (data.success && data.model_url) {
                    showModel(
                        data.model_url,
                        data.usdz_url,
                        data.name,
                        data.short_description,
                    );
                } else {
                    throw new Error(data.error || "Model tidak ditemukan");
                }
            })
            .catch((err) => {
                console.error("fetchModel error:", err);
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: err.message,
                    confirmButtonColor: "#1E5128",
                }).then(() => {
                    showScanner();
                });
            })
            .finally(() => {
                if (loadingOverlay) {
                    loadingOverlay.classList.add("hidden");
                    loadingOverlay.classList.remove("flex");
                }
            });
    }

    function showModel(url, usdzUrl, name, desc) {
        const scanView = document.getElementById("scanner-view");
        const modelView = document.getElementById("model-view");
        const badge = document.getElementById("status-badge");

        if (scanView) scanView.classList.add("hidden");
        if (modelView) modelView.classList.remove("hidden");
        if (badge) badge.innerText = "Sentuh untuk memutar/zoom";

        const viewer = document.getElementById("ar-model-viewer");
        if (viewer) {
            const absoluteUrl = new URL(url, window.location.href).href;
            viewer.src = absoluteUrl;
            if (usdzUrl) {
                const absoluteUsdzUrl = new URL(usdzUrl, window.location.href)
                    .href;
                viewer.setAttribute("ios-src", absoluteUsdzUrl);
            } else {
                viewer.removeAttribute("ios-src");
            }
        }

        const mTitle = document.getElementById("model-title");
        const mDesc = document.getElementById("model-desc");
        if (mTitle) mTitle.innerText = name || "";
        if (mDesc) mDesc.innerText = desc || "";
    }

    function showScanner() {
        const scanView = document.getElementById("scanner-view");
        const modelView = document.getElementById("model-view");
        const badge = document.getElementById("status-badge");

        if (modelView) modelView.classList.add("hidden");
        if (scanView) scanView.classList.remove("hidden");
        if (badge) {
            badge.innerText = "Arahkan ke Marker QR";
            badge.classList.replace("bg-red-500/80", "bg-black/40");
        }

        isProcessing = false;
        const viewer = document.getElementById("ar-model-viewer");
        if (viewer) viewer.src = "";

        if (html5QrcodeScanner && html5QrcodeScanner.getState() === 3) {
            html5QrcodeScanner.resume();
        }
    }

    // Eksekusi AR Scanner saat DOM Ready
    document.addEventListener("DOMContentLoaded", () => {
        initAr();
    });

    // Cleanup saat user menutup tab atau me-refresh
    window.addEventListener("beforeunload", cleanupAndExit);
})();
