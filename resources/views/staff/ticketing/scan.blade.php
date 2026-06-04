@extends('layouts.dashboard')

@section('title', 'Scanner Tiket')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Scanner Tiket Masuk</h1>
        <p class="mt-0.5 text-sm text-gray-500">Pindai QR Code tiket pengunjung untuk verifikasi masuk.</p>
    </div>
    <a href="{{ route('staff.ticketing') }}" class="text-sm font-medium text-primary hover:text-primary-600 flex items-center gap-1">
        &larr; Kembali ke POS
    </a>
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

<!-- Include html5-qrcode library -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let html5QrcodeScanner;

        function onScanSuccess(decodedText, decodedResult) {
            // stop scanning temporary
            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause();
            }

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
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.resume();
                    }
                });
        }

        function onScanFailure(error) {
            // Ignore failure, keep scanning
        }

        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                }
            },
            /* verbose= */
            false);
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        window.resumeScan = function() {
            document.getElementById('scan-result').classList.add('hidden');
            html5QrcodeScanner.resume();
        };
    });
</script>

@endsection
