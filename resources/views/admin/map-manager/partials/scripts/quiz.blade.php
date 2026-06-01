<script>
// ==========================================
// QUIZ MANAGEMENT & AUTOPopulation
// ==========================================
function toggleQuizzes(checkbox) {
    const btn = document.getElementById('btn-manage-quizzes');
    const list = document.getElementById('quizzes-list');
    if (!btn || !list) return;
    
    if (checkbox.checked) {
        btn.classList.remove('hidden');
        btn.classList.add('flex');
        if (list.children.length === 0) {
            addQuizField();
        }
        openQuizModal();
    } else {
        btn.classList.add('hidden');
        btn.classList.remove('flex');
        // We intentionally do not clear list.innerHTML here
        // so if the user accidentally unchecks, the quizzes aren't lost.
    }
}

function openQuizModal() {
    const modal = document.getElementById('quizzes-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeQuizModal() {
    const modal = document.getElementById('quizzes-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function addQuizField(quiz = null) {
    const list = document.getElementById('quizzes-list');
    if (!list) return;
    
    const index = list.children.length;
    
    const html = `
        <div class="quiz-item relative bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <button type="button" onclick="this.closest('.quiz-item').remove()" class="absolute top-2 right-2 p-1 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="mb-3">
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Pertanyaan ${index + 1}</label>
                <textarea name="quiz_question[]" rows="2" required placeholder="Contoh: Apa nama tempat ini?" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">${quiz ? quiz.question : ''}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Opsi A</label>
                    <input type="text" name="quiz_option_a[]" required value="${quiz ? quiz.option_a : ''}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Opsi B</label>
                    <input type="text" name="quiz_option_b[]" required value="${quiz ? quiz.option_b : ''}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Opsi C</label>
                    <input type="text" name="quiz_option_c[]" required value="${quiz ? quiz.option_c : ''}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Opsi D</label>
                    <input type="text" name="quiz_option_d[]" required value="${quiz ? quiz.option_d : ''}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-primary focus:outline-none">
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Jawaban Benar</label>
                <select name="quiz_correct_option[]" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">
                    <option value="A" ${quiz && quiz.correct_option === 'A' ? 'selected' : ''}>Opsi A</option>
                    <option value="B" ${quiz && quiz.correct_option === 'B' ? 'selected' : ''}>Opsi B</option>
                    <option value="C" ${quiz && quiz.correct_option === 'C' ? 'selected' : ''}>Opsi C</option>
                    <option value="D" ${quiz && quiz.correct_option === 'D' ? 'selected' : ''}>Opsi D</option>
                </select>
            </div>
        </div>
    `;
    list.insertAdjacentHTML('beforeend', html);
}
</script>
