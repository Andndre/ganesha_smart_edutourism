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

    <x-modal name="quizzes-modal" maxWidth="2xl" desktopLayout="drawer">
        <div class="mb-4">
            <h3 class="font-display text-charcoal text-lg font-bold">Kelola Kuis Edutourism</h3>
            <p class="mt-1 text-xs text-gray-500">Soal-soal ini akan muncul saat turis tiba di lokasi ini.</p>
        </div>

        <div class="max-h-[50vh] space-y-6 overflow-y-auto p-1" id="quizzes-list">
            <!-- Quizzes will be appended here -->
        </div>

        <div class="mt-6 space-y-3 border-t border-gray-100 pt-4">
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
    </x-modal>
</div>
