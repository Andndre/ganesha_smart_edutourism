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
    
    const item = document.createElement('div');
    item.className = 'story-item relative bg-white p-4 rounded-xl border border-gray-100 shadow-sm';
    
    const titleVal = story ? story.title : '';
    const contentVal = story ? story.content : '';
    const typeVal = story ? story.story_type : 'history';
    
    item.innerHTML = `
        <button type="button" onclick="this.closest('.story-item').remove()" class="absolute top-2 right-2 p-1 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <div class="mb-3">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Judul Kisah <span class="story-index">${index + 1}</span></label>
            <input type="text" name="story_title[]" required placeholder="Contoh: Asal Usul Pura" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
        </div>
        <div class="mb-3">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tipe Kisah</label>
            <select name="story_type[]" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">
                <option value="history">Sejarah (History)</option>
                <option value="philosophy">Filosofi (Philosophy)</option>
                <option value="value">Nilai-Nilai Luhur (Value)</option>
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Konten Kisah</label>
            <textarea name="story_content[]" rows="4" required placeholder="Tulis kisah lengkap di sini..." class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none"></textarea>
        </div>
    `;
    
    item.querySelector('input[name="story_title[]"]').value = titleVal;
    item.querySelector('select[name="story_type[]"]').value = typeVal;
    item.querySelector('textarea[name="story_content[]"]').value = contentVal;
    
    list.appendChild(item);
}
</script>
