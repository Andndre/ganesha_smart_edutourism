@extends('layouts.dashboard')

@section('title', 'Scanner Tiket')

@section('content')

<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Scanner Tiket Masuk</h1>
        <p class="mt-0.5 text-sm text-gray-500">Pindai QR Code tiket pengunjung untuk verifikasi masuk.</p>
    </div>
    <div class="flex flex-wrap gap-2.5 justify-start lg:justify-end">
        <a href="{{ route('staff.ticketing') }}" class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200 transition-all active:scale-[0.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke POS
        </a>
    </div>
</div>

<div class="max-w-xl mx-auto">
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="p-6 text-gray-900">
            <h3 class="mb-4 text-center text-sm font-semibold text-gray-600">Arahkan kamera ke QR Code tiket pengunjung</h3>

            <div id="reader" class="mx-auto w-full overflow-hidden rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50">
            </div>

            <div id="scan-result" class="mt-6 hidden rounded-2xl p-6 text-center border transition-all">
                <!-- Result injected here via JS -->
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    #reader {
        width: 100%;
        max-width: 400px;
        aspect-ratio: 1 / 1;
        background-color: #f9fafb;
    }
    #reader video {
        object-fit: cover;
        width: 100% !important;
        height: 100% !important;
        border-radius: 16px;
    }
    /* Hide the library scan region box or modify it */
    #reader__scan_region {
        background: transparent !important;
    }
</style>
@endpush

<!-- Include html5-qrcode library -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let html5QrCode;
        let isScanning = true;

        function onScanSuccess(decodedText, decodedResult) {
            if (!isScanning) return;
            isScanning = false;

            fetch('{{ route('staff.ticketing.verify') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        qr_code: decodedText
                    })
                })
                .then(response => response.json().then(data => ({
                    status: response.status,
                    body: data
                })))
                .then(res => {
                    const data = res.body;
                    const resultDiv = document.getElementById('scan-result');
                    resultDiv.classList.remove('hidden', 'bg-primary/10', 'text-primary-900', 'bg-warning/10',
                        'text-warning-900', 'border-primary/20', 'border-warning/20');

                    if (data.success) {
                        resultDiv.classList.add('bg-primary/10', 'text-primary-900', 'border-primary/20');
                        resultDiv.innerHTML = `
                        <div class="mb-2 flex justify-center">
                            <svg class="h-12 w-12 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold">Berhasil!</h3>
                        <p class="mt-2 text-sm text-primary-800">${data.message}</p>
                        <button class="mt-6 rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 hover:bg-primary-600 active:scale-95 transition-all" onclick="resumeScan()">Scan Tiket Lainnya</button>
                    `;
                    } else {
                        resultDiv.classList.add('bg-warning/10', 'text-warning-900', 'border-warning/20');
                        resultDiv.innerHTML = `
                        <div class="mb-2 flex justify-center">
                            <svg class="h-12 w-12 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold">Gagal</h3>
                        <p class="mt-2 text-sm text-warning-800">${data.message}</p>
                        <button class="mt-6 rounded-xl bg-warning px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-warning/20 hover:bg-warning-600 active:scale-95 transition-all" onclick="resumeScan()">Coba Lagi</button>
                    `;
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('Terjadi kesalahan pada server. Coba lagi.');
                    isScanning = true;
                });
        }

        function onScanFailure(error) {
            // Ignore scan failure to keep polling the camera
        }

        html5QrCode = new Html5Qrcode("reader");

        // Request permission and start camera scanning automatically
        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                // Try to find environment (back) camera
                const backCamera = devices.find(device => device.label.toLowerCase().includes('back') || device.label.toLowerCase().includes('environment') || device.label.toLowerCase().includes('rear'));
                const cameraId = backCamera ? backCamera.id : devices[0].id;
                
                html5QrCode.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: { width: 220, height: 220 }
                    },
                    onScanSuccess,
                    onScanFailure
                ).catch(err => {
                    console.error("Gagal memulai kamera: ", err);
                    fallbackFacingMode();
                });
            } else {
                fallbackFacingMode();
            }
        }).catch(err => {
            console.warn("Gagal mendeteksi kamera, mencoba facingMode langsung: ", err);
            fallbackFacingMode();
        });

        function fallbackFacingMode() {
            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 220, height: 220 }
                },
                onScanSuccess,
                onScanFailure
            ).catch(err2 => {
                showCameraError("Izin akses kamera ditolak atau kamera tidak tersedia.");
            });
        }

        function showCameraError(msg) {
            document.getElementById('reader').innerHTML = `
                <div class="p-6 text-center text-warning-800 bg-warning/5 border border-warning/10 rounded-2xl">
                    <svg class="h-10 w-10 mx-auto text-warning mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="font-semibold text-sm">${msg}</p>
                    <p class="text-xs mt-2 text-gray-500">Pastikan izin kamera diaktifkan untuk situs ini di pengaturan browser Anda, lalu muat ulang halaman.</p>
                </div>
            `;
        }

        window.resumeScan = function() {
            document.getElementById('scan-result').classList.add('hidden');
            isScanning = true;
        };
    });
</script>

@endsection
