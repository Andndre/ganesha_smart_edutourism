@extends('layouts.app')
@section('title', $object['name'] . ' - Penglipuran')
@section('header_title', 'Storytelling')

@section('content')
    <article
        class="bg-surface {{ !empty($object['ar_marker_id']) || !empty($object['model_3d_path']) ? (isset($activeEdutourismSession) && !Route::is('edutourism.active') ? 'pb-52' : 'pb-32') : 'pb-10' }}">
        <!-- Hero Image Area / Carousel -->
        <div class="relative h-[40dvh] w-full overflow-hidden bg-gray-200" x-data="{
            currentIndex: 0,
            images: {{ json_encode(array_map(fn($img) => asset('storage/' . $img), $object['historical_images'] ?? [])) }},
            next() {
                this.currentIndex = (this.currentIndex + 1) % this.images.length;
            },
            prev() {
                this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
            }
        }">

            @if (!empty($object['historical_images']) && count($object['historical_images']) > 0)
                <!-- Slides -->
                <template x-for="(img, index) in images" :key="index">
                    <div x-show="currentIndex === index" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        class="absolute inset-0 h-full w-full">
                        <img :src="img" alt="{{ $object['name'] }}" class="h-full w-full object-cover">
                    </div>
                </template>

                <!-- Navigation Chevrons -->
                <template x-if="images.length > 1">
                    <div>
                        <button @click="prev()"
                            class="absolute left-4 top-1/2 z-20 -translate-y-1/2 rounded-full bg-black/40 p-2 text-white transition-all hover:bg-black/60 active:scale-90">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button @click="next()"
                            class="absolute right-4 top-1/2 z-20 -translate-y-1/2 rounded-full bg-black/40 p-2 text-white transition-all hover:bg-black/60 active:scale-90">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </template>

                <!-- Dot Indicators -->
                <template x-if="images.length > 1">
                    <div class="absolute bottom-12 left-1/2 z-20 flex -translate-x-1/2 gap-1.5">
                        <template x-for="(img, index) in images" :key="index">
                            <div class="h-2 w-2 rounded-full transition-all duration-300"
                                :class="currentIndex === index ? 'bg-white w-4' : 'bg-white/50'"></div>
                        </template>
                    </div>
                </template>
            @else
                <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                    <svg class="h-16 w-16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </div>
            @endif

            <div class="bg-linear-to-t from-charcoal/90 via-charcoal/20 absolute inset-0 z-10 to-transparent"></div>

            <div class="bottom-18 pointer-events-none absolute left-6 right-6 z-20 text-white">
                <h1 class="font-playfair mb-2 text-3xl font-bold leading-tight">{{ $object['name'] }}</h1>
                @if (!empty($object['short_description']))
                    <p class="text-sm font-medium tracking-wide text-gray-200">{{ $object['short_description'] }}</p>
                @endif
            </div>
        </div>

        <!-- Audio Player -->
        @if (!empty($object['audio_narration_path']))
            <div class="relative z-30 -mt-6 mb-8 px-6" x-data="{
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
                        <div class="mb-0.5 inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-500">
                            {{ app()->getLocale() === 'id' ? __('Audio Bahasa Indonesia') : __('Audio Bahasa Inggris') }}
                        </div>
                        <div class="text-charcoal text-sm font-bold"
                            x-text="playing ? '{{ __('Memutar Kisah Sejarah...') }}' : '{{ __('Dengarkan Kisah Ini') }}'">{{ __('Dengarkan Kisah Ini') }}</div>
                        <!-- Playback Seekbar & Timers -->
                        <div class="mt-1.5 flex items-center gap-3">
                            <span class="min-w-7 text-[10px] font-bold tabular-nums text-gray-500"
                                x-text="formatTime(currentTime)">0:00</span>
                            <input type="range" min="0" :max="duration || 100" x-model.number="currentTime"
                                @mousedown="dragging = true" @touchstart="dragging = true"
                                @change="if ($refs.audioEl && duration > 0) { try { $refs.audioEl.currentTime = currentTime; } catch(e) {} } else { currentTime = 0; }; dragging = false;"
                                class="accent-primary outline-hidden h-1 flex-1 cursor-pointer appearance-none rounded-full bg-gray-100"
                                :style="'background: linear-gradient(to right, #1E5128 0%, #1E5128 ' + (currentTime / (
                                    duration || 100) * 100) + '%, #f3f4f6 ' + (currentTime / (duration || 100) * 100) +
                                '%, #f3f4f6 100%);'">
                            <span class="min-w-7 text-right text-[10px] font-bold tabular-nums text-gray-500"
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
            class="prose prose-p:text-gray-600 prose-p:leading-relaxed prose-h2:font-playfair prose-h2:text-charcoal prose-h2:text-2xl prose-h2:font-bold prose-h2:mt-8 prose-h2:mb-4 max-w-none px-6 {{ empty($object['audio_narration_path']) ? 'pt-8' : '' }}">

            @if (!empty($object['description']))
                {!! $object['description'] !!}
            @endif
        </div>


        <!-- AR Button -->
        @if (!empty($object['ar_marker_id']) || !empty($object['model_3d_path']))
            <div
                class="{{ isset($activeEdutourismSession) && !Route::is('edutourism.active') ? 'bottom-22 pb-4' : 'bottom-0 pb-[calc(1rem+env(safe-area-inset-bottom))]' }} fixed inset-x-0 z-30 border-t border-gray-100 bg-white p-4 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
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
