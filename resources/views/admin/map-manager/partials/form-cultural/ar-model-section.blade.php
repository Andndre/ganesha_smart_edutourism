<div>
    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Model 3D Aset AR</label>
    <span class="mb-2 block text-xs text-gray-500">Pilih model 3D yang sudah ada atau buat baru.</span>
    <select name="ar_model_id" id="ar_model_id_select" onchange="toggleModelSelect(this.value)"
        class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
        <option value="none">Tanpa Model 3D</option>
        @foreach ($models as $model)
            <option value="{{ $model->id }}"
                data-glb="{{ $model->model_3d_path ? asset('storage/' . $model->model_3d_path) : '' }}"
                data-usdz="{{ $model->model_3d_usdz_path ? asset('storage/' . $model->model_3d_usdz_path) : '' }}"
                data-audio="{{ $model->audio_narration_path ? route('audio.stream', ['path' => $model->audio_narration_path]) : '' }}"
                data-marker-id="{{ $model->ar_marker_id ?? '' }}">{{ $model->name }}</option>
        @endforeach
        <option value="new">+ Tambah Model 3D Baru...</option>
    </select>

    {{-- Badge marker ID untuk model existing --}}
    <div id="existing-marker-badge" class="mt-2 hidden">
        <span class="text-[10px] text-gray-500">ID Marker QR: </span>
        <span id="existing-marker-id-text"
            class="text-primary bg-primary/10 rounded-full px-2 py-0.5 font-mono text-[10px] font-bold"></span>
    </div>
</div>

<!-- Container for New Model Upload (hidden by default) -->
<div id="new-model-fields"
    class="hidden space-y-4 rounded-2xl border border-dashed border-gray-200 bg-gray-50/50 p-4">
    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Detail Model 3D Baru</h4>
    <div>
        <label class="mb-1 block text-xs font-semibold text-gray-700">Nama Model <span
                class="text-warning">*</span></label>
        <input type="text" name="new_model_name" placeholder="Contoh: Model Candi Bentar"
            class="focus:border-primary w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none">
    </div>
    <div>
        <label class="mb-1 block text-xs font-semibold text-gray-700">Deskripsi Model</label>
        <textarea name="new_model_description" rows="2" placeholder="Detail deskripsi model..."
            class="focus:border-primary w-full resize-none rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none"></textarea>
    </div>

    <div>
        <label class="mb-1 block text-xs font-semibold text-gray-700">ID Marker QR AR</label>
        <span class="mb-1.5 block text-[10px] text-gray-400">Opsional. Harus unik. Digunakan untuk integrasi
            Augmented Reality.</span>
        <input type="text" name="ar_marker_id" id="ar_marker_id" placeholder="Contoh: MARKER_PURA_01"
            class="focus:border-primary w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none"
            oninput="generateARMarker()">
        <input type="hidden" name="ar_marker_patt_content" id="ar_marker_patt_content">

        <div id="ar-download-container" class="mt-2" style="display: none;">
            <button type="button" onclick="downloadARMarker()"
                class="border-primary text-primary hover:bg-primary/5 flex w-full items-center justify-center gap-2 rounded-xl border-2 py-2 text-xs font-semibold transition-colors active:scale-95">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Unduh QR Marker AR (.png)
            </button>
            <span class="mt-1 block text-[10px] text-gray-400">Pola (.patt) akan otomatis tersimpan ke
                server.</span>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-xs font-semibold text-gray-700">Model 3D (.glb) <span
                class="text-warning">*</span></label>
        <span class="mb-1 block text-[10px] text-gray-400">Maksimal 20MB.</span>
        <input type="file" name="model_3d_file" accept=".glb"
            class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
    </div>

    <div>
        <label class="mb-1 block text-xs font-semibold text-gray-700">Model 3D iOS (.usdz)</label>
        <span class="mb-1 block text-[10px] text-gray-400">Maksimal 50MB.</span>
        <input type="file" name="model_3d_usdz_file" accept=".usdz"
            class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
    </div>

    <div>
        <label class="mb-1 block text-xs font-semibold text-gray-700">Audio Narasi (.mp3)</label>
        <span class="mb-1 block text-[10px] text-gray-400">Maksimal 10MB.</span>
        <input type="file" name="audio_narration_file" accept="audio/*"
            class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
    </div>
</div>

<!-- Previews and Narration -->
<span id="current-model-3d" class="mt-1 block text-[10px] text-gray-400"></span>
<span id="current-model-3d-usdz" class="mt-1 block text-[10px] text-gray-400"></span>

<div id="model-3d-preview-container" style="display: none;"
    class="mt-2.5 flex flex-col gap-1.5 rounded-xl border border-gray-100 bg-gray-50/50 p-3">
    <span class="text-primary text-[10px] font-bold uppercase tracking-wider">Pratinjau Model 3D</span>
    <div class="relative h-44 w-full overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
        <model-viewer id="model-3d-preview" class="h-full w-full" camera-controls auto-rotate
            shadow-intensity="1" touch-action="pan-y">
        </model-viewer>
    </div>
</div>

