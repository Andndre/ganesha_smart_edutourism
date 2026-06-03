<script>
// ==========================================
// STORY MANAGEMENT
// ==========================================
function toggleStories(checkbox) {
    const btn = document.getElementById('btn-manage-stories');
    const list = document.getElementById('stories-list');
    if (!btn || !list) return;
    
    if (checkbox.checked) {
        btn.classList.remove('hidden');
        btn.classList.add('flex');
        if (list.children.length === 0) {
            addStoryField();
        }
        openStoryModal();
    } else {
        btn.classList.add('hidden');
        btn.classList.remove('flex');
    }
}

function openStoryModal() {
    const modal = document.getElementById('stories-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeStoryModal() {
    const modal = document.getElementById('stories-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function addStoryField(story = null) {
    const list = document.getElementById('stories-list');
    if (!list) return;
    
    const index = list.children.length;
    const editorId = `story-editor-${Date.now()}-${index}`;
    
    const item = document.createElement('div');
    item.className = 'story-item relative bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-3';
    
    const titleVal = story ? story.title : '';
    const contentVal = story ? story.content : '';
    const typeVal = story ? story.story_type : 'history';
    
    item.innerHTML = `
        <button type="button" class="btn-delete-story absolute top-2 right-2 p-1 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Judul Kisah <span class="story-index">${index + 1}</span></label>
            <input type="text" name="story_title[]" required placeholder="Contoh: Asal Usul Pura" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tipe Kisah</label>
            <select name="story_type[]" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">
                <option value="history">Sejarah (History)</option>
                <option value="philosophy">Filosofi (Philosophy)</option>
                <option value="value">Nilai-Nilai Luhur (Value)</option>
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Konten Kisah</label>
            
            <!-- Dynamic TipTap Toolbar -->
            <div class="story-editor-toolbar flex flex-wrap gap-1 rounded-t-xl border border-b-0 border-gray-200 bg-gray-50 p-1.5 text-gray-600">
                <button type="button" data-action="bold" class="flex items-center justify-center rounded-lg border border-transparent p-1 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Bold">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12h8a4 4 0 100-8H6v8zm0 0h10a4 4 0 110 8H6v-8z"></path></svg>
                </button>
                <button type="button" data-action="italic" class="flex items-center justify-center rounded-lg border border-transparent p-1 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Italic">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4h4M12 4v16M10 20h4M15 4L9 20"></path></svg>
                </button>
                <span class="mx-1 my-auto h-4 w-px bg-gray-200"></span>
                <button type="button" data-action="bulletList" class="flex items-center justify-center rounded-lg border border-transparent p-1 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Bullet List">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16M8 6h.01M8 12h.01M8 18h.01"></path></svg>
                </button>
                <button type="button" data-action="orderedList" class="flex items-center justify-center rounded-lg border border-transparent p-1 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Ordered List">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5h11M9 12h11M9 19h11M4 4h2v4H4V4zm0 6h2a1 1 0 011 1v1a1 1 0 01-1 1H4v-1h2v-1H4v-1zm0 6h2v3H4v-3z"></path></svg>
                </button>
            </div>
            
            <!-- Editor Container -->
            <div id="${editorId}" class="story-editor focus-within:border-primary focus-within:ring-primary/20 max-h-[200px] min-h-[100px] w-full overflow-y-auto rounded-b-xl border border-gray-200 bg-white p-3 text-sm focus-within:ring-1"></div>
            <textarea name="story_content[]" class="hidden"></textarea>
        </div>
    `;
    
    // Set initial static values
    item.querySelector('input[name="story_title[]"]').value = titleVal;
    item.querySelector('select[name="story_type[]"]').value = typeVal;
    
    const textarea = item.querySelector('textarea[name="story_content[]"]');
    textarea.value = contentVal;
    
    list.appendChild(item);
    
    // Initialize TipTap Editor
    let editor = null;
    if (window.TipTapEditor && window.TipTapStarterKit) {
        editor = new window.TipTapEditor({
            element: document.getElementById(editorId),
            extensions: [window.TipTapStarterKit],
            content: contentVal,
            editorProps: {
                attributes: {
                    class: 'focus:outline-none prose max-w-none text-sm text-gray-700 leading-relaxed min-h-[80px]',
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
    
    // Set up cleanup on delete button click to destroy TipTap instance
    item.querySelector('.btn-delete-story').addEventListener('click', () => {
        if (editor) {
            editor.destroy();
        }
        item.remove();
        if (typeof markUnsaved === 'function') {
            markUnsaved();
        }
    });
}
</script>
