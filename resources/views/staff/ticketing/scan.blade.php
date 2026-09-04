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

                @forelse ($rates->groupBy('origin') as $origin => $group)
                    <fieldset class="mt-5">
                        <legend class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            {{ \App\Models\TicketRate::ORIGIN_LABELS[$origin] }}
                            <span class="normal-case tracking-normal text-gray-400">
                                — {{ $origin === 'domestic' ? 'Warga Negara Indonesia' : 'Warga Negara Asing' }}
                            </span>
                        </legend>

                        <div class="space-y-2">
                            @foreach ($group as $rate)
                                <div class="rate-row flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-3 py-2.5 transition-colors"
                                    data-rate-id="{{ $rate->id }}"
                                    data-price="{{ $rate->price }}"
                                    data-fee="{{ $rate->service_fee }}">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-charcoal">{{ $rate->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            Rp {{ number_format($rate->price, 0, ',', '.') }}
                                            @if ($rate->service_fee > 0)
                                                <span class="text-gray-500">+ Rp {{ number_format($rate->service_fee, 0, ',', '.') }} layanan</span>
                                            @endif
                                        </p>
                                    </div>

                                    {{-- 44×44 tombol dengan jarak: petugas menekan ini sambil berdiri di gerbang. --}}
                                    <div class="flex shrink-0 items-center gap-2">
                                        <button type="button"
                                            class="qty-step h-11 w-11 rounded-xl border border-gray-200 bg-white text-xl font-bold text-gray-700 active:scale-95 disabled:opacity-40"
                                            data-delta="-1"
                                            aria-label="Kurangi {{ \App\Models\TicketRate::ORIGIN_LABELS[$origin] }} {{ $rate->name }}"
                                            disabled>&minus;</button>
                                        <span class="qty-value w-8 text-center text-lg font-bold tabular-nums text-gray-500">0</span>
                                        <button type="button"
                                            class="qty-step h-11 w-11 rounded-xl border border-gray-200 bg-white text-xl font-bold text-gray-700 active:scale-95"
                                            data-delta="1"
                                            aria-label="Tambah {{ \App\Models\TicketRate::ORIGIN_LABELS[$origin] }} {{ $rate->name }}">+</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                @empty
                    <p class="mt-5 rounded-xl border border-warning/20 bg-warning/5 p-4 text-sm text-warning-800">
                        Belum ada tarif tiket yang aktif. Minta admin mengisinya di menu Tarif Tiket sebelum memindai.
                    </p>
                @endforelse

                <label for="visitor-name" class="mt-5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Nama (opsional)</label>
                <input id="visitor-name" type="text" placeholder="Kosongkan bila tidak perlu"
                    class="mt-2 min-h-[44px] w-full rounded-xl border border-gray-200 px-4 text-sm focus:border-primary focus:ring-primary">

                {{-- Ringkasan berjalan: petugas mencocokkannya dengan total di struk sebelum menyimpan. --}}
                <div class="mt-5 flex items-baseline justify-between rounded-xl bg-white px-4 py-3 shadow-sm"
                    role="status" aria-live="polite">
                    <span id="summary-people" class="text-sm font-semibold text-charcoal">0 orang</span>
                    <span id="summary-total" class="text-lg font-bold tabular-nums text-charcoal">Rp 0</span>
                </div>

                <div class="mt-5 flex gap-3">
                    <button type="button" id="scan-save" disabled
                        class="min-h-[44px] flex-1 rounded-xl bg-primary px-6 text-sm font-semibold text-white shadow-lg shadow-primary/20 active:scale-95 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-600 disabled:shadow-none">Simpan</button>
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

        const rupiah = new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', maximumFractionDigits: 0
        });

        let html5QrCode;
        let isScanning = true;
        let pendingCode = null;

        const formEl = document.getElementById('scan-form');
        const resultEl = document.getElementById('scan-result');
        const nameEl = document.getElementById('visitor-name');
        const saveBtn = document.getElementById('scan-save');
        const peopleEl = document.getElementById('summary-people');
        const totalEl = document.getElementById('summary-total');
        const rows = Array.from(document.querySelectorAll('.rate-row'));

        function post(url, payload) {
            return fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(payload)
            }).then(r => r.json().then(body => ({ status: r.status, body })));
        }

        function rowQuantity(row) {
            return parseInt(row.querySelector('.qty-value').textContent, 10) || 0;
        }

        // Satu sumber kebenaran untuk ringkasan, tombol, dan payload: dihitung
        // ulang dari DOM setiap ketukan supaya tidak ada state bayangan.
        function refreshSummary() {
            let people = 0;
            let total = 0;

            rows.forEach(row => {
                const qty = rowQuantity(row);
                people += qty;
                total += qty * (Number(row.dataset.price) + Number(row.dataset.fee));

                // Baris terisi ditandai cincin, tebal huruf, dan warna sekaligus —
                // bukan warna saja, supaya tetap terbaca di layar terik dan bagi
                // petugas yang buta warna.
                row.classList.toggle('ring-2', qty > 0);
                row.classList.toggle('ring-primary', qty > 0);
                row.classList.toggle('border-primary', qty > 0);

                const valueEl = row.querySelector('.qty-value');
                valueEl.classList.toggle('text-charcoal', qty > 0);
                valueEl.classList.toggle('text-gray-500', qty === 0);

                row.querySelector('[data-delta="-1"]').disabled = qty === 0;
            });

            peopleEl.textContent = `${people} orang`;
            totalEl.textContent = rupiah.format(total);
            saveBtn.disabled = people === 0;
        }

        function resetCounters() {
            rows.forEach(row => { row.querySelector('.qty-value').textContent = '0'; });
            refreshSummary();
        }

        function collectLines() {
            return rows
                .map(row => ({ rate_id: Number(row.dataset.rateId), quantity: rowQuantity(row) }))
                .filter(line => line.quantity > 0);
        }

        rows.forEach(row => {
            row.querySelectorAll('.qty-step').forEach(btn => {
                btn.addEventListener('click', () => {
                    const valueEl = row.querySelector('.qty-value');
                    const next = rowQuantity(row) + Number(btn.dataset.delta);
                    valueEl.textContent = Math.min(200, Math.max(0, next));
                    navigator.vibrate && navigator.vibrate(10);
                    refreshSummary();
                });
            });
        });

        function showForm(code) {
            pendingCode = code;
            resetCounters();
            nameEl.value = '';
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

        function buildResumeButton(colorClass) {
            const btn = document.createElement('button');
            btn.className = `mt-6 min-h-[44px] rounded-xl ${colorClass} px-6 text-sm font-semibold text-white active:scale-95`;
            btn.textContent = 'Scan Tiket Berikutnya';
            btn.addEventListener('click', () => window.resumeScan());
            return btn;
        }

        /** Rincian golongan pada panel hasil, mengikuti urutan baris di struk. */
        function buildBreakdown(scan) {
            const list = document.createElement('dl');
            list.className = 'mt-4 space-y-1 text-left text-sm';

            (scan.breakdown || []).forEach(line => {
                const row = document.createElement('div');
                row.className = 'flex justify-between gap-4';

                const term = document.createElement('dt');
                term.textContent = `${line.quantity} × ${line.label}`;

                const value = document.createElement('dd');
                value.className = 'tabular-nums';
                value.textContent = rupiah.format(line.subtotal);

                row.append(term, value);
                list.append(row);
            });

            const totalRow = document.createElement('div');
            totalRow.className = 'flex justify-between gap-4 border-t border-current/20 pt-1 font-bold';

            const totalTerm = document.createElement('dt');
            totalTerm.textContent = `Total ${scan.party_size} orang`;

            const totalValue = document.createElement('dd');
            totalValue.className = 'tabular-nums';
            totalValue.textContent = rupiah.format(scan.total_price);

            totalRow.append(totalTerm, totalValue);
            list.append(totalRow);

            return list;
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
            detail.textContent = scan.visitor_name;

            resultEl.append(title, detail, buildBreakdown(scan), buildResumeButton('bg-primary'));
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
            detail.textContent = scan.visitor_name;

            const attempts = document.createElement('p');
            attempts.className = 'mt-2 text-xs';
            attempts.textContent = `Percobaan ulang: ${scan.duplicate_attempts}x`;

            resultEl.append(title, scannedInfo, detail, buildBreakdown(scan), attempts, buildResumeButton('bg-warning'));
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

        document.getElementById('scan-cancel').addEventListener('click', () => window.resumeScan());

        saveBtn.addEventListener('click', function() {
            // Satu ketukan = satu permintaan: tanpa ini, ketukan ganda di layar
            // sentuh mengirim dua store dan yang kedua terhitung sebagai dobel.
            if (saveBtn.disabled) return;
            saveBtn.disabled = true;
            saveBtn.classList.add('opacity-60');

            post(storeUrl, {
                raw_code: pendingCode,
                lines: collectLines(),
                visitor_name: nameEl.value.trim() || null
            }).then(res => {
                if (res.status === 409) {
                    showDuplicate(res.body.scan);
                } else if (res.body.success) {
                    showSaved(res.body.scan);
                } else {
                    alert('Data tidak valid. Periksa jumlah orang pada tiap golongan.');
                }
            }).catch(() => alert('Gagal menyimpan. Coba lagi.')).finally(() => {
                saveBtn.classList.remove('opacity-60');
                refreshSummary();
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
            resetCounters();
            resumeCamera();
        };

        refreshSummary();
    });
</script>

@endsection
