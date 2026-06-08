@extends('layouts.app')

@section('title', 'Smart Edutourism - ' . $activeSession->tourRoute->name)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        .leaflet-control-attribution {
            display: none !important;
        }

        .leaflet-container:focus {
            outline: none;
        }
    </style>
@endpush

@section('content')
    <div class="fixed inset-x-0 bottom-0 top-0 z-0 bg-[#E5E3DF]">
        <div id="map" class="absolute inset-0 z-0"></div>

        <!-- Top Overlay -->
        <div class="pointer-events-none absolute inset-x-0 top-0 z-20 p-4 pt-[calc(env(safe-area-inset-top)+1rem)]">
            <div
                class="pointer-events-auto flex items-center justify-between rounded-2xl bg-white/90 p-4 shadow-sm backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h2 class="text-charcoal font-bold leading-tight">{{ $activeSession->tourRoute->name }}</h2>
                        <p class="text-xs text-gray-500">Misi: {{ $activeSession->points_completed }} /
                            {{ $activeSession->tourRoute->routePoints->count() }} Selesai</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-primary text-xl font-black leading-none">{{ $activeSession->total_score }}</span>
                    <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400">Poin</p>
                </div>
            </div>
        </div>

        <!-- Active Point Info (Bottom Sheet Style) -->
        @if ($activeSession->currentPoint)
            <div class="pointer-events-none absolute inset-x-0 bottom-0 z-20 p-4 pb-24">
                <div class="pointer-events-auto rounded-3xl bg-white p-6 shadow-2xl">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Tujuan Saat Ini</h3>
                    <h2 class="text-charcoal mt-1 text-xl font-black">
                        {{ $activeSession->currentPoint->locationable->name ?? 'Titik Perhentian' }}</h2>

                    <div class="mt-4 flex items-center gap-3 rounded-xl bg-blue-50 p-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-blue-900" id="distance-info">Mencari lokasi GPS...</p>
                            <p class="text-[10px] uppercase tracking-wider text-blue-700">Arahkan ke lokasi untuk membuka
                                kuis</p>
                        </div>
                    </div>

                    <button id="btn-arrive" disabled onclick="triggerArrive({{ $activeSession->currentPoint->id }})"
                        class="bg-primary mt-4 w-full rounded-xl py-3 text-center text-sm font-bold text-white opacity-50 shadow-sm transition-transform active:scale-95 disabled:cursor-not-allowed">
                        Mendekati Lokasi...
                    </button>
                </div>
            </div>
        @else
            <div class="absolute inset-0 z-50 flex items-center justify-center bg-white/90 p-4 backdrop-blur-md">
                <div class="w-full max-w-sm rounded-3xl border border-emerald-200 bg-emerald-50 p-8 text-center shadow-2xl">
                    <div
                        class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 shadow-inner">
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="mt-2 text-3xl font-black text-emerald-900">Misi Selesai!</h2>
                    <p class="mt-4 text-base leading-relaxed text-emerald-700">Selamat! Anda telah menyelesaikan seluruh
                        rute ini dengan luar biasa. Skor akhir Anda:</p>
                    <div class="my-6 rounded-2xl bg-white py-4 shadow-sm">
                        <span class="block text-4xl font-black text-emerald-600">{{ $activeSession->total_score }}</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Total Poin</span>
                    </div>
                    <a href="{{ route('home') }}"
                        class="block w-full rounded-xl bg-emerald-600 py-4 text-center text-base font-bold text-white shadow-md transition-transform hover:bg-emerald-700 active:scale-95">Kembali
                        ke Beranda</a>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Quiz -->
    <x-modal name="quiz-modal">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span
                    class="rounded-lg border border-amber-100 bg-amber-50 px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-amber-600">Tantangan
                    Kuis</span>
            </div>
            <h3 id="quiz-question" class="font-display text-charcoal text-lg font-bold leading-snug tracking-tight"></h3>

            <div id="quiz-options" class="mt-6 space-y-3">
                <!-- Options injected via JS -->
            </div>
        </div>
    </x-modal>

