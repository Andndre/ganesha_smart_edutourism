<script>
// ==========================================
// QUIZ MANAGEMENT & AUTOPopulation
// ==========================================
let quizListSnapshot = '';
let quizModalDirty = false;

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
    const list = document.getElementById('quizzes-list');
    quizListSnapshot = list ? list.innerHTML : '';
    quizModalDirty = false;
    window.dispatchEvent(new CustomEvent('open-quizzes-modal'));
}

// Called by the modal's backdrop-click / X-button close paths (see modal.blade.php
// onCloseAttempt). "Selesai & Tutup" goes through closeQuizModal() instead, which
// handles the delete-confirmation flow separately.
window.quizzesModalCloseAttempt = function (proceed) {
    if (!quizModalDirty) {
        proceed();
        return;
    }

    Swal.fire({
        icon: 'warning',
        title: 'Buang perubahan?',
        text: 'Yakin ingin membuang perubahan pada kuis ini?',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Buang Perubahan',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (!result.isConfirmed) return;

        const list = document.getElementById('quizzes-list');
        if (list) {
            list.innerHTML = quizListSnapshot;
            window.Alpine?.initTree(list);
        }
        quizModalDirty = false;
        proceed();
    });
};

function closeQuizModal() {
    const list = document.getElementById('quizzes-list');
    const marked = list ? list.querySelectorAll('.quiz-item[data-soft-deleted="1"]') : [];

    if (marked.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: `Hapus ${marked.length} pertanyaan ini?`,
            text: 'Pertanyaan yang dihapus tidak bisa dikembalikan.',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                marked.forEach(item => item.remove());
                finalizeCloseQuizModal();
            } else {
                marked.forEach(item => {
                    item.classList.remove('hidden');
                    delete item.dataset.softDeleted;
                });
            }
        });
        return;
    }

    finalizeCloseQuizModal();
}

function finalizeCloseQuizModal() {
    quizModalDirty = false;
    window.dispatchEvent(new CustomEvent('close-quizzes-modal'));
}

// Trash icon = soft delete: hide the row and mark it, but don't touch the DOM yet.
// The actual removal (and its confirmation) happens in closeQuizModal() above, since
// that's the point the deletion becomes real for this editing session.
function softDeleteQuizItem(btn) {
    const item = btn.closest('.quiz-item');
    if (!item) return;
    item.dataset.softDeleted = '1';
    item.classList.add('hidden');
    quizModalDirty = true;
}

function addQuizField(quiz = null) {
    const list = document.getElementById('quizzes-list');
    if (!list) return;

    const index = list.children.length;

    const html = `
        <div class="quiz-item bg-white p-4 rounded-xl border border-gray-100 shadow-sm" x-data="{ locale: 'id' }">
            <div class="mb-3">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-sm font-semibold text-gray-700">Pertanyaan ${index + 1}</label>
                    <div class="flex items-center gap-1">
                        <button @click="locale = 'id'" :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded text-[10px] font-semibold transition-all" type="button">ID</button>
                        <button @click="locale = 'en'" :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded text-[10px] font-semibold transition-all" type="button">EN</button>
                        <button type="button" onclick="softDeleteQuizItem(this)" class="p-1 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
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
            <div class="mt-3">
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Penjelasan (opsional)</label>
                <div x-show="locale === 'en'">
                    <textarea name="quiz_explanation[${index}][en]" rows="2" placeholder="Why this answer is correct (EN)" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">${quiz ? (typeof quiz.explanation === 'object' ? (quiz.explanation?.en || '') : (quiz.explanation || '')) : ''}</textarea>
                </div>
                <div x-show="locale === 'id'">
                    <textarea name="quiz_explanation[${index}][id]" rows="2" placeholder="Kenapa jawaban ini benar (ID)" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">${quiz ? (typeof quiz.explanation === 'object' ? (quiz.explanation?.id || '') : (quiz.explanation || '')) : ''}</textarea>
                </div>
            </div>
        </div>
    `;
    list.insertAdjacentHTML('beforeend', html);
    window.injectTranslateButtons?.();
    quizModalDirty = true;
}

document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('quizzes-list');
    if (list) list.addEventListener('input', () => quizModalDirty = true);
});
</script>
