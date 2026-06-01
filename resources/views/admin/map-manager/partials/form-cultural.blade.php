{{-- FORM 1: Cultural Object --}}
<form id="form-cultural" action="{{ route('admin.cultural-objects.store') }}" method="POST" enctype="multipart/form-data"
    class="hidden space-y-4">
    @csrf
    <div id="method-cultural"></div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Objek Budaya <span
                class="text-warning">*</span></label>
        <input type="text" name="name" required placeholder="Contoh: Pura Penataran Agung"
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Singkat <span
                class="text-warning">*</span></label>
        <input type="text" name="short_description" required placeholder="Contoh: Jantung Spiritual Desa Penglipuran"
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kategori Budaya <span
                class="text-warning">*</span></label>
        <select name="category" required
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
            <option value="temple">Pura / Tempat Suci</option>
            <option value="house">Pekarangan Adat / Rumah</option>
            <option value="craft">Kerajinan Seni</option>
            <option value="tradition">Tradisi Adat / Upacara</option>
        </select>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi</label>

        <!-- TipTap Editor Toolbar -->
        <div id="cultural-editor-toolbar"
            class="flex flex-wrap gap-1 rounded-t-xl border border-b-0 border-gray-200 bg-gray-50 p-1.5 text-gray-600">
            <button type="button" data-action="bold"
                class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                title="Bold">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6 12h8a4 4 0 100-8H6v8zm0 0h10a4 4 0 110 8H6v-8z"></path>
                </svg>
            </button>
            <button type="button" data-action="italic"
                class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                title="Italic">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 4h4M12 4v16M10 20h4M15 4L9 20"></path>
                </svg>
            </button>
            <button type="button" data-action="strike"
                class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                title="Strike">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 12h16M16 8.5C16 6.57 14.21 5 12 5C9.79 5 8 6.57 8 8.5C8 9.8 8.8 10.9 10 11.5M14 12.5C15.2 13.1 16 14.2 16 15.5C16 17.43 14.21 19 12 19C9.79 19 8 17.43 8 15.5">
                    </path>
                </svg>
            </button>
            <span class="mx-1 my-auto h-5 w-px bg-gray-200"></span>
            <button type="button" data-action="bulletList"
                class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                title="Bullet List">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 6h16M4 12h16M4 18h16M8 6h.01M8 12h.01M8 18h.01"></path>
                </svg>
            </button>
            <button type="button" data-action="orderedList"
                class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                title="Ordered List">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5h11M9 12h11M9 19h11M4 4h2v4H4V4zm0 6h2a1 1 0 011 1v1a1 1 0 01-1 1H4v-1h2v-1H4v-1zm0 6h2v3H4v-3z">
                    </path>
                </svg>
            </button>
            <span class="mx-1 my-auto h-5 w-px bg-gray-200"></span>
            <button type="button" data-action="undo"
                class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                title="Undo">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6">
                    </path>
                </svg>
            </button>
            <button type="button" data-action="redo"
                class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                title="Redo">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 10H11a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"></path>
                </svg>
            </button>
        </div>

        <!-- TipTap Editor Container -->
        <div id="cultural-editor"
            class="focus-within:border-primary focus-within:ring-primary/20 max-h-[300px] min-h-[120px] w-full overflow-y-auto rounded-b-xl border border-gray-200 bg-white p-4 text-sm focus-within:ring-1">
        </div>
        <textarea name="description" class="hidden"></textarea>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">ID Marker AR</label>
        <span class="mb-2 block text-xs text-gray-500">Opsional. Digunakan untuk integrasi Augmented Reality</span>
        <input type="text" name="ar_marker_id" id="ar_marker_id" placeholder="Contoh: MARKER_PURA_01"
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
            oninput="generateARMarker()">
        <input type="hidden" name="ar_marker_patt_content" id="ar_marker_patt_content">

        <!-- Premium Download Button -->
        <div id="ar-download-container" class="mt-2" style="display: none;">
            <button type="button" onclick="downloadARMarker()"
                class="border-primary text-primary hover:bg-primary/5 flex w-full items-center justify-center gap-2 rounded-xl border-2 py-2.5 text-sm font-semibold transition-colors active:scale-95">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Unduh QR Marker AR (.png)
            </button>
            <span class="mt-1 block text-[10px] text-gray-500">Unduh gambar QR Marker berpola AR (.png). Pola (.patt) akan otomatis tersimpan ke server.</span>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">Model 3D (.glb)</label>
        <span class="mb-2 block text-xs text-gray-500">Format .glb, maksimal 20MB. Wajib untuk Android (Scene Viewer).</span>
        <input type="file" name="model_3d_file" accept=".glb"
            class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
        <span id="current-model-3d" class="mt-1 block text-[10px] text-gray-400"></span>
        <div id="model-3d-preview-container" style="display: none;"
            class="mt-2.5 flex flex-col gap-1.5 rounded-xl border border-gray-100 bg-gray-50/50 p-3">
            <span class="text-primary text-[10px] font-bold uppercase tracking-wider">Pratinjau Model 3D</span>
            <div class="relative h-44 w-full overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                <model-viewer id="model-3d-preview" class="h-full w-full" camera-controls auto-rotate
                    shadow-intensity="1" touch-action="pan-y">
                </model-viewer>
            </div>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">Model 3D iOS (.usdz)</label>
        <span class="mb-2 block text-xs text-gray-500">Format .usdz, maksimal 20MB. Wajib untuk AR Apple Quick Look (iPhone/iPad).</span>
        <input type="file" name="model_3d_usdz_file" accept=".usdz"
            class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
        <span id="current-model-3d-usdz" class="mt-1 block text-[10px] text-gray-400"></span>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">Audio Narasi</label>
        <span class="mb-2 block text-xs text-gray-500">Format .mp3, maksimal 10MB</span>
        <input type="file" name="audio_narration_file" accept="audio/*"
            class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
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
                        <span class="min-w-[24px] text-[9px] font-bold tabular-nums text-gray-500"
                            x-text="formatTime(currentTime)">0:00</span>
                        <input type="range" min="0" :max="duration || 100" x-model.number="currentTime"
                            @mousedown="dragging = true"
                            @touchstart="dragging = true"
                            @change="if (audio && duration > 0) { try { audio.currentTime = currentTime; } catch(e) {} } else { currentTime = 0; }; dragging = false;"
                            class="accent-primary outline-hidden h-1 flex-1 cursor-pointer appearance-none rounded-full bg-gray-100"
                            :style="'background: linear-gradient(to right, #1E5128 0%, #1E5128 ' + (currentTime / (duration || 100) * 100) + '%, #e5e7eb ' + (currentTime / (duration || 100) * 100) + '%, #e5e7eb 100%);'">
                        <span class="min-w-[24px] text-right text-[9px] font-bold tabular-nums text-gray-500"
                            x-text="duration ? formatTime(duration) : '0:00'">0:00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="border-t border-gray-100 pt-4">
        <label class="mb-3 flex cursor-pointer items-center space-x-2">
            <input type="checkbox" id="has_quiz" name="has_quiz" value="1"
                class="text-primary focus:ring-primary h-4 w-4 rounded border-gray-300"
                onchange="toggleQuizzes(this)">
            <span class="text-sm font-semibold text-gray-700">Tambahkan Kuis Edutourism?</span>
        </label>

        <button type="button" id="btn-manage-quizzes" onclick="openQuizModal()"
            class="border-primary text-primary hover:bg-primary/5 hidden w-full items-center justify-center gap-2 rounded-xl border-2 py-2.5 text-sm font-semibold transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                </path>
            </svg>
            Kelola Soal Kuis
        </button>

        <div id="quizzes-modal"
            class="bg-charcoal/50 fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto p-4 backdrop-blur-sm">
            <div class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/50 p-5">
                    <div>
                        <h3 class="font-display text-charcoal text-lg font-bold">Kelola Kuis Edutourism</h3>
                        <p class="mt-1 text-xs text-gray-500">Soal-soal ini akan muncul saat turis tiba di lokasi ini.
                        </p>
                    </div>
                    <button type="button" onclick="closeQuizModal()"
                        class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-red-50 hover:text-red-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 space-y-6 overflow-y-auto p-6" id="quizzes-list">
                    <!-- Quizzes will be appended here -->
                </div>

                <div class="space-y-3 border-t border-gray-100 bg-gray-50/50 p-5">
                    <button type="button" onclick="addQuizField()"
                        class="hover:border-primary hover:text-primary flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 py-3 text-sm font-semibold text-gray-500 transition-colors hover:bg-green-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        Tambah Soal Kuis
                    </button>
                    <button type="button" onclick="closeQuizModal()"
                        class="bg-primary hover:bg-primary-600 shadow-primary/20 w-full rounded-xl py-3 text-sm font-semibold text-white shadow-lg transition-all">Selesai
                        & Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">Foto Sejarah</label>
        <span class="mb-2 block text-xs text-gray-500">Dapat memilih beberapa file gambar sekaligus</span>
        <input type="file" name="historical_images[]" multiple accept="image/*"
            class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
        <div id="current-images" class="mt-2 flex flex-wrap gap-1"></div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-400">Latitude</label>
            <input type="text" name="latitude" readonly
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-400">Longitude</label>
            <input type="text" name="longitude" readonly
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
    </div>

    <div class="flex items-center gap-2 py-1">
        <input type="checkbox" id="cultural_is_accessible" name="is_accessible" value="1"
            class="text-primary focus:ring-primary h-4 w-4 rounded border-gray-300">
        <label for="cultural_is_accessible" class="text-sm font-semibold text-gray-700">Akses Ramah
            Disabilitas</label>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas</label>
        <textarea name="accessibility_notes" rows="2" placeholder="Contoh: Pintu masuk landai, ramah kursi roda..."
            class="focus:border-primary w-full resize-none rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">Akses jalan datar ramah kursi roda dan stroller bayi.</textarea>
    </div>

