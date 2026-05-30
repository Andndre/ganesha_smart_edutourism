<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Scanner Tiket') }}
            </h2>
            <a href="{{ route('staff.ticketing') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                &larr; Kembali ke POS
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="mb-4 text-center text-lg font-medium text-gray-700">Arahkan kamera ke QR Code tiket pengunjung</h3>
                    
                    <div id="reader" class="mx-auto w-full max-w-lg overflow-hidden rounded-xl border-2 border-dashed border-gray-300"></div>
                    
                    <div id="scan-result" class="mt-6 hidden rounded-xl p-6 text-center shadow-inner">
                        <!-- Result injected here via JS -->
                    </div>
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
                if(html5QrcodeScanner) html5QrcodeScanner.pause();

                fetch('{{ route('staff.ticketing.verify') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ qr_code: decodedText })
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    const data = res.body;
                    const resultDiv = document.getElementById('scan-result');
                    resultDiv.classList.remove('hidden', 'bg-green-50', 'text-green-900', 'bg-red-50', 'text-red-900', 'border', 'border-green-200', 'border-red-200');
                    
                    if (data.success) {
                        resultDiv.classList.add('bg-green-50', 'text-green-900', 'border', 'border-green-200');
                        resultDiv.innerHTML = `
                            <div class="mb-2 flex justify-center">
                                <svg class="h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold">Berhasil!</h3>
                            <p class="mt-2 text-green-700">${data.message}</p>
                            <button class="mt-6 rounded-lg bg-green-600 px-6 py-2 font-medium text-white shadow-sm hover:bg-green-700 active:scale-95" onclick="resumeScan()">Scan Tiket Lainnya</button>
                        `;
                    } else {
                        resultDiv.classList.add('bg-red-50', 'text-red-900', 'border', 'border-red-200');
                        resultDiv.innerHTML = `
                            <div class="mb-2 flex justify-center">
                                <svg class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold">Gagal</h3>
                            <p class="mt-2 text-red-700">${data.message}</p>
                            <button class="mt-6 rounded-lg bg-red-600 px-6 py-2 font-medium text-white shadow-sm hover:bg-red-700 active:scale-95" onclick="resumeScan()">Coba Lagi</button>
                        `;
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('Terjadi kesalahan pada server. Coba lagi.');
                    if(html5QrcodeScanner) html5QrcodeScanner.resume();
                });
            }

            function onScanFailure(error) {
                // Ignore failure, keep scanning
            }

            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                { fps: 10, qrbox: {width: 250, height: 250} },
                /* verbose= */ false);
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);

            window.resumeScan = function() {
                document.getElementById('scan-result').classList.add('hidden');
                html5QrcodeScanner.resume();
            };
        });
    </script>
</x-admin-layout>
