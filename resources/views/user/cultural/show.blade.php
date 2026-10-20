@extends('layouts.app')
@section('title', $object['name'] . ' - Penglipuran')
@section('header_title', $object['name'])

@section('content')
    <article
        class="bg-surface {{ !empty($object['ar_marker_id']) || !empty($object['model_3d_path']) ? 'pb-32' : 'pb-10' }}">
        <!-- Hero Image Area / Carousel -->
        <div class="bg-linear-to-br from-charcoal via-charcoal to-primary/50 md:max-h-100 relative h-[40dvh] w-full overflow-hidden"
            x-data="{
                currentIndex: 0,
                loaded: false,
                touchStartX: 0,
                images: {{ json_encode(array_map(fn($img) => asset('storage/' . $img), $object['historical_images'] ?? [])) }},
                next() {
                    this.currentIndex = (this.currentIndex + 1) % this.images.length;
                },
                prev() {
                    this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
                },
                touchStart(e) {
                    if (e.changedTouches && e.changedTouches[0]) {
                        this.touchStartX = e.changedTouches[0].clientX;
                    }
                },
                touchEnd(e) {
                    if (!e.changedTouches || !e.changedTouches[0]) return;
                    const diff = this.touchStartX - e.changedTouches[0].clientX;
                    if (Math.abs(diff) > 40) {
                        if (diff > 0) this.next();
                        else this.prev();
                    }
                }
            }" @touchstart.passive="touchStart($event)" @touchend.passive="touchEnd($event)">

            @if (!empty($object['historical_images']) && count($object['historical_images']) > 0)
                <!-- Slides -->
                <template x-for="(img, index) in images" :key="index">
                    <div x-show="currentIndex === index" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        class="absolute inset-0 h-full w-full">
                        {{-- Blurred backdrop desktop only --}}
                        <img :src="img" alt="" aria-hidden="true"
                            class="pointer-events-none absolute inset-0 hidden h-full w-full scale-110 object-cover opacity-40 blur-2xl md:block">
                        {{-- Main image --}}
                        <img :src="img" :loading="index === 0 ? 'eager' : 'lazy'" x-on:load="loaded = true"
                            x-on:error="loaded = true" alt="{{ $object['name'] }}"
                            class="relative z-10 mx-auto h-full w-full object-cover md:object-contain">
                    </div>
                </template>

                <!-- Navigation Controls Pill (Mobile & Desktop quick nav) -->
                <template x-if="images.length > 1">
                    <div class="pointer-events-none absolute inset-x-0 top-3 z-30">
                        <div class="mx-auto flex max-w-3xl justify-end px-6">
                            <div
                                class="pointer-events-auto flex items-center gap-1 rounded-full bg-black/60 px-2 py-1 text-xs font-semibold text-white shadow-md backdrop-blur-sm">
                                <button type="button" @click="prev()" aria-label="Previous"
                                    class="flex h-6 w-6 items-center justify-center rounded-full transition-colors hover:bg-white/20 active:scale-90">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <span class="px-1 text-[11px] tabular-nums"
                                    x-text="(currentIndex + 1) + ' / ' + images.length"></span>
                                <button type="button" @click="next()" aria-label="Next"
                                    class="flex h-6 w-6 items-center justify-center rounded-full transition-colors hover:bg-white/20 active:scale-90">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Desktop Side Chevrons (Only on md+ screen, avoiding mobile text overlap) -->
                <template x-if="images.length > 1">
                    <div class="pointer-events-none absolute inset-x-0 top-1/2 z-30 hidden -translate-y-1/2 md:block">
                        <div class="mx-auto flex max-w-4xl items-center justify-between px-4">
                            <button type="button" @click="prev()" aria-label="Previous"
                                class="tap-target backdrop-blur-xs pointer-events-auto rounded-full bg-black/40 text-white shadow-sm transition-all hover:bg-black/60 active:scale-90">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button type="button" @click="next()" aria-label="Next"
                                class="tap-target backdrop-blur-xs pointer-events-auto rounded-full bg-black/40 text-white shadow-sm transition-all hover:bg-black/60 active:scale-90">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            @else
                <div class="absolute inset-0 flex items-center justify-center text-white/40">
                    <svg class="h-16 w-16 md:h-24 md:w-24" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </div>
            @endif
            <div x-show="!loaded && images.length" class="absolute inset-0 animate-pulse bg-gray-300"></div>

            <div class="bg-linear-to-t from-charcoal/90 via-charcoal/40 absolute inset-0 z-10 to-transparent"></div>

            <div
                class="{{ !empty($object['audio_narration_path']) ? 'bottom-16 md:bottom-18' : 'bottom-6 md:bottom-8' }} pointer-events-none absolute inset-x-0 z-20 text-white">
                <div class="mx-auto max-w-3xl px-6">
                    @if (!empty($object['parent']))
                        <a href="{{ route('cultural-object', ['slug' => $object['parent']['slug']]) }}"
                            class="pointer-events-auto mb-2.5 inline-flex max-w-full items-center gap-1.5 rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white shadow-xs backdrop-blur-md transition-all hover:bg-white/30 active:scale-95">
                            <svg class="h-3.5 w-3.5 shrink-0 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                            </svg>
                            <span class="truncate">{{ __('Bagian dari :name', ['name' => $object['parent']['name']]) }}</span>
                            <svg class="h-3 w-3 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                    <h1 class="font-playfair mb-2 text-3xl font-bold leading-tight md:text-4xl">{{ $object['name'] }}</h1>
                    @if (!empty($object['short_description']))
                        <p class="text-sm font-medium tracking-wide text-gray-200 md:text-base">
                            {{ $object['short_description'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Audio Player -->
        @if (!empty($object['audio_narration_path']))
            <div class="relative z-30 mx-auto -mt-6 mb-8 max-w-3xl px-6" x-data="{
                playing: false,
                dragging: false,
                currentTime: 0,
                duration: 0,
                formatTime(secs) {
                    if (isNaN(secs)) return '0:00';
                    const m = Math.floor(secs / 60);
                    const s = Math.floor(secs % 60);
                    return m + ':' + (s < 10 ? '0' : '') + s;
                },
                togglePlay() {
                    const audio = this.$refs.audioEl;
                    if (this.playing) {
                        audio.pause();
                    } else {
                        audio.play();
                    }
                    this.playing = !this.playing;
                },
                init() {
                    this.$nextTick(() => {
                        const el = this.$refs.audioEl;
                        el.addEventListener('timeupdate', () => {
                            if (!this.dragging) {
                                this.currentTime = el.currentTime;
                            }
                        });
                        el.addEventListener('loadedmetadata', () => {
                            this.duration = el.duration;
                        });
                        el.addEventListener('ended', () => {
                            this.playing = false;
                            this.currentTime = 0;
                        });
                        if (el.duration) {
                            this.duration = el.duration;
                        }
                    });
                }
            }">
                <!-- Hidden Audio Element -->
                <audio x-ref="audioEl" src="{{ route('audio.stream', $object['audio_narration_path']) }}"
                    preload="auto"></audio>

                <div class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <button @click="togglePlay()"
                        class="bg-primary flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-white shadow-[0_4px_10px_rgba(30,81,40,0.3)] transition-all active:scale-95">
                        <!-- Play Icon (Centered Play Triangle) -->
                        <svg x-show="!playing" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                        <!-- Pause Icon -->
                        <svg x-show="playing" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24"
                            style="display: none;">
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" fill="currentColor" />
                        </svg>
                    </button>
                    <div class="flex-1">
                        <div
                            class="mb-0.5 inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-500">
                            {{ app()->getLocale() === 'id' ? __('Audio Bahasa Indonesia') : __('Audio Bahasa Inggris') }}
                        </div>
                        <div class="text-charcoal text-sm font-bold"
                            x-text="playing ? '{{ __('Memutar Kisah Sejarah...') }}' : '{{ __('Dengarkan Kisah Ini') }}'">
                            {{ __('Dengarkan Kisah Ini') }}</div>
                        <!-- Playback Seekbar & Timers -->
                        <div class="mt-1.5 flex items-center gap-3">
                            <span class="min-w-7 text-[11px] font-bold tabular-nums text-gray-500"
                                x-text="formatTime(currentTime)">0:00</span>
                            <input type="range" min="0" :max="duration || 100" x-model.number="currentTime"
                                @mousedown="dragging = true" @touchstart="dragging = true"
                                @change="if ($refs.audioEl && duration > 0) { try { $refs.audioEl.currentTime = currentTime; } catch(e) {} } else { currentTime = 0; }; dragging = false;"
                                class="audio-seek outline-hidden flex-1 cursor-pointer"
                                :style="'--seek-progress: ' + (currentTime / (duration || 100) * 100) + '%'">
                            <span class="min-w-7 text-right text-[11px] font-bold tabular-nums text-gray-500"
                                x-text="duration ? formatTime(duration) : '0:00'">0:00</span>
                        </div>
                    </div>
                    <!-- Animated Waveform -->
                    <div class="pointer-events-none flex h-5 select-none items-end gap-1">
                        <div class="bg-primary/70 w-1 rounded-full transition-all duration-300"
                            :style="playing ?
                                'animation: audio-bounce 0.8s ease-in-out infinite alternate 0.1s; height: 16px;' :
                                'height: 8px;'">
                        </div>
                        <div class="bg-primary w-1 rounded-full transition-all duration-300"
                            :style="playing ?
                                'animation: audio-bounce 0.6s ease-in-out infinite alternate 0.3s; height: 24px;' :
                                'height: 12px;'">
                        </div>
                        <div class="bg-primary/80 w-1 rounded-full transition-all duration-300"
                            :style="playing ?
                                'animation: audio-bounce 0.7s ease-in-out infinite alternate 0.2s; height: 20px;' :
                                'height: 10px;'">
                        </div>
                        <div class="bg-primary/50 w-1 rounded-full transition-all duration-300"
                            :style="playing ?
                                'animation: audio-bounce 0.5s ease-in-out infinite alternate 0.4s; height: 12px;' :
                                'height: 6px;'">
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Article Content -->
        <div
            class="prose prose-p:text-gray-600 prose-p:leading-relaxed prose-h2:font-playfair prose-h2:text-charcoal prose-h2:text-2xl prose-h2:font-bold prose-h2:mt-8 prose-h2:mb-4 {{ empty($object['audio_narration_path']) ? 'pt-8' : '' }} mx-auto max-w-3xl px-6">

            @if (!empty($object['description']))
                {!! $object['description'] !!}
            @endif
        </div>

        @if (!empty($object['children']))
            <section class="mx-auto max-w-3xl px-6 pb-6 pt-2">
                <h2 class="font-playfair text-charcoal mb-3 flex items-baseline gap-2 text-xl font-bold">
                    {{ __('Bagian dari :name', ['name' => $object['name']]) }}
                    <span class="text-sm font-semibold text-gray-500">{{ count($object['children']) }}</span>
                </h2>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                    @foreach ($object['children'] as $child)
                        <a href="{{ route('cultural-object', ['slug' => $child['slug']]) }}"
                            class="group block overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100 transition-all hover:-translate-y-1 hover:shadow-md active:scale-[0.98]">
                            <div class="aspect-4/3 relative overflow-hidden bg-gray-200">
                                @if (!empty($child['historical_images']))
                                    <img src="{{ asset('storage/' . $child['historical_images'][0]) }}"
                                        alt="{{ $child['name'] }}" loading="lazy"
                                        class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                @if (!empty($child['ar_marker_id']) || !empty($child['model_3d_path']))
                                    <span
                                        class="text-primary absolute right-2 top-2 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-semibold shadow-sm backdrop-blur-sm">{{ __('AR') }}</span>
                                @endif
                            </div>
                            <div class="p-3">
                                <h3 class="font-playfair text-charcoal line-clamp-2 text-sm font-bold leading-tight">
                                    {{ $child['name'] }}</h3>
                                @if (!empty($child['short_description']))
                                    <p class="mt-1 line-clamp-2 text-xs text-gray-600">{{ $child['short_description'] }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @auth
            @if ($canRate)
                <div id="cultural-object-rating-form" class="mx-auto max-w-3xl px-6 pb-6">
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h3 class="font-playfair text-charcoal mb-3 text-lg font-bold">{{ __('Beri Rating Objek Ini') }}</h3>
                        @if (session('status'))
                            <p class="mb-3 text-sm text-green-700">{{ session('status') }}</p>
                        @endif
                        @error('rating')
                            <p class="mb-3 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('comment')
                            <p class="mb-3 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <form method="POST" action="{{ route('cultural-object.rating.store', $object['slug']) }}"
                            x-data="{ rating: {{ $existingRating->rating ?? 0 }} }">
                            @csrf
                            <div class="mb-3 flex gap-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <button type="button" @click="rating = {{ $i }}"
                                        aria-label="{{ __('Beri :n bintang', ['n' => $i]) }}"
                                        class="flex h-11 w-11 items-center justify-center text-2xl"
                                        :class="rating >= {{ $i }} ? 'text-[#D4AF37]' : 'text-gray-300'">★</button>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" x-model="rating">
                            <textarea name="comment" rows="2" maxlength="1000"
                                class="w-full rounded-xl border border-gray-200 p-3 text-sm" placeholder="{{ __('Komentar (opsional)') }}">{{ $existingRating->comment ?? '' }}</textarea>
                            <button type="submit" class="bg-primary mt-3 w-full rounded-xl py-3 font-bold text-white">
                                {{ $existingRating ? __('Perbarui Rating') : __('Kirim Rating') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endauth

        <!-- AR Button -->
        @if (!empty($object['ar_marker_id']) || !empty($object['model_3d_path']))
            <div
                class="bottom-(--route-banner-h,0px) fixed inset-x-0 z-30 border-t border-gray-100 bg-white p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
                <a href="{{ route('ar-scan', empty($object['ar_marker_id']) ? [] : ['marker' => $object['ar_marker_id']]) }}"
                    class="bg-primary flex w-full items-center justify-center gap-2 rounded-xl py-4 font-bold text-white shadow-[0_4px_14px_rgba(30,81,40,0.3)] transition-all active:scale-[0.98]">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 011.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ __('Jelajahi dalam Mode AR') }}
                </a>
            </div>
        @endif

    </article>
@endsection

@push('styles')
    <style>
        /* ponytail: seekbar 44px touch target — thumb digeser satu jempol, track tetap tipis */
        .audio-seek {
            -webkit-appearance: none;
            appearance: none;
            height: 44px;
            background: transparent;
        }

        .audio-seek::-webkit-slider-runnable-track {
            height: 4px;
            border-radius: 9999px;
            background: linear-gradient(to right, #1E5128 var(--seek-progress, 0%), #f3f4f6 var(--seek-progress, 0%));
        }

        .audio-seek::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            height: 18px;
            width: 18px;
            margin-top: -7px;
            border: 0;
            border-radius: 9999px;
            background: #1E5128;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .audio-seek::-moz-range-track {
            height: 4px;
            border-radius: 9999px;
            background: linear-gradient(to right, #1E5128 var(--seek-progress, 0%), #f3f4f6 var(--seek-progress, 0%));
        }

        .audio-seek::-moz-range-thumb {
            height: 18px;
            width: 18px;
            border: 0;
            border-radius: 9999px;
            background: #1E5128;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        @keyframes audio-bounce {
            0% {
                transform: scaleY(0.4);
            }

            100% {
                transform: scaleY(1.2);
            }
        }

        /* Custom typography styling for rich text stories and main body */
        .story-content-prose ul,
        .prose ul {
            list-style-type: disc !important;
            padding-left: 1.5rem !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0.5rem !important;
        }

        .story-content-prose ol,
        .prose ol {
            list-style-type: decimal !important;
            padding-left: 1.5rem !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0.5rem !important;
        }

        .story-content-prose li,
        .prose li {
            margin-bottom: 0.35rem !important;
            line-height: 1.6 !important;
        }

        .story-content-prose p,
        .prose p {
            margin-bottom: 0.75rem !important;
            line-height: 1.6 !important;
        }

        .story-content-prose p:last-child {
            margin-bottom: 0 !important;
        }

        .story-content-prose img,
        .prose img {
            max-width: 100% !important;
            height: auto !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            margin-top: 0.75rem !important;
            margin-bottom: 0.75rem !important;
            display: block !important;
        }
    </style>
@endpush
