{{-- FORM 1: Cultural Object --}}
<form id="form-cultural" action="{{ route('admin.cultural-objects.store') }}" method="POST"
    enctype="multipart/form-data" class="hidden space-y-4">
    @csrf
    <div id="method-cultural"></div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Objek Budaya <span
                class="text-warning">*</span></label>
        <input type="text" name="name" required placeholder="Contoh: Pura Penataran Agung"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kategori Budaya <span
                class="text-warning">*</span></label>
        <select name="category" required
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            <option value="temple">Pura / Tempat Suci</option>
            <option value="house">Pekarangan Adat / Rumah</option>
            <option value="craft">Kerajinan Seni</option>
            <option value="tradition">Tradisi Adat / Upacara</option>
        </select>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi</label>
        
        <!-- TipTap Editor Toolbar -->
        <div id="cultural-editor-toolbar" class="flex flex-wrap gap-1 p-1.5 bg-gray-50 border border-gray-200 rounded-t-xl text-gray-600 border-b-0">
            <button type="button" data-action="bold" class="p-1.5 rounded-lg hover:bg-gray-200/70 hover:text-gray-900 transition-colors flex items-center justify-center border border-transparent" title="Bold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12h8a4 4 0 100-8H6v8zm0 0h10a4 4 0 110 8H6v-8z"></path></svg>
            </button>
            <button type="button" data-action="italic" class="p-1.5 rounded-lg hover:bg-gray-200/70 hover:text-gray-900 transition-colors flex items-center justify-center border border-transparent" title="Italic">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4h4M12 4v16M10 20h4M15 4L9 20"></path></svg>
            </button>
            <button type="button" data-action="strike" class="p-1.5 rounded-lg hover:bg-gray-200/70 hover:text-gray-900 transition-colors flex items-center justify-center border border-transparent" title="Strike">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M16 8.5C16 6.57 14.21 5 12 5C9.79 5 8 6.57 8 8.5C8 9.8 8.8 10.9 10 11.5M14 12.5C15.2 13.1 16 14.2 16 15.5C16 17.43 14.21 19 12 19C9.79 19 8 17.43 8 15.5"></path></svg>
            </button>
            <span class="w-px h-5 bg-gray-200 my-auto mx-1"></span>
            <button type="button" data-action="bulletList" class="p-1.5 rounded-lg hover:bg-gray-200/70 hover:text-gray-900 transition-colors flex items-center justify-center border border-transparent" title="Bullet List">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16M8 6h.01M8 12h.01M8 18h.01"></path></svg>
            </button>
            <button type="button" data-action="orderedList" class="p-1.5 rounded-lg hover:bg-gray-200/70 hover:text-gray-900 transition-colors flex items-center justify-center border border-transparent" title="Ordered List">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5h11M9 12h11M9 19h11M4 4h2v4H4V4zm0 6h2a1 1 0 011 1v1a1 1 0 01-1 1H4v-1h2v-1H4v-1zm0 6h2v3H4v-3z"></path></svg>
            </button>
            <span class="w-px h-5 bg-gray-200 my-auto mx-1"></span>
            <button type="button" data-action="undo" class="p-1.5 rounded-lg hover:bg-gray-200/70 hover:text-gray-900 transition-colors flex items-center justify-center border border-transparent" title="Undo">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
            </button>
            <button type="button" data-action="redo" class="p-1.5 rounded-lg hover:bg-gray-200/70 hover:text-gray-900 transition-colors flex items-center justify-center border border-transparent" title="Redo">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"></path></svg>
            </button>
        </div>

        <!-- TipTap Editor Container -->
        <div id="cultural-editor" class="w-full rounded-b-xl border border-gray-200 p-4 text-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary/20 bg-white min-h-[120px] max-h-[300px] overflow-y-auto"></div>
        <textarea name="description" class="hidden"></textarea>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">ID Marker AR</label>
        <span class="mb-2 block text-xs text-gray-500">Opsional. Digunakan untuk integrasi Augmented Reality</span>
        <input type="text" name="ar_marker_id" placeholder="Contoh: MARKER_PURA_01"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">Model 3D</label>
        <span class="mb-2 block text-xs text-gray-500">Format .glb, maksimal 20MB</span>
        <input type="file" name="model_3d_file" accept=".glb"
            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
        <span id="current-model-3d" class="text-[10px] text-gray-400 block mt-1"></span>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">Audio Narasi</label>
        <span class="mb-2 block text-xs text-gray-500">Format .mp3, maksimal 10MB</span>
        <input type="file" name="audio_narration_file" accept="audio/*"
            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
        <span id="current-audio" class="text-[10px] text-gray-400 block mt-1"></span>
    </div>

    <div class="pt-4 border-t border-gray-100">
        <label class="flex items-center space-x-2 cursor-pointer mb-3">
            <input type="checkbox" id="has_quiz" name="has_quiz" value="1" class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary" onchange="toggleQuizzes(this)">
            <span class="text-sm font-semibold text-gray-700">Tambahkan Kuis Edutourism?</span>
        </label>
        
        <button type="button" id="btn-manage-quizzes" onclick="openQuizModal()" class="hidden w-full rounded-xl border-2 border-primary text-primary py-2.5 text-sm font-semibold hover:bg-primary/5 transition-colors items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Kelola Soal Kuis
        </button>

        <div id="quizzes-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-charcoal/50 backdrop-blur-sm p-4 justify-center items-center">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="font-display font-bold text-lg text-charcoal">Kelola Kuis Edutourism</h3>
                        <p class="text-xs text-gray-500 mt-1">Soal-soal ini akan muncul saat turis tiba di lokasi ini.</p>
                    </div>
                    <button type="button" onclick="closeQuizModal()" class="text-gray-400 hover:text-red-500 rounded-lg p-2 hover:bg-red-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto flex-1 space-y-6" id="quizzes-list">
                    <!-- Quizzes will be appended here -->
                </div>
                
                <div class="p-5 border-t border-gray-100 bg-gray-50/50 space-y-3">
                    <button type="button" onclick="addQuizField()" class="w-full rounded-xl border-2 border-dashed border-gray-200 py-3 text-sm font-semibold text-gray-500 hover:border-primary hover:text-primary hover:bg-green-50 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Soal Kuis
                    </button>
                    <button type="button" onclick="closeQuizModal()" class="w-full rounded-xl bg-primary py-3 text-sm font-semibold text-white hover:bg-primary-600 transition-all shadow-lg shadow-primary/20">Selesai & Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">Foto Sejarah</label>
        <span class="mb-2 block text-xs text-gray-500">Dapat memilih beberapa file gambar sekaligus</span>
        <input type="file" name="historical_images[]" multiple accept="image/*"
            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
        <div id="current-images" class="flex flex-wrap gap-1 mt-2"></div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase">Latitude</label>
            <input type="text" name="latitude" readonly
                class="w-full rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase">Longitude</label>
            <input type="text" name="longitude" readonly
                class="w-full rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
    </div>

    <div class="flex items-center gap-2 py-1">
        <input type="checkbox" id="cultural_is_accessible" name="is_accessible" value="1"
            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
        <label for="cultural_is_accessible" class="text-sm font-semibold text-gray-700">Akses Ramah
            Disabilitas</label>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas</label>
        <textarea name="accessibility_notes" rows="2"
            placeholder="Contoh: Pintu masuk landai, ramah kursi roda..."
            class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none resize-none">Akses jalan datar ramah kursi roda dan stroller bayi.</textarea>
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
        color: #374151; /* gray-700 */
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
        color: #111827; /* gray-900 */
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
    import { Editor } from 'https://esm.sh/@tiptap/core';
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
            onUpdate({ editor }) {
                if (textarea) {
                    textarea.value = editor.getHTML();
                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                    textarea.dispatchEvent(new Event('change', { bubbles: true }));
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
                    else if (action === 'bulletList') editor.chain().focus().toggleBulletList().run();
                    else if (action === 'orderedList') editor.chain().focus().toggleOrderedList().run();
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
