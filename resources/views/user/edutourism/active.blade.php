@extends('layouts.app')

@section('title', 'Smart Edutourism - ' . $activeSession->tourRoute->name)

@push('styles')
    <style>
        .leaflet-control-attribution {
            display: none !important;
        }

        .leaflet-container:focus {
            outline: none;
        }

        @keyframes quiz-shake {

            10%,
            90% {
                transform: translateX(-2px);
            }

            20%,
            80% {
                transform: translateX(4px);
            }

            30%,
            50%,
            70% {
                transform: translateX(-8px);
            }

            40%,
            60% {
                transform: translateX(8px);
            }
        }

        .quiz-shake {
            animation: quiz-shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
        }

        @keyframes quiz-float-up {
            0% {
                opacity: 0;
                transform: translate(-50%, 0);
            }

            20% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                transform: translate(-50%, -40px);
            }
        }

        .quiz-score-badge {
            animation: quiz-float-up 1.4s ease-out forwards;
        }

        .quiz-option-highlight,
        .quiz-option-highlight span {
            color: #fff !important;
        }

        .quiz-option-correct,
        .quiz-option-correct:hover,
        .quiz-option-correct:active {
            background-color: #16a34a !important;
            border-color: #16a34a !important;
        }

        .quiz-option-incorrect,
        .quiz-option-incorrect:hover,
        .quiz-option-incorrect:active {
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
        }

        @keyframes quiz-success-pop {
            0% {
                opacity: 0;
                transform: scale(0);
            }

            60% {
                opacity: 1;
                transform: scale(1.15);
            }

            100% {
                transform: scale(1);
            }
        }

        .quiz-success-icon {
            animation: quiz-success-pop 0.45s cubic-bezier(.34, 1.56, .64, 1) both;
        }

        @keyframes quiz-success-draw {
            to {
                stroke-dashoffset: 0;
            }
        }

        .quiz-success-check {
            stroke-dasharray: 30;
            stroke-dashoffset: 30;
            animation: quiz-success-draw 0.4s ease-out 0.25s forwards;
        }

        .quiz-success-check-pin {
            stroke-dasharray: 60;
            stroke-dashoffset: 60;
            animation: quiz-success-draw 0.5s ease-out 0.25s forwards;
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
                        <p class="text-xs text-gray-500">{{ __('Misi: :completed / :total Selesai', ['completed' => $activeSession->points_completed, 'total' => $activeSession->tourRoute->routePoints->count()]) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="text-right">
                        <span class="text-primary text-xl font-black leading-none">{{ $activeSession->total_score }}</span>
                        <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400">{{ __('Poin') }}</p>
                    </div>
                    <button type="button" id="btn-stop-route" onclick="stopRoute()"
                        aria-label="{{ __('Berhenti dari Rute?') }}"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-gray-300 transition-colors hover:bg-gray-100 hover:text-gray-500">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 6a2 2 0 100-4 2 2 0 000 4zM12 14a2 2 0 100-4 2 2 0 000 4zM12 22a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Active Point Info (Bottom Sheet Style) -->
        @if ($activeSession->currentPoint)
            <div class="pointer-events-none absolute inset-x-0 bottom-0 z-20 p-4 pb-24">
                <div class="pointer-events-auto rounded-3xl bg-white p-6 shadow-2xl">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ __('Tujuan Saat Ini') }}</h3>
                    <h2 class="text-charcoal mt-1 text-xl font-black">
                        {{ $activeSession->currentPoint->locationable->name ?? __('Titik Perhentian') }}</h2>

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
                            <p class="text-sm font-bold text-blue-900" id="distance-info">{{ __('Mencari lokasi GPS...') }}</p>
                            <p class="text-[10px] uppercase tracking-wider text-blue-700">{{ __('Arahkan ke lokasi untuk membuka kuis') }}</p>
                        </div>
                    </div>

                    <button id="btn-arrive" disabled onclick="triggerArrive({{ $activeSession->currentPoint->id }})"
                        class="bg-primary mt-4 w-full rounded-xl py-3 text-center text-sm font-bold text-white opacity-50 shadow-sm transition-transform active:scale-95 disabled:cursor-not-allowed">
                        {{ __('Mendekati Lokasi...') }}
                    </button>
                </div>
            </div>
        @else
            @php
                $totalAnswers = $activeSession->quizAnswers->count();
                $correctAnswers = $activeSession->quizAnswers->where('is_correct', true)->count();
                $correctRatio = $totalAnswers > 0 ? $correctAnswers / $totalAnswers : null;

                if ($correctRatio === null) {
                    $tier = 'neutral';
                } elseif ($correctRatio >= 1.0) {
                    $tier = 'perfect';
                } elseif ($correctRatio >= 0.5) {
                    $tier = 'good';
                } else {
                    $tier = 'basic';
                }

                $tierContent = [
                    'perfect' => [
                        'title' => __('Skor Sempurna!'),
                        'message' => __('Luar biasa! Anda menjawab semua pertanyaan dengan benar. Skor akhir Anda:'),
                        'accent' => 'amber',
                        'icon' => 'star',
                    ],
                    'good' => [
                        'title' => __('Misi Selesai!'),
                        'message' => __('Selamat! Anda telah menyelesaikan seluruh rute ini dengan baik. Skor akhir Anda:'),
                        'accent' => 'emerald',
                        'icon' => 'check',
                    ],
                    'basic' => [
                        'title' => __('Rute Selesai!'),
                        'message' => __('Anda telah menyelesaikan rute ini. Masih ada beberapa hal menarik untuk dipelajari ulang. Skor akhir Anda:'),
                        'accent' => 'blue',
                        'icon' => 'flag',
                    ],
                    'neutral' => [
                        'title' => __('Rute Selesai!'),
                        'message' => __('Selamat! Anda telah menjelajahi seluruh rute ini.'),
                        'accent' => 'emerald',
                        'icon' => 'pin',
                    ],
                ][$tier];

                $accentClasses = [
                    'amber' => [
                        'icon_bg' => 'bg-amber-100',
                        'icon_text' => 'text-amber-600',
                        'title' => 'text-amber-900',
                        'score_bg' => 'bg-amber-50',
                        'score_border' => 'border-amber-100',
                        'score_text' => 'text-amber-600',
                        'score_label' => 'text-amber-400',
                        'button_bg' => 'bg-amber-600',
                        'button_hover' => 'hover:bg-amber-700',
                    ],
                    'emerald' => [
                        'icon_bg' => 'bg-emerald-100',
                        'icon_text' => 'text-emerald-600',
                        'title' => 'text-emerald-900',
                        'score_bg' => 'bg-emerald-50',
                        'score_border' => 'border-emerald-100',
                        'score_text' => 'text-emerald-600',
                        'score_label' => 'text-emerald-400',
                        'button_bg' => 'bg-emerald-600',
                        'button_hover' => 'hover:bg-emerald-700',
                    ],
                    'blue' => [
                        'icon_bg' => 'bg-blue-100',
                        'icon_text' => 'text-blue-600',
                        'title' => 'text-blue-900',
                        'score_bg' => 'bg-blue-50',
                        'score_border' => 'border-blue-100',
                        'score_text' => 'text-blue-600',
                        'score_label' => 'text-blue-400',
                        'button_bg' => 'bg-blue-600',
                        'button_hover' => 'hover:bg-blue-700',
                    ],
                ][$tierContent['accent']];
            @endphp

            <div class="absolute inset-0 z-50 overflow-y-auto bg-white">
                <div class="mx-auto max-w-md px-6 py-10 pb-28 text-center md:max-w-lg lg:max-w-xl">
                    <div
                        class="quiz-success-icon mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full {{ $accentClasses['icon_bg'] }} {{ $accentClasses['icon_text'] }} shadow-inner md:h-24 md:w-24 lg:h-28 lg:w-28">
                        @if ($tierContent['icon'] === 'star')
                            <svg class="h-10 w-10 md:h-12 md:w-12 lg:h-14 lg:w-14" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l2.9 6.26L21.5 9.27l-4.75 4.63 1.12 6.55L12 17.27l-5.87 3.18 1.12-6.55L2.5 9.27l6.6-1.01L12 2z" />
                            </svg>
                        @elseif ($tierContent['icon'] === 'flag')
                            <svg class="h-10 w-10 md:h-12 md:w-12 lg:h-14 lg:w-14" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M5 3a1 1 0 00-1 1v17h2v-6h11.382a1 1 0 00.894-1.447L16 9l2.276-4.553A1 1 0 0017.382 3H6V3a1 1 0 00-1-1z" />
                            </svg>
                        @elseif ($tierContent['icon'] === 'pin')
                            <svg class="quiz-success-check-pin h-10 w-10 md:h-12 md:w-12 lg:h-14 lg:w-14" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        @else
                            <svg class="quiz-success-check h-10 w-10 md:h-12 md:w-12 lg:h-14 lg:w-14" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        @endif
                    </div>
                    <h2 class="mt-2 text-3xl font-black {{ $accentClasses['title'] }} md:text-4xl">{{ $tierContent['title'] }}</h2>
                    <p class="mt-4 text-base leading-relaxed text-gray-600 lg:text-lg">{{ $tierContent['message'] }}</p>
                    <div class="my-6 rounded-2xl border {{ $accentClasses['score_bg'] }} {{ $accentClasses['score_border'] }} py-4 shadow-sm">
                        <span class="block text-4xl font-black {{ $accentClasses['score_text'] }} lg:text-5xl">{{ $activeSession->total_score }}</span>
                        <span class="text-xs font-bold uppercase tracking-wider {{ $accentClasses['score_label'] }}">{{ __('Total Poin') }}</span>
                    </div>
                    @if ($activeSession->quizAnswers->isNotEmpty())
                        <div class="mb-6 space-y-3 text-left">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ __('Ringkasan Kuis') }}</h3>
                            @foreach ($activeSession->quizAnswers as $answer)
                                @php($quiz = $answer->quiz)
                                @continue(! $quiz)
                                <div class="rounded-xl border {{ $answer->is_correct ? 'border-emerald-100 bg-emerald-50' : 'border-amber-100 bg-amber-50' }} p-3">
                                    <p class="text-sm font-bold text-gray-800">{{ $quiz->question }}</p>
                                    <p class="mt-1 text-xs {{ $answer->is_correct ? 'text-emerald-700' : 'text-amber-700' }}">
                                        {{ $answer->is_correct ? __('Benar') : __('Salah, jawaban yang benar: ') . $quiz->{'option_' . strtolower($quiz->correct_option)} }}
                                    </p>
                                    @if ($quiz->explanation)
                                        <p class="mt-1 text-xs text-gray-600">{{ $quiz->explanation }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="fixed inset-x-0 bottom-0 z-50 border-t border-gray-100 bg-white/95 p-4 backdrop-blur-sm">
                    <a href="{{ route('home') }}"
                        class="mx-auto block max-w-md rounded-xl {{ $accentClasses['button_bg'] }} py-4 text-center text-base font-bold text-white shadow-md transition-transform {{ $accentClasses['button_hover'] }} active:scale-95 md:max-w-lg lg:max-w-xl">{{ __('Kembali ke Beranda') }}</a>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Quiz -->
    <x-modal name="quiz-modal">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span
                    class="rounded-lg border border-amber-100 bg-amber-50 px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-amber-600">{{ __('Tantangan Kuis') }}</span>
            </div>
            <h3 id="quiz-question" class="font-display text-charcoal text-lg font-bold leading-snug tracking-tight"></h3>

            <div id="quiz-options" class="mt-6 space-y-3">
                <!-- Options injected via JS -->
            </div>
        </div>
    </x-modal>


    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        (function() {
                let mapInstance = null;
                let watchId = null;
                const hasCurrentPoint = @json((bool) $activeSession->currentPoint);
                const targetLat = {{ $activeSession->currentPoint?->locationable->mapLocation->latitude ?? 0 }};
                const targetLng = {{ $activeSession->currentPoint?->locationable->mapLocation->longitude ?? 0 }};

                const initActiveEdutourism = function() {
                    const mapEl = document.getElementById('map');
                    if (mapEl && !mapInstance) {
                        if (!hasCurrentPoint) {
                            const duration = 3 * 1000;
                            const animationEnd = Date.now() + duration;
                            const defaults = {
                                startVelocity: 30,
                                spread: 360,
                                ticks: 60,
                                zIndex: 100
                            };

                            function randomInRange(min, max) {
                                return Math.random() * (max - min) + min;
                            }

                            const interval = setInterval(function() {
                                const timeLeft = animationEnd - Date.now();

                                if (timeLeft <= 0) {
                                    clearInterval(interval);
                                    return;
                                }

                                const particleCount = 50 * (timeLeft / duration);
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
                        }

                        const map = L.map(mapEl, {
                            zoomControl: false
                        }).setView([-8.4223, 115.3595], 17);
                        mapInstance = map;
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

                                if (infoText && arriveBtn) {
                                    if (dist < 30) {
                                        infoText.innerHTML = `{{ __('Lokasi Ditemukan!') }} ({{ __('Jarak') }}: ${dist}m)`;
                                        arriveBtn.disabled = false;
                                        arriveBtn.classList.remove('opacity-50');
                                        arriveBtn.textContent = @js(__('Jawab Pertanyaan & Lanjut'));
                                    } else {
                                        infoText.textContent = `{{ __('Jarak') }}: ${dist} {{ __('meter') }}`;
                                        arriveBtn.disabled = true;
                                        arriveBtn.classList.add('opacity-50');
                                        arriveBtn.textContent = "{{ __('Mendekati Lokasi...') }}";
                                    }
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
                            const quizQuestionEl = document.getElementById('quiz-question');
                            if (quizQuestionEl) {
                                quizQuestionEl.textContent =
                                    `{{ __('Soal') }} ${currentQuizIndex + 1} {{ __('dari') }} ${currentQuizzes.length}: ` + quiz.question;
                            }
                            const opts = document.getElementById('quiz-options');
                            if (opts) {
                                opts.innerHTML = `
                        <button data-option="A" onclick="submitQuiz(${quiz.id}, 'A')" class="w-full text-left rounded-xl border-2 border-gray-100 p-4 transition hover:border-emerald-200 hover:bg-emerald-50 active:bg-emerald-100"><span class="mr-2 font-bold text-emerald-600">A.</span> <span class="font-medium text-gray-700">${quiz.option_a}</span></button>
                        <button data-option="B" onclick="submitQuiz(${quiz.id}, 'B')" class="w-full text-left rounded-xl border-2 border-gray-100 p-4 transition hover:border-emerald-200 hover:bg-emerald-50 active:bg-emerald-100"><span class="mr-2 font-bold text-emerald-600">B.</span> <span class="font-medium text-gray-700">${quiz.option_b}</span></button>
                        <button data-option="C" onclick="submitQuiz(${quiz.id}, 'C')" class="w-full text-left rounded-xl border-2 border-gray-100 p-4 transition hover:border-emerald-200 hover:bg-emerald-50 active:bg-emerald-100"><span class="mr-2 font-bold text-emerald-600">C.</span> <span class="font-medium text-gray-700">${quiz.option_c}</span></button>
                        <button data-option="D" onclick="submitQuiz(${quiz.id}, 'D')" class="w-full text-left rounded-xl border-2 border-gray-100 p-4 transition hover:border-emerald-200 hover:bg-emerald-50 active:bg-emerald-100"><span class="mr-2 font-bold text-emerald-600">D.</span> <span class="font-medium text-gray-700">${quiz.option_d}</span></button>
                    `;
                            }
                        }

                        window.triggerArrive = function(pointId) {
                            console.log("triggerArrive called for point ID:", pointId);
                            const btnArrive = document.getElementById('btn-arrive');
                            if (btnArrive) {
                                btnArrive.disabled = true;
                                btnArrive.textContent = "{{ __('Memuat Kuis...') }}";
                            }

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
                                        document.getElementById('btn-arrive').textContent =
                                            @js(__('Jawab Pertanyaan & Lanjut'));
                                    } else {
                                        if (data.session_status === 'completed') {
                                            window.location.reload();
                                        } else {
                                            console.log(
                                                "No quizzes found for this point. Showing SweetAlert info..."
                                            );
                                            Swal.fire({
                                                title: "{{ __('Info') }}",
                                                text: "{{ __('Tidak ada kuis untuk titik ini. Rute berlanjut...') }}",
                                                icon: 'info',
                                                confirmButtonColor: '#1E5128',
                                                confirmButtonText: "{{ __('Lanjut') }}"
                                            }).then(() => {
                                                window.location.reload();
                                            });
                                        }
                                    }
                                })
                                .catch(err => {
                                    console.error("Error occurred in triggerArrive:", err);
                                    document.getElementById('btn-arrive').disabled = false;
                                    document.getElementById('btn-arrive').textContent =
                                        @js(__('Jawab Pertanyaan & Lanjut'));
                                    Swal.fire({
                                        title: "{{ __('Oops!') }}",
                                        text: "{{ __('Gagal memuat kuis.') }}",
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
                            const selectedBtn = document.querySelector(`#quiz-options button[data-option="${answer}"]`);

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
                                    const advanceDelay = data.is_correct ? 1500 : 2800;

                                    if (data.is_correct) {
                                        if (selectedBtn) {
                                            selectedBtn.classList.add('quiz-option-highlight',
                                                'quiz-option-correct');
                                        }
                                        confetti({
                                            particleCount: 80,
                                            spread: 70,
                                            origin: {
                                                y: 0.6
                                            }
                                        });
                                        const badge = document.createElement('div');
                                        badge.className =
                                            'quiz-score-badge fixed left-1/2 top-1/3 z-[60] text-3xl font-black text-green-600';
                                        badge.textContent = '+100';
                                        document.body.appendChild(badge);
                                        setTimeout(() => badge.remove(), 1500);
                                    } else {
                                        if (selectedBtn) {
                                            selectedBtn.classList.add('quiz-option-highlight',
                                                'quiz-option-incorrect', 'quiz-shake');
                                        }
                                        const correctBtn = document.querySelector(
                                            `#quiz-options button[data-option="${data.correct_option}"]`);
                                        if (correctBtn) {
                                            correctBtn.classList.add('quiz-option-highlight',
                                                'quiz-option-correct', 'animate-pulse');
                                        }
                                    }

                                    setTimeout(() => {
                                        if (isLast) {
                                            document.getElementById('quiz-question').innerHTML =
                                                `<div class="flex flex-col items-center gap-2 py-2 text-center">
                                                    <div class="quiz-success-icon flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                                        <svg class="quiz-success-check h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                    <span class="font-display text-lg font-black text-emerald-600">{{ __('Semua Terjawab!') }}</span>
                                                    <span class="text-sm text-gray-400">{{ __('Rute dilanjutkan...') }}</span>
                                                </div>`;
                                            document.getElementById('quiz-options').innerHTML = '';
                                            setTimeout(() => window.location.reload(), 1200);
                                        } else {
                                            currentQuizIndex++;
                                            showQuiz();
                                        }
                                    }, advanceDelay);
                                })
                                .catch(err => {
                                    Swal.fire({
                                        title: "{{ __('Oops!') }}",
                                        text: "{{ __('Gagal mengirim jawaban.') }}",
                                        icon: 'error',
                                        confirmButtonColor: '#1E5128'
                                    });
                                    buttons.forEach(btn => btn.disabled = false);
                                });
                        }

                        window.stopRoute = function() {
                            const btn = document.getElementById('btn-stop-route');
                            Swal.fire({
                                title: "{{ __('Berhenti dari Rute?') }}",
                                text: "{{ __('Progres Anda akan hilang jika berhenti sekarang.') }}",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: "{{ __('Ya, Berhenti') }}",
                                cancelButtonText: "{{ __('Batal') }}",
                                confirmButtonColor: '#E65100'
                            }).then(result => {
                                if (!result.isConfirmed) {
                                    return;
                                }

                                if (btn) {
                                    btn.disabled = true;
                                }

                                fetch('/edutourism/stop', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        }
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            if (window.history.length > 1) {
                                                window.history.back();
                                            } else {
                                                window.location.href = data.redirect;
                                            }
                                        }
                                    })
                                    .catch(() => {
                                        if (btn) {
                                            btn.disabled = false;
                                        }
                                        Swal.fire({
                                            title: "{{ __('Oops!') }}",
                                            text: "{{ __('Gagal menghentikan rute.') }}",
                                            icon: 'error',
                                            confirmButtonColor: '#1E5128'
                                        });
                                    });
                            });
                        }
                    }
                };

                // Run immediately
                initActiveEdutourism();

                // Clean up GPS watch position and map instance when navigating away via Livewire
                document.addEventListener('livewire:navigating', function cleanup(e) {
                    if (watchId !== null) {
                        navigator.geolocation.clearWatch(watchId);
                        watchId = null;
                    }
                    if (mapInstance) {
                        mapInstance.remove();
                        mapInstance = null;
                    }
                    delete window.triggerArrive;
                    delete window.submitQuiz;
                    delete window.stopRoute;
                    document.removeEventListener('livewire:navigating', cleanup);
                });
        })();
    </script>
@endsection
