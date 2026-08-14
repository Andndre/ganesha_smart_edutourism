@extends('layouts.dashboard')

@section('title', 'Scan Tiket')

@section('content')

<div class="mb-6">
    <h1 class="font-display text-2xl font-bold text-charcoal">Scan Tiket Masuk</h1>
    <p class="mt-0.5 text-sm text-gray-500">Pindai QR tiket pengunjung untuk pendataan kunjungan.</p>
</div>

<div class="max-w-xl mx-auto">
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="p-6 text-gray-900">
            <div id="reader" class="mx-auto w-full overflow-hidden rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50"></div>

            {{-- Input manual untuk tiket yang QR-nya tidak terbaca kamera --}}
            <div class="mt-4 flex gap-2">
                <input id="manual-code" type="text" inputmode="text" placeholder="Ketik kode tiket manual"
                    class="min-h-[44px] flex-1 rounded-xl border border-gray-200 px-4 text-sm focus:border-primary focus:ring-primary">
                <button type="button" id="manual-submit"
                    class="min-h-[44px] rounded-xl bg-gray-100 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-200 active:scale-[0.98]">
                    Cek
                </button>
            </div>

            {{-- Form cepat: muncul saat kode belum pernah tercatat --}}
            <div id="scan-form" class="mt-6 hidden rounded-2xl border border-primary/20 bg-primary/5 p-5">
                <p class="text-sm font-semibold text-charcoal">Tiket baru — catat kunjungan</p>
                <p class="mt-1 truncate text-xs text-gray-500" id="scan-form-code"></p>

                <label class="mt-4 block text-xs font-semibold uppercase tracking-wider text-gray-500">Jumlah Orang</label>
                <div class="mt-2 flex items-center gap-3">
                    <button type="button" id="party-minus" class="h-11 w-11 rounded-xl bg-white border border-gray-200 text-xl font-bold text-gray-700 active:scale-95">−</button>
                    <input id="party-size" type="number" min="1" max="200" value="1"
                        class="h-11 w-20 rounded-xl border border-gray-200 text-center text-lg font-bold">
                    <button type="button" id="party-plus" class="h-11 w-11 rounded-xl bg-white border border-gray-200 text-xl font-bold text-gray-700 active:scale-95">+</button>
                </div>

                <label class="mt-4 block text-xs font-semibold uppercase tracking-wider text-gray-500">Asal Pengunjung</label>
                <div class="mt-2 grid grid-cols-2 gap-3">
                    <button type="button" data-origin="domestic"
                        class="origin-btn min-h-[44px] rounded-xl border-2 border-primary bg-primary text-sm font-semibold text-white">Domestik</button>
                    <button type="button" data-origin="foreign"
                        class="origin-btn min-h-[44px] rounded-xl border-2 border-gray-200 bg-white text-sm font-semibold text-gray-700">Asing</button>
                </div>

                <label class="mt-4 block text-xs font-semibold uppercase tracking-wider text-gray-500">Nama (opsional)</label>
                <input id="visitor-name" type="text" placeholder="Kosongkan bila tidak perlu"
                    class="mt-2 min-h-[44px] w-full rounded-xl border border-gray-200 px-4 text-sm focus:border-primary focus:ring-primary">

                <div class="mt-5 flex gap-3">
                    <button type="button" id="scan-save"
                        class="min-h-[44px] flex-1 rounded-xl bg-primary px-6 text-sm font-semibold text-white shadow-lg shadow-primary/20 active:scale-95">Simpan</button>
                    <button type="button" id="scan-cancel"
                        class="min-h-[44px] rounded-xl bg-gray-100 px-6 text-sm font-semibold text-gray-700 active:scale-95">Batal</button>
                </div>
            </div>

            {{-- Panel hasil: hijau untuk tersimpan, merah untuk duplikat --}}
            <div id="scan-result" class="mt-6 hidden rounded-2xl border p-6 text-center transition-all"></div>
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
    #reader__scan_region {
        background: transparent !important;
    }
</style>
@endpush

