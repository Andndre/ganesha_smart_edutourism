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
    window.dispatchEvent(new CustomEvent('open-quizzes-modal'));
}

function closeQuizModal() {
    window.dispatchEvent(new CustomEvent('close-quizzes-modal'));
}

function addQuizField(quiz = null) {
    const list = document.getElementById('quizzes-list');
    if (!list) return;
    
    const index = list.children.length;
    
    const html = `
        <div class="quiz-item relative bg-white p-4 rounded-xl border border-gray-100 shadow-sm" x-data="{ locale: 'en' }">
            <button type="button" onclick="this.closest('.quiz-item').remove()" class="absolute top-2 right-2 p-1 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="mb-3">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-sm font-semibold text-gray-700">Pertanyaan ${index + 1}</label>
                    <div class="flex gap-1">
                        <button @click="locale = 'en'" :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded text-[10px] font-semibold transition-all" type="button">EN</button>
                        <button @click="locale = 'id'" :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded text-[10px] font-semibold transition-all" type="button">ID</button>
                    </div>
                </div>
                <div x-show="locale === 'en'">
                    <textarea name="quiz_question[${index}][en]" rows="2" placeholder="e.g. What is the name of this temple?" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">${quiz ? (typeof quiz.question === 'object' ? (quiz.question.en || '') : quiz.question) : ''}</textarea>
                </div>
                <div x-show="locale === 'id'">
                    <textarea name="quiz_question[${index}][id]" rows="2" placeholder="Contoh: Apa nama pura ini?" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">${quiz ? (typeof quiz.question === 'object' ? (quiz.question.id || '') : quiz.question) : ''}</textarea>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                ${['A', 'B', 'C', 'D'].map(opt => {
                    const key = 'option_' + opt.toLowerCase();
                    const val = quiz ? (typeof quiz[key] === 'object' ? quiz[key] : { en: quiz[key], id: quiz[key] }) : { en: '', id: '' };
                    return `
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Opsi ${opt}</label>
                        <div x-show="locale === 'en'">
                            <input type="text" name="quiz_${key}[${index}][en]" required value="${val.en || ''}" placeholder="EN" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-primary focus:outline-none">
                        </div>
                        <div x-show="locale === 'id'">
                            <input type="text" name="quiz_${key}[${index}][id]" required value="${val.id || ''}" placeholder="ID" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-primary focus:outline-none">
                        </div>
                    </div>`;
                }).join('')}
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Jawaban Benar</label>
                <select name="quiz_correct_option[${index}]" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">
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
