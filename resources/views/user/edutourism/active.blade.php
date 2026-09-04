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

        @keyframes sheet-slide-up {
            from {
                opacity: 0;
                transform: translateY(100%);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sheet-slide-up {
            animation: sheet-slide-up 0.3s ease-out both;
        }

        /* Sebagian orang mual atau pusing oleh gerakan. Animasi dimatikan, tapi elemen yang
           bergantung padanya (centang yang digambar via stroke-dashoffset) tetap dipaksa ke
           keadaan akhir supaya tidak hilang sama sekali. */
        @media (prefers-reduced-motion: reduce) {

            .quiz-shake,
            .quiz-score-badge,
            .quiz-success-icon,
            .quiz-success-check,
            .quiz-success-check-pin,
            .sheet-slide-up {
                animation: none !important;
            }

            .quiz-success-check,
            .quiz-success-check-pin {
                stroke-dashoffset: 0 !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="fixed inset-x-0 bottom-0 top-0 z-0 bg-[#E5E3DF]">
        <div id="map" class="absolute inset-0 z-0"></div>

        {{-- Ditaruh di bawah header, bukan di atas bottom sheet: tinggi sheet berubah-ubah
             mengikuti jumlah tombol titik, jadi jangkar atas satu-satunya posisi yang tidak
             pernah tertutup. Satelit membantu mengenali bangunan asli saat mencari titik. --}}
        <div class="absolute right-4 top-[calc(env(safe-area-inset-top)+8.5rem)] z-20">
            <x-map-style-fab />
        </div>

        <!-- Top Overlay -->
        <div class="pointer-events-none absolute inset-x-0 top-0 z-20 p-4 pt-[calc(env(safe-area-inset-top)+1rem)]">
            <div
                class="pointer-events-auto flex items-center justify-between rounded-2xl bg-white/90 p-4 shadow-sm backdrop-blur-sm">
                @php
                    $totalPoints = $activeSession->tourRoute->routePoints->count();
                    $donePoints = $activeSession->points_completed;
                    $progressPct = $totalPoints > 0 ? round(($donePoints / $totalPoints) * 100) : 0;
                @endphp
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <a href="{{ route('home') }}" aria-label="{{ __('Kembali') }}"
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-charcoal truncate font-bold leading-tight">
                            {{ $activeSession->tourRoute->name }}</h2>
                        <p class="text-xs text-gray-500">
                            {{ __('Misi: :completed / :total Selesai', ['completed' => $donePoints, 'total' => $totalPoints]) }}
                        </p>
                        {{-- Progres sebagai bar, bukan hanya angka: terbaca sekilas sambil berjalan
                             di bawah matahari, saat teks kecil praktis tidak terbaca. me-3 menjaga
                             ujungnya tidak menempel ke angka poin di sebelah kanan. --}}
                        <div class="me-3 mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-200" role="progressbar"
                            aria-valuenow="{{ $donePoints }}" aria-valuemin="0" aria-valuemax="{{ $totalPoints }}"
                            aria-label="{{ __('Progres rute') }}">
                            <div class="bg-primary h-full rounded-full transition-all duration-500"
                                style="width: {{ $progressPct }}%"></div>
                        </div>
                    </div>
                </div>
                {{-- Sebaris, bukan bertumpuk: tombol berlabel sudah setinggi 44px, jadi menumpuknya
                     di bawah skor akan menaikkan tinggi header dan memakan area peta. --}}
                <div class="flex shrink-0 items-center gap-2">
                    <div class="text-right">
                        <span
                            class="text-primary block text-xl font-black leading-none">{{ $activeSession->total_score }}</span>
                        <span
                            class="mt-0.5 block text-[10px] font-bold uppercase tracking-wider text-gray-500">{{ __('Poin') }}</span>
                    </div>
                    {{-- Dulu ikon titik-tiga, yang menjanjikan sebuah menu padahal isinya satu aksi
                         destruktif. Sekarang diberi label eksplisit dan warna Alert Amber, supaya
                         tidak tertukar dengan panah kembali di kiri yang tidak menghentikan sesi. --}}
                    <button type="button" id="btn-stop-route" onclick="stopRoute()"
                        class="tap-target text-warning border-warning/30 hover:bg-warning/10 flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider transition-colors active:scale-95">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <rect x="6" y="6" width="12" height="12" rx="2" />
                        </svg>
                        {{ __('Berhenti') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Active Point Info (Bottom Sheet Style) -->
        @if ($activeSession->currentPoint)
            <div class="pointer-events-none absolute inset-x-0 bottom-0 z-20 md:mx-auto md:max-w-md md:p-4">
                <div class="sheet-slide-up pointer-events-auto rounded-t-[2.5rem] bg-white p-6 shadow-2xl md:rounded-b-3xl"
                    style="padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));">
                    <div class="mx-auto -mt-2 mb-3 h-1.5 w-12 rounded-full bg-gray-200"></div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ __('Tujuan Saat Ini') }}</h3>
                    <h2 class="text-charcoal mt-1 text-xl font-black">
                        {{ $activeSession->currentPoint->locationable->name ?? __('Titik Perhentian') }}</h2>

                    {{-- Kartu status GPS. Warnanya berubah per keadaan (mencari / jauh / sampai /
                         sinyal lemah), tapi teksnya selalu ikut berubah, jadi keadaan tetap
                         terbaca tanpa membedakan warna. --}}
                    <div id="gps-status"
                        class="mt-4 flex items-center gap-3 rounded-2xl border border-blue-100 bg-blue-50 p-3 transition-colors">
                        <div id="gps-status-icon"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            {{-- Jarak adalah satu-satunya angka yang berubah terus dan menentukan
                                 apakah tombol terbuka, jadi ia yang dibesarkan. aria-live membuat
                                 pembaruannya ikut diumumkan pembaca layar. --}}
                            <p class="text-base font-bold leading-tight text-blue-900" id="distance-info"
                                aria-live="polite">{{ __('Mencari lokasi GPS...') }}</p>
                            <p class="mt-0.5 text-[11px] uppercase tracking-wider text-blue-700" id="gps-hint">
                                {{ __('Arahkan ke lokasi untuk membuka kuis') }}</p>
                        </div>
                    </div>

                    {{-- Nonaktif pakai warna solid, bukan opacity: teks putih di atas hijau 50%
                         jatuh di bawah rasio kontras 4.5:1 dan hilang di bawah matahari. --}}
                    <button id="btn-arrive" disabled onclick="triggerArrive({{ $activeSession->currentPoint->id }})"
                        class="bg-primary mt-4 min-h-12 w-full rounded-xl py-3 text-center text-sm font-bold text-white shadow-sm transition-transform active:scale-95 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-700 disabled:shadow-none">
                        {{ __('Mendekati Lokasi...') }}
                    </button>

                    @if ($activeSession->currentPoint->locationable instanceof \App\Models\CulturalObject)
                        <a id="btn-detail-object"
                            href="{{ route('cultural-object', ['slug' => $activeSession->currentPoint->locationable->slug]) }}"
                            target="_blank"
                            class="text-primary border-primary/30 mt-2 flex hidden w-full items-center justify-center gap-2 rounded-xl border-2 bg-white py-3 text-center text-sm font-bold transition-transform active:scale-95">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            {{ __('Lihat Detail Objek Budaya') }}
                        </a>
                    @endif

                    @if (
                        $activeSession->currentPoint->locationable instanceof \App\Models\CulturalObject &&
                            $activeSession->currentPoint->locationable->arModel)
                        <a id="btn-scan-qr"
                            href="{{ route('ar-scan', ['route_point_id' => $activeSession->currentPoint->id, 'edutourism_return' => 1]) }}"
                            class="text-primary border-primary/30 mt-2 flex hidden w-full items-center justify-center gap-2 rounded-xl border-2 bg-white py-3 text-center text-sm font-bold transition-transform active:scale-95">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 13.5h2.25v2.25H13.5v-2.25zM18.75 13.5H21v2.25h-2.25V13.5zM13.5 18.75h2.25V21H13.5v-2.25zM18.75 18.75H21V21h-2.25v-2.25z" />
                            </svg>
                            {{ __('Scan QR di Lokasi') }}
                        </a>
                    @endif
                </div>
            </div>
        @else
            @php
                $maxRouteScore = $activeSession->tourRoute->routePoints->flatMap->missions->sum('points');
                $scoreRatio = $maxRouteScore > 0 ? $activeSession->total_score / $maxRouteScore : null;

                if ($scoreRatio === null) {
                    $tier = 'neutral';
                } elseif ($scoreRatio >= 1.0) {
                    $tier = 'perfect';
                } elseif ($scoreRatio >= 0.5) {
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
                        'message' => __(
                            'Selamat! Anda telah menyelesaikan seluruh rute ini dengan baik. Skor akhir Anda:',
                        ),
                        'accent' => 'emerald',
                        'icon' => 'check',
                    ],
                    'basic' => [
                        'title' => __('Rute Selesai!'),
                        'message' => __(
                            'Anda telah menyelesaikan rute ini. Masih ada beberapa hal menarik untuk dipelajari ulang. Skor akhir Anda:',
                        ),
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
                        class="quiz-success-icon {{ $accentClasses['icon_bg'] }} {{ $accentClasses['icon_text'] }} mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full shadow-inner md:h-24 md:w-24 lg:h-28 lg:w-28">
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
                    <h2 class="{{ $accentClasses['title'] }} mt-2 text-3xl font-black md:text-4xl">
                        {{ $tierContent['title'] }}</h2>
                    <p class="mt-4 text-base leading-relaxed text-gray-600 lg:text-lg">{{ $tierContent['message'] }}</p>
                    <div
                        class="{{ $accentClasses['score_bg'] }} {{ $accentClasses['score_border'] }} my-6 rounded-2xl border py-4 shadow-sm">
                        <span
                            class="{{ $accentClasses['score_text'] }} block text-4xl font-black lg:text-5xl">{{ $activeSession->total_score }}</span>
                        <span
                            class="{{ $accentClasses['score_label'] }} text-xs font-bold uppercase tracking-wider">{{ __('Total Poin') }}</span>
                    </div>

                    @if ($activeSession->badge_awarded)
                        <div
                            class="quiz-success-icon mb-6 rounded-2xl border border-[#D4AF37]/40 bg-gradient-to-b from-amber-50 to-white p-5 shadow-sm">
                            <div
                                class="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-full bg-[#D4AF37]/15 text-[#B8962E]">
                                <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M5 3h14v2h2v4a4 4 0 01-3.42 3.96A6.01 6.01 0 0113 16.92V19h3v2H8v-2h3v-2.08a6.01 6.01 0 01-4.58-3.96A4 4 0 013 9V5h2V3zm0 4v2a2 2 0 001.18 1.82A8.2 8.2 0 016 9V7H5zm14 0h-1v2c0 .61-.06 1.22-.18 1.82A2 2 0 0019 9V7z" />
                                </svg>
                            </div>
                            <p class="text-[11px] font-bold uppercase tracking-widest text-[#B8962E]">
                                {{ __('Predikat Diraih') }}</p>
                            <p class="font-display text-charcoal mt-1 text-xl font-black">
                                {{ $activeSession->badge_awarded }}</p>
                        </div>
                    @endif

                    @if (!empty($activeSession->collectibles_earned))
                        <div class="mb-6 text-left">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                {{ __('Koleksi Didapat') }}</h3>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($activeSession->collectibles_earned as $collectible)
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                        🎖️
                                        {{ $collectible === 'digital_passport' ? __('Digital Passport') : \Illuminate\Support\Str::of($collectible)->replace('_', ' ')->title() }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="fixed inset-x-0 bottom-0 z-50 border-t border-gray-100 bg-white/95 p-4 backdrop-blur-sm">
                    <a href="{{ route('home') }}"
                        class="{{ $accentClasses['button_bg'] }} {{ $accentClasses['button_hover'] }} mx-auto block max-w-md rounded-xl py-4 text-center text-base font-bold text-white shadow-md transition-transform active:scale-95 md:max-w-lg lg:max-w-xl">{{ __('Kembali ke Beranda') }}</a>
                </div>
            </div>
        @endif
    </div>

    @php($pointMissions = $activeSession->currentPoint?->missions ?? collect())
    @php($completedMissionIds = $activeSession->missions_completed ?? [])

    <!-- Mission Runner Overlay (gamified missions per route point) -->
    @if ($pointMissions->isNotEmpty())
        {{-- Hoisted out of the individual games on purpose. Each game still @includes this (they
        stay self-contained), but the @once inside would otherwise emit the stylesheet inside a
        <template x-if> — meaning the `edu-*` classes, including the sticky CTA bar, would only
        exist in the DOM while that one mission happened to be mounted. Emitting it here, above the
        mission loop, makes the @once fire at the top level and every game inherit the same feel. --}}
        @include('user.edutourism.games.partials.game-fx')

        {{-- Each game's eduGame*() factory is hoisted here for the same reason, and it is not
        cosmetic: a <script> inside a <template> is inert until Alpine clones it, and the games'
        @once emitted theirs into the *first* mission of that type only. Resume a session on
        mission 2 and mission 1's template never mounts, so the factory never runs and every
        binding on the board dies with "eduGameMatching is not defined". Emitting one script per
        distinct type up here means the factories exist before any mission is cloned. --}}
        @foreach ($pointMissions->pluck('type')->unique() as $missionType)
            @include('user.edutourism.games.partials.' . str_replace('_', '-', $missionType) . '-script')
        @endforeach

        <div x-data="missionRunner(@js($pointMissions->map(fn($m) => ['id' => $m->id])->values()), @js($completedMissionIds))" x-show="open" x-cloak @open-mission-runner.window="openRunner()"
            @mission-complete="onMissionComplete($event.detail)" class="fixed inset-0 z-[60] flex flex-col bg-[#FAF9F6]">
            <div
                class="flex items-center gap-3 border-b border-gray-100 bg-white p-4 pt-[calc(env(safe-area-inset-top)+1rem)]">
                <button type="button" @click="open = false; $dispatch('close-mission-runner')"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="min-w-0 flex-1">
                    <h2 class="text-charcoal truncate font-bold leading-tight">
                        {{ $activeSession->currentPoint->locationable->name ?? __('Titik Perhentian') }}</h2>
                    <p class="text-xs text-gray-500" x-show="stage === 'mission'">
                        {{ __('Misi') }} <span x-text="index + 1"></span>/<span x-text="missions.length"></span>
                    </p>
                </div>
                <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-100">
                    <div class="bg-primary h-full rounded-full transition-all"
                        :style="`width: ${stage === 'intro' ? 0 : Math.round(index / missions.length * 100)}%`"></div>
                </div>
            </div>

            <div class="edu-mission-scroll flex-1 overflow-y-auto p-5">
                <template x-if="stage === 'intro'">
                    <div class="mx-auto max-w-md space-y-5">
                        @if ($activeSession->currentPoint->intro_video_path)
                            <video src="{{ route('audio.stream', $activeSession->currentPoint->intro_video_path) }}"
                                controls playsinline class="w-full rounded-2xl border border-gray-100 shadow-sm"></video>
                        @endif
                        @if ($activeSession->currentPoint->intro_audio_path)
                            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500">
                                    {{ __('Audio Pengantar') }}</h3>
                                <audio src="{{ route('audio.stream', $activeSession->currentPoint->intro_audio_path) }}"
                                    controls class="w-full"></audio>
                            </div>
                        @endif
                        @if ($activeSession->currentPoint->storytelling_content)
                            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                    {{ __('Tentang Titik Ini') }}</h3>
                                <p class="font-display text-charcoal mt-2 text-base leading-relaxed">
                                    {{ $activeSession->currentPoint->storytelling_content }}</p>
                            </div>
                        @endif
                        <button type="button" @click="stage = 'mission'"
                            class="bg-primary w-full rounded-xl py-4 text-center text-base font-bold text-white shadow-md transition-transform active:scale-95">
                            🎯 {{ __('Mulai Misi') }} (<span x-text="missions.length"></span>)
                        </button>
                    </div>
                </template>

                <div x-show="stage === 'mission'">
                    @foreach ($pointMissions as $i => $mission)
                        {{-- x-if (not x-show): defers mounting each game's x-data until it's the
                        active mission — otherwise every mission on the point mounts up front and
                        e.g. sequence.blade.php's countdown timer starts immediately, before the
                        player even taps "Mulai Misi". --}}
                        <template x-if="index === {{ $i }}">
                            {{-- Width is per mission, not per point. The puzzle needs the wide
                            column — its board sits beside a reference photo, and max-w-md would
                            shrink both to about a third of a playable size — but when it shares a
                            point with a quiz or a riddle, those used to inherit the wide column
                            too: option cards stretched to 56rem while the docked CTA below them
                            stayed at its own 28rem, which read as a layout bug on a desktop
                            screen. Everything except the puzzle stays a single readable column,
                            the same width as the CTA. --}}
                            <div class="mx-auto space-y-4 {{ $mission->type === 'puzzle' ? 'max-w-4xl' : 'max-w-md' }}"
                                x-cloak>
                                <div class="flex items-center justify-between">
                                    <span
                                        class="rounded-lg border border-amber-100 bg-amber-50 px-2.5 py-0.5 text-[11px] font-extrabold uppercase tracking-wider text-amber-600">{{ __('Misi') }}
                                        {{ $i + 1 }}</span>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">+
                                        {{ $mission->points }} {{ __('poin maks.') }}</span>
                                </div>
                                <h3 class="font-display text-charcoal text-xl font-black">{{ $mission->title }}</h3>
                                @include('user.edutourism.games.' . str_replace('_', '-', $mission->type), [
                                    'mission' => $mission,
                                ])
                            </div>
                        </template>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <x-map-style-modal />

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    {{-- Harus dimuat sebelum skrip di bawah, yang memanggil initMapStyleSwitcher() saat peta dibuat. --}}
    @include('components.map-style-script')
    <script>
        function missionRunner(missions, completedIds) {
            return {
                missions,
                open: false,
                stage: 'intro',
                index: 0,
                submitting: false,

                init() {
                    const firstIncomplete = this.missions.findIndex(m => !completedIds.includes(m.id));
                    this.index = firstIncomplete === -1 ? 0 : firstIncomplete;
                },
                openRunner() {
                    this.open = true;
                },
                onMissionComplete(detail) {
                    if (this.submitting) return;
                    this.submitting = true;

                    fetch(`/edutourism/mission/${detail.id}/complete`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                earned: detail.earned
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success) throw new Error(data.message || 'failed');

                            if (detail.earned > 0) {
                                const badge = document.createElement('div');
                                badge.className =
                                    'quiz-score-badge fixed left-1/2 top-1/3 z-[70] text-3xl font-black text-green-600';
                                badge.textContent = `+${detail.earned}`;
                                document.body.appendChild(badge);
                                setTimeout(() => badge.remove(), 1500);
                            }

                            if (data.is_last_mission) {
                                setTimeout(() => window.location.reload(), 1200);
                            } else {
                                setTimeout(() => {
                                    this.index++;
                                    this.submitting = false;
                                }, 800);
                            }
                        })
                        .catch(() => {
                            this.submitting = false;
                            Swal.fire({
                                title: "{{ __('Oops!') }}",
                                text: "{{ __('Gagal menyimpan progres misi.') }}",
                                icon: 'error',
                                confirmButtonColor: '#1E5128'
                            });
                        });
                },
            };
        }

        (function() {
            let mapInstance = null;
            let watchId = null;
            let gpsTimer = null;

            function clearGpsTimer() {
                if (gpsTimer !== null) {
                    clearTimeout(gpsTimer);
                    gpsTimer = null;
                }
            }

            const hasCurrentPoint = @json((bool) $activeSession->currentPoint);
            // A locationable may have several map points (e.g. multiple entrances) —
            // arriving at any one of them completes the mission.
            const targetPoints = @json($targetPoints);

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
                        maxZoom: 22,
                        maxNativeZoom: 19
                    }).addTo(map);

                    // Pilihan standar/satelit dibagi dengan /explore lewat localStorage 'mapStyle',
                    // jadi tampilan peta tidak berubah-ubah saat berpindah halaman.
                    if (window.initMapStyleSwitcher) {
                        window.initMapStyleSwitcher(map);
                    }

                    targetPoints.forEach(function(point) {
                        L.marker([point.lat, point.lng], {
                            icon: window.gseMapPin('check')
                        }).addTo(map);
                    });

                    let userMarker = null;
                    let manualMode = false;
                    // Diset saat wisatawan benar-benar menaruh posisi sendiri. Setelah itu GPS
                    // tidak boleh menimpanya: kalau GPS pulih sebentar lalu buruk lagi, posisi
                    // akan lompat-lompat dan tombol yang sudah terbuka terkunci kembali.
                    let manualPlaced = false;

                    // Titik dianggap tercapai di radius ini (meter).
                    const ARRIVE_RADIUS_M = 5;
                    // Fix GPS dengan akurasi lebih buruk dari ini tidak dipakai untuk membuka
                    // titik, karena ±100m akan meng-unlock apa pun. Ponsel di ruang terbuka biasanya
                    // ±5-15m; di antara tembok/bambu bisa jauh lebih buruk.
                    const MAX_ACCURACY_M = 50;
                    // Belum ada satu pun fix layak dalam rentang ini -> tawarkan mode manual.
                    const GPS_GIVE_UP_MS = 20000;

                    // Satu tempat untuk seluruh tampilan kartu status, supaya tidak ada kombinasi
                    // setengah jadi (mis. ikon hijau tapi teks amber). Kelas ditulis utuh sebagai
                    // literal agar tetap terpindai Tailwind.
                    const GPS_STATES = {
                        searching: {
                            box: 'border-blue-100 bg-blue-50',
                            icon: 'bg-blue-100 text-blue-600',
                            title: 'text-blue-900',
                            hint: 'text-blue-700',
                        },
                        arrived: {
                            box: 'border-primary/30 bg-primary/10',
                            icon: 'bg-primary text-white',
                            title: 'text-primary',
                            hint: 'text-primary',
                        },
                        warning: {
                            box: 'border-amber-200 bg-amber-50',
                            icon: 'bg-amber-100 text-amber-700',
                            title: 'text-amber-900',
                            hint: 'text-amber-700',
                        },
                    };
                    const GPS_STATE_CLASSES = Object.values(GPS_STATES)
                        .flatMap(s => Object.values(s))
                        .flatMap(v => v.split(' '));

                    let gpsState = null;

                    function setGpsState(state) {
                        if (state === gpsState) return;

                        const nodes = {
                            box: document.getElementById('gps-status'),
                            icon: document.getElementById('gps-status-icon'),
                            title: document.getElementById('distance-info'),
                            hint: document.getElementById('gps-hint'),
                        };
                        const style = GPS_STATES[state];
                        if (!style || !nodes.box) return;

                        for (const key in nodes) {
                            if (!nodes[key]) continue;
                            nodes[key].classList.remove(...GPS_STATE_CLASSES);
                            nodes[key].classList.add(...style[key].split(' '));
                        }

                        // Getar sekali saat titik terbuka: wisatawan sedang berjalan dan sering
                        // tidak menatap layar, jadi warna dan teks saja tidak cukup memberi tahu.
                        if (state === 'arrived' && gpsState !== null && navigator.vibrate) {
                            navigator.vibrate(50);
                        }

                        gpsState = state;
                    }

                    // Fallback: tap-di-peta hanya hidup kalau GPS benar-benar tidak bisa dipakai
                    // (ditolak, tidak didukung, atau tak dapat fix). Selama GPS jalan, posisi
                    // hanya boleh datang dari watchPosition.
                    function enableManualMode(reason) {
                        clearGpsTimer();
                        if (manualMode) return;
                        manualMode = true;

                        map.on('click', function(e) {
                            manualPlaced = true;
                            updateUserPosition(e.latlng.lat, e.latlng.lng);
                        });

                        const hint = document.getElementById('gps-hint');
                        if (hint) {
                            hint.textContent = @js(__('GPS tidak terdeteksi. Ketuk peta untuk menandai posisi Anda'));
                        }

                        const infoText = document.getElementById('distance-info');
                        if (infoText && !userMarker) {
                            infoText.textContent = @js(__('Mode manual'));
                        }

                        setGpsState('warning');

                        console.warn('Edutourism: manual position mode aktif:', reason);
                    }

                    // accuracy = radius error GPS dalam meter (0 untuk posisi yang ditaruh manual).
                    function updateUserPosition(lat, lng, accuracy = 0) {
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

                        if (targetPoints.length > 0) {
                            // Arriving at any one of the target's points completes the mission.
                            const dist = Math.min(...targetPoints.map(function(point) {
                                return calculateDistance(lat, lng, point.lat, point.lng);
                            }));
                            const infoText = document.getElementById('distance-info');
                            const arriveBtn = document.getElementById('btn-arrive');

                            if (infoText && arriveBtn) {
                                const detailBtn = document.getElementById('btn-detail-object');
                                const scanBtn = document.getElementById('btn-scan-qr');

                                // Radius 5m lebih ketat daripada akurasi GPS ponsel, jadi jarak
                                // dinilai pada tepi terdekat lingkaran error, bukan titik tengahnya.
                                // Tanpa ini wisatawan bisa berdiri persis di objek dan tetap terkunci.
                                const usableFix = accuracy <= MAX_ACCURACY_M;
                                const nearestDist = Math.max(0, dist - accuracy);
                                // Akurasi ikut ditampilkan: radius 5m bergantung penuh padanya,
                                // jadi tanpa angka ini "kenapa belum terbuka" mustahil dijawab
                                // di lapangan, baik oleh wisatawan maupun saat uji coba.
                                const acc = accuracy > 0 ? ` · ±${Math.round(accuracy)}m` : '';

                                const hint = document.getElementById('gps-hint');

                                if (usableFix && nearestDist < ARRIVE_RADIUS_M) {
                                    infoText.textContent =
                                        `{{ __('Lokasi Ditemukan!') }} (${dist}m${acc})`;
                                    // Di mode manual, instruksi "ketuk peta" sudah basi begitu
                                    // posisi ditandai. Tetap dibedakan dari GPS asli supaya tidak
                                    // terbaca seolah kedatangannya terverifikasi satelit.
                                    if (hint) {
                                        hint.textContent = manualMode ?
                                            @js(__('Posisi ditandai manual')) :
                                            @js(__('Anda sudah sampai di titik ini'));
                                    }
                                    arriveBtn.disabled = false;
                                    arriveBtn.textContent = @js(__('Jawab Pertanyaan & Lanjut'));
                                    setGpsState('arrived');

                                    if (detailBtn) detailBtn.classList.remove('hidden');
                                    if (scanBtn) scanBtn.classList.remove('hidden');
                                } else {
                                    infoText.textContent = usableFix ?
                                        `{{ __('Jarak') }}: ${dist} {{ __('meter') }}${acc}` :
                                        `${@js(__('Sinyal GPS lemah'))} (±${Math.round(accuracy)}m)`;
                                    if (hint) {
                                        hint.textContent = manualMode ?
                                            @js(__('GPS tidak terdeteksi. Ketuk peta untuk menandai posisi Anda')) :
                                            (usableFix ?
                                                @js(__('Arahkan ke lokasi untuk membuka kuis')) :
                                                @js(__('Menunggu sinyal yang lebih akurat')));
                                    }
                                    arriveBtn.disabled = true;
                                    arriveBtn.textContent = "{{ __('Mendekati Lokasi...') }}";
                                    setGpsState(usableFix && !manualMode ? 'searching' : 'warning');

                                    if (detailBtn) detailBtn.classList.add('hidden');
                                    if (scanBtn) scanBtn.classList.add('hidden');
                                }
                            }
                        }
                    }

                    if (targetPoints.length > 0) {
                        if (!navigator.geolocation) {
                            enableManualMode('geolocation tidak didukung browser');
                        } else {
                            // GPS bisa "berhasil" tapi hanya mengirim fix ±200m selamanya
                            // (dalam ruangan, di antara tembok). Itu bukan error, jadi
                            // watchPosition tidak akan pernah memanggil handler error,
                            // timer ini yang menawarkan jalan keluar.
                            gpsTimer = setTimeout(
                                () => enableManualMode('tidak ada fix GPS yang cukup akurat'),
                                GPS_GIVE_UP_MS);

                            watchId = navigator.geolocation.watchPosition(pos => {
                                if (pos.coords.accuracy <= MAX_ACCURACY_M) {
                                    clearGpsTimer();
                                }
                                if (manualPlaced) {
                                    return;
                                }
                                updateUserPosition(pos.coords.latitude, pos.coords.longitude,
                                    pos.coords.accuracy);
                            }, err => {
                                // Sengaja TANPA opsi `timeout`: pada watchPosition, timeout
                                // berlaku per-pembaruan, jadi jeda fix biasa saat jalan kaki
                                // (layar meredup, lewat bawah pohon) akan memicu TIMEOUT dan
                                // membuka mode klik padahal GPS sehat. Kasus "lama tak dapat
                                // fix" sudah ditangani gpsTimer di atas. Satu mekanisme saja.
                                if (err.code === err.TIMEOUT) {
                                    return;
                                }
                                enableManualMode(err.message || err.code);
                            }, {
                                enableHighAccuracy: true
                            });
                        }
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

                    window.triggerArrive = function(pointId) {
                        console.log("triggerArrive called for point ID:", pointId);
                        const btnArrive = document.getElementById('btn-arrive');
                        if (btnArrive) {
                            btnArrive.disabled = true;
                            btnArrive.textContent = "{{ __('Memuat...') }}";
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
                                if (data.success && data.has_missions) {
                                    window.dispatchEvent(new CustomEvent('open-mission-runner'));
                                    document.getElementById('btn-arrive').disabled = false;
                                    document.getElementById('btn-arrive').textContent =
                                        @js(__('Mulai Misi'));
                                } else if (data.success && data.session_status === 'completed') {
                                    window.location.reload();
                                } else {
                                    Swal.fire({
                                        title: "{{ __('Info') }}",
                                        text: "{{ __('Rute berlanjut...') }}",
                                        icon: 'info',
                                        confirmButtonColor: '#1E5128',
                                        confirmButtonText: "{{ __('Lanjut') }}"
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }
                            })
                            .catch(err => {
                                console.error("Error occurred in triggerArrive:", err);
                                document.getElementById('btn-arrive').disabled = false;
                                document.getElementById('btn-arrive').textContent =
                                    @js(__('Lanjut'));
                                Swal.fire({
                                    title: "{{ __('Oops!') }}",
                                    text: "{{ __('Gagal memuat titik.') }}",
                                    icon: 'error',
                                    confirmButtonColor: '#1E5128'
                                });
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
                clearGpsTimer();
                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }
                if (mapInstance) {
                    mapInstance.remove();
                    mapInstance = null;
                }
                delete window.triggerArrive;
                delete window.stopRoute;
                document.removeEventListener('livewire:navigating', cleanup);
            });
        })();
    </script>
@endsection
