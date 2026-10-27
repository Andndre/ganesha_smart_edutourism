<div id="{{ $playerId }}" class="mt-1 hidden">
    <div class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5">
        <button type="button" onclick="window.toggleMiniAudio(this)"
            class="mini-audio-btn shrink-0 rounded-full p-0.5 text-primary transition-colors hover:bg-primary/10">
            <svg class="mini-audio-play h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z"/>
            </svg>
            <svg class="mini-audio-pause hidden h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
            </svg>
        </button>
        <span class="mini-audio-name min-w-0 flex-1 truncate text-[11px] text-gray-500" title=""></span>
        <audio class="mini-audio-el" preload="none"></audio>
    </div>
</div>

@once
    @push('scripts')
        <script>
            if (!window.toggleMiniAudio) {
                window.toggleMiniAudio = function(btn) {
                    const row = btn.parentElement;
                    const audio = row.querySelector('.mini-audio-el');
                    if (!audio) return;
                    const playIcon = btn.querySelector('.mini-audio-play');
                    const pauseIcon = btn.querySelector('.mini-audio-pause');
                    document.querySelectorAll('.mini-audio-el').forEach(function(a) {
                        if (a === audio) return;
                        a.pause();
                        const b = a.parentElement?.querySelector('.mini-audio-btn');
                        if (b) {
                            b.querySelector('.mini-audio-play')?.classList.remove('hidden');
                            b.querySelector('.mini-audio-pause')?.classList.add('hidden');
                        }
                    });
                    if (audio.paused) {
                        audio.play();
                        playIcon?.classList.add('hidden');
                        pauseIcon?.classList.remove('hidden');
                        audio.onended = function() {
                            playIcon?.classList.remove('hidden');
                            pauseIcon?.classList.add('hidden');
                        };
                    } else {
                        audio.pause();
                        playIcon?.classList.remove('hidden');
                        pauseIcon?.classList.add('hidden');
                    }
                };
            }
            if (!window.setMiniAudio) {
                window.setMiniAudio = function(id, path) {
                    const el = document.getElementById(id);
                    if (!el || !path) return;
                    const name = path.split('/').pop();
                    el.querySelector('.mini-audio-name').textContent = name;
                    el.querySelector('.mini-audio-name').title = name;
                    el.querySelector('.mini-audio-el').src = '/audio-stream/' + path;
                    el.querySelector('.mini-audio-play')?.classList.remove('hidden');
                    el.querySelector('.mini-audio-pause')?.classList.add('hidden');
                    el.classList.remove('hidden');
                };
            }
            if (!window.resetMiniAudio) {
                window.resetMiniAudio = function(id) {
                    const el = document.getElementById(id);
                    if (!el) return;
                    const audio = el.querySelector('.mini-audio-el');
                    if (audio) {
                        audio.pause();
                        audio.src = '';
                    }
                    el.querySelector('.mini-audio-play')?.classList.remove('hidden');
                    el.querySelector('.mini-audio-pause')?.classList.add('hidden');
                    el.classList.add('hidden');
                };
            }
            if (!window.previewLocalAudio) {
                window.previewLocalAudio = function(id, input) {
                    const el = document.getElementById(id);
                    if (!el) return;
                    const file = input.files && input.files[0];
                    if (!file) return;
                    const audio = el.querySelector('.mini-audio-el');
                    if (audio) {
                        audio.pause();
                        audio.src = URL.createObjectURL(file);
                    }
                    const name = file.name;
                    el.querySelector('.mini-audio-name').textContent = name + ' (baru)';
                    el.querySelector('.mini-audio-name').title = name;
                    el.querySelector('.mini-audio-play')?.classList.remove('hidden');
                    el.querySelector('.mini-audio-pause')?.classList.add('hidden');
                    el.classList.remove('hidden');
                };
            }
        </script>
    @endpush
@endonce