@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($activeSession->currentPoint)
                const targetLat = {{ $activeSession->currentPoint->locationable->mapLocation->latitude ?? 0 }};
                const targetLng = {{ $activeSession->currentPoint->locationable->mapLocation->longitude ?? 0 }};
            @else
                const targetLat = 0;
                const targetLng = 0;

                // Fire confetti when mission is completed
                var duration = 3 * 1000;
                var animationEnd = Date.now() + duration;
                var defaults = {
                    startVelocity: 30,
                    spread: 360,
                    ticks: 60,
                    zIndex: 100
                };

                function randomInRange(min, max) {
                    return Math.random() * (max - min) + min;
                }

                var interval = setInterval(function() {
                    var timeLeft = animationEnd - Date.now();

                    if (timeLeft <= 0) {
                        return clearInterval(interval);
                    }

                    var particleCount = 50 * (timeLeft / duration);
                    confetti(Object.assign({}, defaults, {
                        particleCount,
                        origin: {
                            x: randomInRange(0.1, 0.3),
                            y: Math.random() - 0.2
                        }
                    }));
                    confetti(Object.assign({}, defaults, {
                        particleCount,
                        origin: {
                            x: randomInRange(0.7, 0.9),
                            y: Math.random() - 0.2
                        }
                    }));
                }, 250);
            @endif

            const map = L.map('map', {
                zoomControl: false
            }).setView([-8.4223, 115.3595], 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            if (targetLat !== 0 && targetLng !== 0) {
                L.marker([targetLat, targetLng], {
                    icon: L.divIcon({
                        className: 'target-pin',
                        html: `<div style="background-color: #1E5128; width: 32px; height: 32px; border-radius: 50%; border: 4px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;"><svg style="width: 16px; height: 16px; color: white;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    })
                }).addTo(map);
            }

            let userMarker = null;
            let watchId = null;

            // FOR TESTING PURPOSES ONLY! Delete in production if actual GPS is strictly needed.
            // Simulasi click on map to move GPS to test arrive trigger since dev GPS might be far
            map.on('click', function(e) {
                updateUserPosition(e.latlng.lat, e.latlng.lng);
            });

            function updateUserPosition(lat, lng) {
                if (!userMarker) {
                    userMarker = L.marker([lat, lng], {
                        icon: L.divIcon({
                            className: 'user-pin',
                            html: `<div style="background-color: #3B82F6; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 15px rgba(59,130,246,0.8);"></div>`,
                            iconSize: [24, 24],
                            iconAnchor: [12, 12]
                        })
                    }).addTo(map);
                    map.setView([lat, lng], 18);
                } else {
                    userMarker.setLatLng([lat, lng]);
                }

                if (targetLat !== 0) {
                    const dist = calculateDistance(lat, lng, targetLat, targetLng);
                    const infoText = document.getElementById('distance-info');
                    const arriveBtn = document.getElementById('btn-arrive');

                    if (dist < 30) {
                        infoText.innerHTML = `Lokasi Ditemukan! (Jarak: ${dist}m)`;
                        arriveBtn.disabled = false;
                        arriveBtn.classList.remove('opacity-50');
                        arriveBtn.textContent = 'Jawab Pertanyaan & Lanjut';
                    } else {
                        infoText.textContent = `Jarak: ${dist} meter`;
                        arriveBtn.disabled = true;
                        arriveBtn.classList.add('opacity-50');
                        arriveBtn.textContent = 'Mendekati Lokasi...';
                    }
                }
            }

            if (navigator.geolocation && targetLat !== 0) {
                watchId = navigator.geolocation.watchPosition(pos => {
                    updateUserPosition(pos.coords.latitude, pos.coords.longitude);
                }, err => {
                    console.error(err);
                }, {
                    enableHighAccuracy: true
                });
            }

            function calculateDistance(lat1, lon1, lat2, lon2) {
                const R = 6371e3;
                const p1 = lat1 * Math.PI / 180;
                const p2 = lat2 * Math.PI / 180;
                const dp = (lat2 - lat1) * Math.PI / 180;
                const dl = (lon2 - lon1) * Math.PI / 180;

                const a = Math.sin(dp / 2) * Math.sin(dp / 2) +
                    Math.cos(p1) * Math.cos(p2) *
                    Math.sin(dl / 2) * Math.sin(dl / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return Math.floor(R * c);
            }

            let currentQuizzes = [];
            let currentQuizIndex = 0;

            function showQuiz() {
                const quiz = currentQuizzes[currentQuizIndex];
                document.getElementById('quiz-question').textContent =
                    `Soal ${currentQuizIndex + 1} dari ${currentQuizzes.length}: ` + quiz.question;
                const opts = document.getElementById('quiz-options');
                opts.innerHTML = `
                <button onclick="submitQuiz(${quiz.id}, 'A')" class="w-full text-left rounded-xl border-2 border-gray-100 p-4 transition hover:border-emerald-200 hover:bg-emerald-50 active:bg-emerald-100"><span class="mr-2 font-bold text-emerald-600">A.</span> <span class="font-medium text-gray-700">${quiz.option_a}</span></button>
                <button onclick="submitQuiz(${quiz.id}, 'B')" class="w-full text-left rounded-xl border-2 border-gray-100 p-4 transition hover:border-emerald-200 hover:bg-emerald-50 active:bg-emerald-100"><span class="mr-2 font-bold text-emerald-600">B.</span> <span class="font-medium text-gray-700">${quiz.option_b}</span></button>
                <button onclick="submitQuiz(${quiz.id}, 'C')" class="w-full text-left rounded-xl border-2 border-gray-100 p-4 transition hover:border-emerald-200 hover:bg-emerald-50 active:bg-emerald-100"><span class="mr-2 font-bold text-emerald-600">C.</span> <span class="font-medium text-gray-700">${quiz.option_c}</span></button>
                <button onclick="submitQuiz(${quiz.id}, 'D')" class="w-full text-left rounded-xl border-2 border-gray-100 p-4 transition hover:border-emerald-200 hover:bg-emerald-50 active:bg-emerald-100"><span class="mr-2 font-bold text-emerald-600">D.</span> <span class="font-medium text-gray-700">${quiz.option_d}</span></button>
            `;
            }

            window.triggerArrive = function(pointId) {
                console.log("triggerArrive called for point ID:", pointId);
                document.getElementById('btn-arrive').disabled = true;
                document.getElementById('btn-arrive').textContent = 'Memuat Kuis...';

                const url = `/edutourism/arrive/${pointId}`;
                console.log("Fetching URL:", url);

                fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        console.log("Response received. Status:", res.status);
                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log("Data received successfully:", data);
                        if (data.success && data.quizzes && data.quizzes.length > 0) {
                            currentQuizzes = data.quizzes;
                            currentQuizIndex = 0;
                            showQuiz();
                            window.dispatchEvent(new CustomEvent('open-quiz-modal'));
                            document.getElementById('btn-arrive').disabled = false;
                            document.getElementById('btn-arrive').textContent = 'Jawab Pertanyaan & Lanjut';
                        } else {
                            if (data.session_status === 'completed') {
                                window.location.reload();
                            } else {
                                console.log("No quizzes found for this point. Showing SweetAlert info...");
                                Swal.fire({
                                    title: 'Info',
                                    text: 'Tidak ada kuis untuk titik ini. Rute berlanjut...',
                                    icon: 'info',
                                    confirmButtonColor: '#1E5128',
                                    confirmButtonText: 'Lanjut'
                                }).then(() => {
                                    window.location.reload();
                                });
                            }
                        }
                    })
                    .catch(err => {
                        console.error("Error occurred in triggerArrive:", err);
                        document.getElementById('btn-arrive').disabled = false;
                        document.getElementById('btn-arrive').textContent = 'Jawab Pertanyaan & Lanjut';
                        Swal.fire({
                            title: 'Oops!',
                            text: 'Gagal memuat kuis.',
                            icon: 'error',
                            confirmButtonColor: '#1E5128'
                        });
                    });
            }

            window.submitQuiz = function(quizId, answer) {
                // Disable all buttons to prevent double submit
                const buttons = document.querySelectorAll('#quiz-options button');
                buttons.forEach(btn => btn.disabled = true);

                const isLast = (currentQuizIndex === currentQuizzes.length - 1);

                fetch(`/edutourism/quiz/${quizId}/submit`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            answer: answer,
                            is_last_quiz: isLast
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.is_correct) {
                            if (isLast) {
                                document.getElementById('quiz-question').innerHTML =
                                    '<span class="text-green-600 text-2xl">🎉 Semua Terjawab!</span><br><span class="text-sm">Rute dilanjutkan...</span>';
                                document.getElementById('quiz-options').innerHTML = '';
                                setTimeout(() => window.location.reload(), 1500);
                            } else {
                                document.getElementById('quiz-question').innerHTML =
                                    '<span class="text-green-600 text-2xl">✅ Benar!</span><br><span class="text-sm">Lanjut ke soal berikutnya...</span>';
                                document.getElementById('quiz-options').innerHTML = '';
                                setTimeout(() => {
                                    currentQuizIndex++;
                                    showQuiz();
                                }, 1200);
                            }
                        } else {
                            Swal.fire({
                                title: 'Salah!',
                                text: 'Jawaban Salah! Coba lagi.',
                                icon: 'error',
                                confirmButtonColor: '#1E5128'
                            });
                            buttons.forEach(btn => btn.disabled = false);
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            title: 'Oops!',
                            text: 'Gagal mengirim jawaban.',
                            icon: 'error',
                            confirmButtonColor: '#1E5128'
                        });
                        buttons.forEach(btn => btn.disabled = false);
                    });
            }
        });
    </script>
@endpush
