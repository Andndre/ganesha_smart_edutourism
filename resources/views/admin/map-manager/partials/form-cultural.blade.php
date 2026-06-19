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
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 10H11a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"></path>
                </svg>
            </button>
            <span class="mx-1 my-auto h-5 w-px bg-gray-200"></span>
            <button type="button" data-action="image"
                class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                title="Unggah Gambar">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16l4.586-4.586a2 2 0 012-2h.93a2 2 0 011.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                    </path>
                    <circle cx="12" cy="13" r="3"></circle>
                </svg>
            </button>
        </div>

        <!-- TipTap Editor Container -->
        <div id="cultural-editor"
            class="focus-within:border-primary focus-within:ring-primary/20 max-h-75 min-h-30 w-full overflow-y-auto rounded-b-xl border border-gray-200 bg-white p-4 text-sm focus-within:ring-1">
        </div>
        <textarea name="description" class="hidden"></textarea>
    </div>

    @include('admin.map-manager.partials.form-cultural.ar-model-section')

    @include('admin.map-manager.partials.form-cultural.quiz-section')

    @include('admin.map-manager.partials.form-cultural.story-section')

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

    <div class="accessibility-notes-container">
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
    import Image from 'https://esm.sh/@tiptap/extension-image';

    // Expose TipTap modules globally for dynamic storytelling editors
    window.TipTapEditor = Editor;
    window.TipTapStarterKit = StarterKit;
    window.TipTapImage = Image;

    document.addEventListener('DOMContentLoaded', () => {
        const textarea = document.querySelector('#form-cultural textarea[name="description"]');
        const editorEl = document.getElementById('cultural-editor');
        if (!editorEl) return;

        const editor = new Editor({
            element: editorEl,
            extensions: [StarterKit, Image],
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
                    else if (action === 'image') {
                        const input = document.createElement('input');
                        input.type = 'file';
                        input.accept = 'image/*';
                        input.onchange = async () => {
                            const file = input.files[0];
                            if (!file) return;

                            Swal.fire({
                                title: 'Mengunggah Gambar...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            const formData = new FormData();
                            formData.append('image', file);
                            formData.append('_token', '{{ csrf_token() }}');

                            try {
                                const response = await fetch(
                                    '{{ route('admin.cultural-objects.upload-image') }}', {
                                        method: 'POST',
                                        body: formData
                                    });
                                const data = await response.json();
                                Swal.close();

                                if (data.url) {
                                    editor.chain().focus().setImage({
                                        src: data.url
                                    }).run();
                                } else {
                                    Swal.fire('Gagal',
                                        'Terjadi kesalahan saat mengunggah gambar.',
                                        'error');
                                }
                            } catch (error) {
                                Swal.close();
                                Swal.fire('Gagal', 'Koneksi ke server terputus.',
                                    'error');
                            }
                        };
                        input.click();
                    }

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
<script type="module" src="{{ asset('js/model-viewer.min.js') }}"></script>
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