</form>

<style>
    /* TipTap Custom Editor Styles */
    .ProseMirror {
        min-height: 120px;
        outline: none !important;
        font-family: inherit;
    }

    .ProseMirror p {
        margin-bottom: 0.75rem;
        line-height: 1.6;
        color: #374151;
        /* gray-700 */
    }

    .ProseMirror p:last-child {
        margin-bottom: 0;
    }

    .ProseMirror ul {
        list-style-type: disc !important;
        padding-left: 1.5rem !important;
        margin-bottom: 0.75rem !important;
    }

    .ProseMirror ol {
        list-style-type: decimal !important;
        padding-left: 1.5rem !important;
        margin-bottom: 0.75rem !important;
    }

    .ProseMirror li {
        margin-bottom: 0.25rem;
    }

    .ProseMirror strong {
        font-weight: 700 !important;
        color: #111827;
        /* gray-900 */
    }

    .ProseMirror em {
        font-style: italic !important;
    }

    .ProseMirror del {
        text-decoration: line-through !important;
    }

    /* Toolbar button active style matching emerald green (#1E5128) */
    .tiptap-btn-active {
        background-color: rgba(30, 81, 40, 0.1) !important;
        color: #1E5128 !important;
        border-color: rgba(30, 81, 40, 0.2) !important;
    }
