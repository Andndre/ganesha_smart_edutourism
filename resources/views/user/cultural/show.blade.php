@extends('layouts.app')
@section('title', $object->name . ' - Penglipuran')
@section('header_title', 'Storytelling')

@section('content')
    <article class="bg-surface pb-10">
        <!-- Hero Image Area -->
        <div class="w-full h-[40dvh] bg-gray-200 relative overflow-hidden">
            @if($object->historical_images && count($object->historical_images) > 0)
                <img src="{{ asset('storage/' . $object->historical_images[0]) }}" alt="{{ $object->name }}" class="w-full h-full object-cover">
            @else
                <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                    <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
            <div class="absolute inset-0 bg-linear-to-t from-charcoal/90 via-charcoal/20 to-transparent z-10"></div>

            <div class="absolute bottom-12 left-6 right-6 text-white z-20">
                <h1 class="text-3xl font-bold font-playfair mb-2 leading-tight">{{ $object->name }}</h1>
                <p class="text-sm text-gray-200 font-medium tracking-wide">Jantung Spiritual Desa Penglipuran</p>
            </div>
        </div>

        <!-- Audio Player -->
        @if($object->audio_narration_path)
            <div class="px-6 -mt-6 relative z-10 mb-8" 
                 x-data="{
                     playing: false,
                     audio: null,
                     currentTime: 0,
                     duration: 0,
                     formatTime(secs) {
                         if (isNaN(secs)) return '0:00';
                         const m = Math.floor(secs / 60);
                         const s = Math.floor(secs % 60);
                         return m + ':' + (s < 10 ? '0' : '') + s;
                     },
                     togglePlay() {
                         if (!this.audio) {
                             this.audio = this.$refs.audioEl;
                         }
                         if (this.playing) {
                             this.audio.pause();
                         } else {
                             this.audio.play();
                         }
                         this.playing = !this.playing;
                     },
                     init() {
                         this.$nextTick(() => {
                             const el = this.$refs.audioEl;
                             el.addEventListener('timeupdate', () => {
                                 this.currentTime = el.currentTime;
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
                <audio x-ref="audioEl" src="{{ asset('storage/' . $object->audio_narration_path) }}" preload="metadata"></audio>

                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center gap-4">
                    <button @click="togglePlay()"
                        class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center shrink-0 active:scale-95 transition-all shadow-[0_4px_10px_rgba(30,81,40,0.3)]">
                        <!-- Play Icon (Centered Play Triangle) -->
                        <svg x-show="!playing" class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <!-- Pause Icon -->
                        <svg x-show="playing" class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" fill="currentColor"/>
                        </svg>
                    </button>
                    <div class="flex-1">
                        <div class="text-sm font-bold text-charcoal" x-text="playing ? 'Memutar Kisah Sejarah...' : 'Dengarkan Kisah Ini'">Dengarkan Kisah Ini</div>
                        <!-- Playback Seekbar & Timers -->
                        <div class="flex items-center gap-3 mt-1.5">
                            <span class="text-[10px] font-bold text-gray-500 min-w-[28px] tabular-nums" x-text="formatTime(currentTime)">0:00</span>
                            <input type="range" 
                                   min="0" 
                                   :max="duration || 100" 
                                   :value="currentTime"
                                   @input="if (audio) { audio.currentTime = $el.value; currentTime = $el.value; }"
                                   class="flex-1 h-1 rounded-full appearance-none bg-gray-100 cursor-pointer accent-primary outline-hidden"
                                   :style="'background: linear-gradient(to right, #1E5128 0%, #1E5128 ' + (duration ? (currentTime / duration * 100) : 0) + '%, #f3f4f6 ' + (duration ? (currentTime / duration * 100) : 0) + '%, #f3f4f6 100%);'">
                            <span class="text-[10px] font-bold text-gray-500 min-w-[28px] text-right tabular-nums" x-text="duration ? formatTime(duration) : '0:00'">0:00</span>
                        </div>
                    </div>
                    <!-- Animated Waveform -->
                    <div class="flex items-end gap-1 h-5 select-none pointer-events-none">
                        <div class="w-1 bg-primary/70 rounded-full transition-all duration-300"
                             :style="playing ? 'animation: audio-bounce 0.8s ease-in-out infinite alternate 0.1s; height: 16px;' : 'height: 8px;'"></div>
                        <div class="w-1 bg-primary rounded-full transition-all duration-300"
                             :style="playing ? 'animation: audio-bounce 0.6s ease-in-out infinite alternate 0.3s; height: 24px;' : 'height: 12px;'"></div>
                        <div class="w-1 bg-primary/80 rounded-full transition-all duration-300"
                             :style="playing ? 'animation: audio-bounce 0.7s ease-in-out infinite alternate 0.2s; height: 20px;' : 'height: 10px;'"></div>
                        <div class="w-1 bg-primary/50 rounded-full transition-all duration-300"
                             :style="playing ? 'animation: audio-bounce 0.5s ease-in-out infinite alternate 0.4s; height: 12px;' : 'height: 6px;'"></div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Article Content -->
        <div
            class="px-6 prose prose-p:text-gray-600 prose-p:leading-relaxed prose-h2:font-playfair prose-h2:text-charcoal prose-h2:text-2xl prose-h2:font-bold prose-h2:mt-8 prose-h2:mb-4 max-w-none">

            <p class="text-lg font-medium text-charcoal leading-relaxed mb-6">
                {{ $object->description }}
            </p>

            @foreach($object->stories as $story)
                <h2>{{ $story->title }}</h2>
                <div class="text-gray-600 leading-relaxed text-sm">
                    {!! nl2br(e($story->content)) !!}
                </div>
            @endforeach
        </div>

        <!-- AR Button -->
        @if($object->ar_marker_id || $object->model_3d_path)
            <div class="px-6 mt-10">
                <a href="{{ route('ar-scan') }}"
                    class="w-full flex items-center justify-center gap-2 bg-primary text-white py-4 rounded-xl font-bold active:scale-[0.98] transition-all shadow-[0_4px_14px_rgba(30,81,40,0.3)]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 011.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Jelajahi dalam Mode AR
                </a>
            </div>
        @endif

    </article>
@endsection

@push('styles')
    <style>
        @keyframes audio-bounce {
            0% { transform: scaleY(0.4); }
            100% { transform: scaleY(1.2); }
        }
    </style>
@endpush
