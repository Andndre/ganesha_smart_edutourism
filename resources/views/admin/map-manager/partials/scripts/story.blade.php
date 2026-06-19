<script>
// ==========================================
// STORY MANAGEMENT
// ==========================================
let currentActiveStoryTab = 'history';

function toggleStories(checkbox) {
    const btn = document.getElementById('btn-manage-stories');
    if (!btn) return;
    
    if (checkbox.checked) {
        btn.classList.remove('hidden');
        btn.classList.add('flex');
        
        // Check if all lists are empty
        const hEmpty = document.getElementById('stories-list-history').children.length === 0;
        const pEmpty = document.getElementById('stories-list-philosophy').children.length === 0;
        const vEmpty = document.getElementById('stories-list-value').children.length === 0;
        
        if (hEmpty && pEmpty && vEmpty) {
            addStoryField(null, 'history');
        }
        openStoryModal();
    } else {
        btn.classList.add('hidden');
        btn.classList.remove('flex');
    }
}

function openStoryModal() {
    window.dispatchEvent(new CustomEvent('open-stories-modal'));
}

function closeStoryModal() {
    window.dispatchEvent(new CustomEvent('close-stories-modal'));
}

function switchStoryTab(category) {
    currentActiveStoryTab = category;
    
    // Hide all contents
    document.querySelectorAll('.story-tab-content').forEach(el => el.classList.add('hidden'));
    
    // Show target content
    const targetContent = document.getElementById(`stories-list-${category}`);
    if (targetContent) {
        targetContent.classList.remove('hidden');
    }
    
    // Update button styles
    document.querySelectorAll('.story-tab-btn').forEach(btn => {
        btn.classList.remove('border-primary', 'text-primary');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    const activeBtn = document.getElementById(`tab-btn-${category}`);
    if (activeBtn) {
        activeBtn.classList.add('border-primary', 'text-primary');
        activeBtn.classList.remove('border-transparent', 'text-gray-500');
    }
}

function updateStoryIndexes() {
    ['history', 'philosophy', 'value'].forEach(category => {
        const list = document.getElementById(`stories-list-${category}`);
        if (list) {
            Array.from(list.children).forEach((item, index) => {
                const indexSpan = item.querySelector('.story-index');
                if (indexSpan) {
                    indexSpan.textContent = index + 1;
                }
            });
        }
    });
}

function addStoryField(story = null, forcedCategory = null) {
    const typeVal = story ? story.story_type : (forcedCategory || currentActiveStoryTab);
    const list = document.getElementById(`stories-list-${typeVal}`);
    if (!list) return;
    
    const index = list.children.length;
    const editorId = `story-editor-${Date.now()}-${index}`;
    
    const item = document.createElement('div');
    item.className = 'story-item relative bg-gray-50/60 p-4 rounded-xl border border-gray-100 shadow-xs space-y-3 transition-all duration-300';
    
    const titleVal = story ? story.title : '';
    const contentVal = story ? story.content : '';
    
    const categoryLabel = typeVal === 'history' ? 'Sejarah' : (typeVal === 'philosophy' ? 'Filosofi' : 'Nilai Luhur');
    
    item.innerHTML = `
        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">${categoryLabel} <span class="story-index">${index + 1}</span></span>
            <div class="flex items-center gap-1.5">
                <!-- Move Up Button -->
                <button type="button" class="btn-move-up p-1 text-gray-400 hover:text-primary hover:bg-white rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Geser ke Atas">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path></svg>
                </button>
                <!-- Move Down Button -->
                <button type="button" class="btn-move-down p-1 text-gray-400 hover:text-primary hover:bg-white rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Geser ke Bawah">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <!-- Delete Button -->
                <button type="button" class="btn-delete-story p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors ml-2" title="Hapus Kisah">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-600">Judul Kisah</label>
            <input type="text" name="story_title[]" required placeholder="Contoh: Asal Usul Pura" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary focus:outline-none bg-white">
        </div>
        <input type="hidden" name="story_type[]" value="${typeVal}">
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-600">Konten Kisah</label>
            
            <!-- Dynamic TipTap Toolbar -->
            <div class="story-editor-toolbar flex flex-wrap gap-1 rounded-t-lg border border-b-0 border-gray-200 bg-gray-50 p-1.5 text-gray-600">
                <button type="button" data-action="bold" class="flex items-center justify-center rounded p-1 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Bold">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12h8a4 4 0 100-8H6v8zm0 0h10a4 4 0 110 8H6v-8z"></path></svg>
                </button>
                <button type="button" data-action="italic" class="flex items-center justify-center rounded p-1 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Italic">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4h4M12 4v16M10 20h4M15 4L9 20"></path></svg>
                </button>
                <span class="mx-1 my-auto h-4 w-px bg-gray-200"></span>
                <button type="button" data-action="bulletList" class="flex items-center justify-center rounded p-1 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Bullet List">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16M8 6h.01M8 12h.01M8 18h.01"></path></svg>
                </button>
                <button type="button" data-action="orderedList" class="flex items-center justify-center rounded p-1 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Ordered List">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5h11M9 12h11M9 19h11M4 4h2v4H4V4zm0 6h2a1 1 0 011 1v1a1 1 0 01-1 1H4v-1h2v-1H4v-1zm0 6h2v3H4v-3z"></path></svg>
                </button>
                <span class="mx-1 my-auto h-4 w-px bg-gray-200"></span>
                <button type="button" data-action="image" class="flex items-center justify-center rounded p-1 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Unggah Gambar">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012-2h.93a2 2 0 011.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><circle cx="12" cy="13" r="3"></circle></svg>
                </button>
            </div>
            
            <!-- Editor Container -->
            <div id="${editorId}" class="story-editor focus-within:border-primary focus-within:ring-primary/20 max-h-50 min-h-25 w-full overflow-y-auto rounded-b-lg border border-gray-200 bg-white p-3 text-sm focus-within:ring-1"></div>
            <textarea name="story_content[]" class="hidden"></textarea>
        </div>
    `;
    
    // Set initial values
    item.querySelector('input[name="story_title[]"]').value = titleVal;
    
    const textarea = item.querySelector('textarea[name="story_content[]"]');
    textarea.value = contentVal;
    
    list.appendChild(item);
    
    // Initialize TipTap Editor
    let editor = null;
    if (window.TipTapEditor && window.TipTapStarterKit && window.TipTapImage) {
        editor = new window.TipTapEditor({
            element: document.getElementById(editorId),
            extensions: [window.TipTapStarterKit, window.TipTapImage],
            content: contentVal,
            editorProps: {
                attributes: {
                    class: 'focus:outline-none prose max-w-none text-sm text-gray-700 leading-relaxed min-h-20',
                }
            },
            onUpdate({ editor }) {
                textarea.value = editor.getHTML();
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                textarea.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        
        // Connect Toolbar
        const toolbar = item.querySelector('.story-editor-toolbar');
        if (toolbar) {
            toolbar.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const action = btn.getAttribute('data-action');
                    
                    if (action === 'bold') editor.chain().focus().toggleBold().run();
                    else if (action === 'italic') editor.chain().focus().toggleItalic().run();
                    else if (action === 'bulletList') editor.chain().focus().toggleBulletList().run();
                    else if (action === 'orderedList') editor.chain().focus().toggleOrderedList().run();
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
                                const response = await fetch('{{ route("admin.cultural-objects.upload-image") }}', {
                                    method: 'POST',
                                    body: formData
                                });
                                const data = await response.json();
                                Swal.close();

                                if (data.url) {
                                    editor.chain().focus().setImage({ src: data.url }).run();
                                } else {
                                    Swal.fire('Gagal', 'Terjadi kesalahan saat mengunggah gambar.', 'error');
                                }
                            } catch (error) {
                                Swal.close();
                                Swal.fire('Gagal', 'Koneksi ke server terputus.', 'error');
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
    }
    
    // Connect Move buttons
    item.querySelector('.btn-move-up').addEventListener('click', () => {
        const prev = item.previousElementSibling;
        if (prev) {
            list.insertBefore(item, prev);
            updateStoryIndexes();
            if (typeof markUnsaved === 'function') {
                markUnsaved();
            }
        }
    });
    
    item.querySelector('.btn-move-down').addEventListener('click', () => {
        const next = item.nextElementSibling;
        if (next) {
            list.insertBefore(next, item);
            updateStoryIndexes();
            if (typeof markUnsaved === 'function') {
                markUnsaved();
            }
        }
    });
    
    // Connect Delete button
    item.querySelector('.btn-delete-story').addEventListener('click', () => {
        if (editor) {
            editor.destroy();
        }
        item.remove();
        updateStoryIndexes();
        if (typeof markUnsaved === 'function') {
            markUnsaved();
        }
    });
}
</script>