<div>
    <span id="current-audio" class="mt-1 block text-[10px] text-gray-400"></span>
    <div id="audio-preview-container" style="display: none;"
        class="mt-2.5 rounded-xl border border-gray-100 bg-gray-50/50 p-3" x-data="{
            playing: false,
            dragging: false,
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
                    this.audio = this.$refs.previewAudio;
                }
                if (this.playing) {
                    this.audio.pause();
                } else {
                    this.audio.play();
                }
                this.playing = !this.playing;
            },
            resetPlayer() {
                this.playing = false;
                this.currentTime = 0;
                if (this.audio) {
                    this.audio.pause();
                }
            },
            init() {
                this.$nextTick(() => {
                    this.audio = this.$refs.previewAudio;
                    const el = this.audio;
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
                    const observer = new MutationObserver(() => {
                        this.resetPlayer();
                        setTimeout(() => {
                            this.duration = el.duration || 0;
                        }, 200);
                    });
                    observer.observe(el, { attributes: true, attributeFilter: ['src'] });
                });
            }
        }">
        <audio id="audio-preview" x-ref="previewAudio" preload="auto" class="hidden"></audio>

        <div class="flex items-center gap-3">
            <button type="button" @click="togglePlay()"
                class="bg-primary flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white shadow-[0_4px_10px_rgba(30,81,40,0.3)] transition-all active:scale-95">
                <svg x-show="!playing" class="w-4.5 h-4.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z" />
                </svg>
                <svg x-show="playing" class="w-4.5 h-4.5 text-white" fill="currentColor" viewBox="0 0 24 24"
                    style="display: none;">
                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" fill="currentColor" />
                </svg>
            </button>
            <div class="min-w-0 flex-1">
                <div class="text-charcoal text-xs font-bold">Audio Playback Narasi</div>
                <div class="mt-1 flex items-center gap-2">
                    <span class="min-w-6 text-[9px] font-bold tabular-nums text-gray-500"
                        x-text="formatTime(currentTime)">0:00</span>
                    <input type="range" min="0" :max="duration || 100" x-model.number="currentTime"
                        @mousedown="dragging = true" @touchstart="dragging = true"
                        @change="if (audio && duration > 0) { try { audio.currentTime = currentTime; } catch(e) {} } else { currentTime = 0; }; dragging = false;"
                        class="accent-primary outline-hidden h-1 flex-1 cursor-pointer appearance-none rounded-full bg-gray-100"
                        :style="'background: linear-gradient(to right, #1E5128 0%, #1E5128 ' + (currentTime / (duration ||
                            100) * 100) + '%, #e5e7eb ' + (currentTime / (duration || 100) * 100) +
                        '%, #e5e7eb 100%);'">
                    <span class="min-w-6 text-right text-[9px] font-bold tabular-nums text-gray-500"
                        x-text="duration ? formatTime(duration) : '0:00'">0:00</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleModelSelect(value) {
        const fields = document.getElementById('new-model-fields');
        const modelPreviewContainer = document.getElementById('model-3d-preview-container');
        const modelPreview = document.getElementById('model-3d-preview');
        const audioPreviewContainer = document.getElementById('audio-preview-container');
        const audioPreview = document.getElementById('audio-preview');
        const markerBadge = document.getElementById('existing-marker-badge');
        const markerBadgeText = document.getElementById('existing-marker-id-text');

        const clearPreviews = () => {
            if (modelPreview) modelPreview.src = '';
            if (modelPreviewContainer) modelPreviewContainer.style.display = 'none';
            if (audioPreview) audioPreview.src = '';
            if (audioPreviewContainer) audioPreviewContainer.style.display = 'none';
        };

        if (value === 'new') {
            if (fields) fields.classList.remove('hidden');
            if (markerBadge) markerBadge.classList.add('hidden');
            clearPreviews();
        } else if (value === 'none') {
            if (fields) fields.classList.add('hidden');
            if (markerBadge) markerBadge.classList.add('hidden');
            clearPreviews();
        } else {
            if (fields) fields.classList.add('hidden');
            const select = document.getElementById('ar_model_id_select');
            if (select) {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption) {
                    const glbUrl = selectedOption.getAttribute('data-glb');
                    const audioUrl = selectedOption.getAttribute('data-audio');
                    const markerId = selectedOption.getAttribute('data-marker-id');

                    if (glbUrl && modelPreview && modelPreviewContainer) {
                        modelPreview.src = glbUrl;
                        modelPreviewContainer.style.display = 'flex';
                    } else {
                        if (modelPreview) modelPreview.src = '';
                        if (modelPreviewContainer) modelPreviewContainer.style.display = 'none';
                    }

                    if (audioUrl && audioPreview && audioPreviewContainer) {
                        audioPreview.src = audioUrl;
                        audioPreviewContainer.style.display = 'flex';
                    } else {
                        if (audioPreview) audioPreview.src = '';
                        if (audioPreviewContainer) audioPreviewContainer.style.display = 'none';
                    }

                    if (markerId && markerBadge && markerBadgeText) {
                        markerBadgeText.textContent = markerId;
                        markerBadge.classList.remove('hidden');
                    } else if (markerBadge) {
                        markerBadge.classList.add('hidden');
                    }
                }
            }
        }
    }
</script>