</style>

<script type="module">
    import {
        Editor
    } from 'https://esm.sh/@tiptap/core';
    import StarterKit from 'https://esm.sh/@tiptap/starter-kit';

    document.addEventListener('DOMContentLoaded', () => {
        const textarea = document.querySelector('#form-cultural textarea[name="description"]');
        const editorEl = document.getElementById('cultural-editor');
        if (!editorEl) return;

        const editor = new Editor({
            element: editorEl,
            extensions: [StarterKit],
            content: textarea ? textarea.value : '',
            editorProps: {
                attributes: {
                    class: 'focus:outline-none prose max-w-none text-sm text-gray-700 leading-relaxed',
                }
            },
            onUpdate({
                editor
            }) {
                if (textarea) {
                    textarea.value = editor.getHTML();
                    textarea.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    textarea.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }
            }
        });

        window.setCulturalEditorContent = (html) => {
            if (editor) {
                editor.commands.setContent(html || '');
            }
        };

        // Toolbar Interaction
        const toolbar = document.getElementById('cultural-editor-toolbar');
        if (toolbar) {
            toolbar.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const action = btn.getAttribute('data-action');

                    if (action === 'bold') editor.chain().focus().toggleBold().run();
                    else if (action === 'italic') editor.chain().focus().toggleItalic().run();
                    else if (action === 'strike') editor.chain().focus().toggleStrike().run();
                    else if (action === 'bulletList') editor.chain().focus().toggleBulletList()
                        .run();
                    else if (action === 'orderedList') editor.chain().focus()
                    .toggleOrderedList().run();
                    else if (action === 'undo') editor.chain().focus().undo().run();
                    else if (action === 'redo') editor.chain().focus().redo().run();

                    updateToolbarStates();
                });
            });

            function updateToolbarStates() {
                const states = {
                    bold: editor.isActive('bold'),
                    italic: editor.isActive('italic'),
                    strike: editor.isActive('strike'),
                    bulletList: editor.isActive('bulletList'),
                    orderedList: editor.isActive('orderedList')
                };

                toolbar.querySelectorAll('button').forEach(btn => {
                    const action = btn.getAttribute('data-action');
                    if (states[action]) {
                        btn.classList.add('tiptap-btn-active');
                    } else {
                        btn.classList.remove('tiptap-btn-active');
                    }
                });
            }

            editor.on('selectionUpdate', updateToolbarStates);
            editor.on('transaction', updateToolbarStates);
        }
    });
</script>

<!-- Google model-viewer for 3D GLB models with Meshopt compression -->
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
<script type="module">
    // Configure Meshopt Decoder before model-viewer renders
    document.addEventListener('DOMContentLoaded', () => {
        const ModelViewerElement = customElements.get('model-viewer');
        if (ModelViewerElement) {
            ModelViewerElement.meshoptDecoderLocation =
                'https://unpkg.com/meshoptimizer@0.17.0/meshopt_decoder.js';
        }
    });
</script>