{{-- Versi dipatok: tanpa ini setiap rilis baru html5-qrcode langsung masuk ke gerbang tanpa diuji. --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js" type="text/javascript"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrf = '{{ csrf_token() }}';
        const checkUrl = '{{ route('staff.ticketing.check') }}';
        const storeUrl = '{{ route('staff.ticketing.store') }}';

        let html5QrCode;
        let isScanning = true;
        let pendingCode = null;
        let origin = 'domestic';

        const formEl = document.getElementById('scan-form');
        const resultEl = document.getElementById('scan-result');
        const partyEl = document.getElementById('party-size');
        const nameEl = document.getElementById('visitor-name');

        function post(url, payload) {
            return fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(payload)
            }).then(r => r.json().then(body => ({ status: r.status, body })));
        }

        function showForm(code) {
            pendingCode = code;
            origin = 'domestic';
            partyEl.value = 1;
            nameEl.value = '';
            paintOrigin();
            document.getElementById('scan-form-code').textContent = code;
            resultEl.classList.add('hidden');
            formEl.classList.remove('hidden');
        }

        function pauseCamera() {
            if (html5QrCode && typeof html5QrCode.pause === 'function' && html5QrCode.getState() === Html5QrcodeScannerState.SCANNING) {
                html5QrCode.pause(true);
            }
        }

        function resumeCamera() {
            if (html5QrCode && typeof html5QrCode.resume === 'function' && html5QrCode.getState() === Html5QrcodeScannerState.PAUSED) {
                html5QrCode.resume();
            }
        }

        function paintOrigin() {
            document.querySelectorAll('.origin-btn').forEach(btn => {
                const active = btn.dataset.origin === origin;
                btn.classList.toggle('border-primary', active);
                btn.classList.toggle('bg-primary', active);
                btn.classList.toggle('text-white', active);
                btn.classList.toggle('border-gray-200', !active);
                btn.classList.toggle('bg-white', !active);
                btn.classList.toggle('text-gray-700', !active);
            });
        }

        function buildResumeButton(colorClass) {
            const btn = document.createElement('button');
            btn.className = `mt-6 min-h-[44px] rounded-xl ${colorClass} px-6 text-sm font-semibold text-white active:scale-95`;
            btn.textContent = 'Scan Tiket Berikutnya';
            btn.addEventListener('click', () => window.resumeScan());
            return btn;
        }

        function showSaved(scan) {
            formEl.classList.add('hidden');
            resultEl.className = 'mt-6 rounded-2xl border border-primary/20 bg-primary/10 p-6 text-center text-primary-900';
            resultEl.innerHTML = '';

            const title = document.createElement('h3');
            title.className = 'text-lg font-bold';
            title.textContent = 'Tercatat';

            const detail = document.createElement('p');
            detail.className = 'mt-2 text-sm';
            detail.textContent = `${scan.visitor_name} • ${scan.party_size} orang • ${scan.origin === 'foreign' ? 'Asing' : 'Domestik'}`;

            resultEl.append(title, detail, buildResumeButton('bg-primary'));
        }

        function showDuplicate(scan) {
            formEl.classList.add('hidden');
            resultEl.className = 'mt-6 rounded-2xl border border-warning/20 bg-warning/10 p-6 text-center text-warning-900';
            resultEl.innerHTML = '';

            const title = document.createElement('h3');
            title.className = 'text-lg font-bold';
            title.textContent = 'Tiket Sudah Dipakai';

            const scannedInfo = document.createElement('p');
            scannedInfo.className = 'mt-2 text-sm';
            scannedInfo.textContent = `Dipindai ${scan.scanned_at_date} pukul ${scan.scanned_at} oleh ${scan.scanner_name}`;

            const detail = document.createElement('p');
            detail.className = 'mt-1 text-sm';
            detail.textContent = `${scan.visitor_name} • ${scan.party_size} orang • ${scan.origin === 'foreign' ? 'Asing' : 'Domestik'}`;

            const attempts = document.createElement('p');
            attempts.className = 'mt-2 text-xs';
            attempts.textContent = `Percobaan ulang: ${scan.duplicate_attempts}x`;

            resultEl.append(title, scannedInfo, detail, attempts, buildResumeButton('bg-warning'));
        }

        function handleCode(code) {
            pauseCamera();
            post(checkUrl, { raw_code: code }).then(res => {
                if (res.body.status === 'duplicate') {
                    navigator.vibrate && navigator.vibrate([50, 50, 50]);
                    showDuplicate(res.body.scan);
                } else {
                    navigator.vibrate && navigator.vibrate(50);
                    showForm(code);
                }
            }).catch(() => {
                alert('Gagal menghubungi server. Coba lagi.');
                isScanning = true;
                resumeCamera();
            });
        }

        function onScanSuccess(decodedText) {
            if (!isScanning) return;
            isScanning = false;
            handleCode(decodedText);
        }

        function onScanFailure() {
            // Diabaikan agar kamera terus memindai
        }

        document.getElementById('manual-submit').addEventListener('click', function() {
            const code = document.getElementById('manual-code').value.trim();
            if (!code) return;
            isScanning = false;
            handleCode(code);
        });

        document.getElementById('party-minus').addEventListener('click', () => {
            partyEl.value = Math.max(1, parseInt(partyEl.value || '1', 10) - 1);
        });
        document.getElementById('party-plus').addEventListener('click', () => {
            partyEl.value = Math.min(200, parseInt(partyEl.value || '1', 10) + 1);
        });
        document.querySelectorAll('.origin-btn').forEach(btn => {
            btn.addEventListener('click', () => { origin = btn.dataset.origin; paintOrigin(); });
        });
        document.getElementById('scan-cancel').addEventListener('click', () => window.resumeScan());

        const saveBtn = document.getElementById('scan-save');

        saveBtn.addEventListener('click', function() {
            // Satu ketukan = satu permintaan: tanpa ini, ketukan ganda di layar
            // sentuh mengirim dua store dan yang kedua terhitung sebagai dobel.
            if (saveBtn.disabled) return;
            saveBtn.disabled = true;
            saveBtn.classList.add('opacity-60');

            post(storeUrl, {
                raw_code: pendingCode,
                party_size: parseInt(partyEl.value || '1', 10),
                origin: origin,
                visitor_name: nameEl.value.trim() || null
            }).then(res => {
                if (res.status === 409) {
                    showDuplicate(res.body.scan);
                } else if (res.body.success) {
                    showSaved(res.body.scan);
                } else {
                    alert('Data tidak valid. Periksa jumlah orang dan asal pengunjung.');
                }
            }).catch(() => alert('Gagal menyimpan. Coba lagi.')).finally(() => {
                saveBtn.disabled = false;
                saveBtn.classList.remove('opacity-60');
            });
        });

        html5QrCode = new Html5Qrcode("reader");

        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                const backCamera = devices.find(device => device.label.toLowerCase().includes('back') || device.label.toLowerCase().includes('environment') || device.label.toLowerCase().includes('rear'));
                const cameraId = backCamera ? backCamera.id : devices[0].id;

                html5QrCode.start(cameraId, { fps: 10, qrbox: { width: 220, height: 220 } }, onScanSuccess, onScanFailure)
                    .catch(() => fallbackFacingMode());
            } else {
                fallbackFacingMode();
            }
        }).catch(() => fallbackFacingMode());

        function fallbackFacingMode() {
            html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 220, height: 220 } }, onScanSuccess, onScanFailure)
                .catch(() => showCameraError("Izin akses kamera ditolak atau kamera tidak tersedia."));
        }

        function showCameraError(msg) {
            document.getElementById('reader').innerHTML = `
                <div class="p-6 text-center text-warning-800 bg-warning/5 border border-warning/10 rounded-2xl">
                    <p class="font-semibold text-sm">${msg}</p>
                    <p class="text-xs mt-2 text-gray-500">Gunakan input manual di bawah untuk mengetik kode tiket.</p>
                </div>
            `;
        }

        window.resumeScan = function() {
            formEl.classList.add('hidden');
            resultEl.classList.add('hidden');
            document.getElementById('manual-code').value = '';
            pendingCode = null;
            isScanning = true;
            resumeCamera();
        };
    });
</script>

@endsection
