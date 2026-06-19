<div class="border-t border-gray-100 pt-4">
    <label class="mb-3 flex cursor-pointer items-center space-x-2">
        <input type="checkbox" id="has_story" name="has_story" value="1"
            class="text-primary focus:ring-primary h-4 w-4 rounded border-gray-300"
            onchange="toggleStories(this)">
        <span class="text-sm font-semibold text-gray-700">Tambahkan Kisah Budaya (Storytelling)?</span>
    </label>

    <button type="button" id="btn-manage-stories" onclick="openStoryModal()"
        class="border-primary text-primary hover:bg-primary/5 hidden w-full items-center justify-center gap-2 rounded-xl border-2 py-2.5 text-sm font-semibold transition-colors">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
            </path>
        </svg>
        Kelola Kisah Budaya
    </button>

    <x-modal name="stories-modal" maxWidth="2xl" desktopLayout="drawer">
        <div class="mb-4">
            <h3 class="font-display text-charcoal text-lg font-bold">Kelola Kisah Budaya</h3>
            <p class="mt-1 text-xs text-gray-500">Kelola dan urutkan informasi sejarah, filosofi, atau nilai luhur
                terkait objek ini.</p>
        </div>

        <!-- Tabs navigation inside modal -->
        <div class="-mx-6 mb-4 flex border-b border-gray-100 bg-gray-50 px-2 py-1">
            <button type="button" id="tab-btn-history" onclick="switchStoryTab('history')"
                class="story-tab-btn border-primary text-primary border-b-2 px-4 py-2.5 text-xs font-bold transition-all focus:outline-none">
                Sejarah
            </button>
            <button type="button" id="tab-btn-philosophy" onclick="switchStoryTab('philosophy')"
                class="story-tab-btn hover:text-charcoal border-b-2 border-transparent px-4 py-2.5 text-xs font-bold text-gray-500 transition-all focus:outline-none">
                Filosofi
            </button>
            <button type="button" id="tab-btn-value" onclick="switchStoryTab('value')"
                class="story-tab-btn hover:text-charcoal border-b-2 border-transparent px-4 py-2.5 text-xs font-bold text-gray-500 transition-all focus:outline-none">
                Nilai Luhur
            </button>
        </div>

        <div class="max-h-[50vh] overflow-y-auto p-1">
            <!-- History Stories -->
            <div id="stories-list-history" class="story-tab-content space-y-4">
                <!-- History stories will go here -->
            </div>

            <!-- Philosophy Stories -->
            <div id="stories-list-philosophy" class="story-tab-content hidden space-y-4">
                <!-- Philosophy stories will go here -->
            </div>

            <!-- Value Stories -->
            <div id="stories-list-value" class="story-tab-content hidden space-y-4">
                <!-- Value stories will go here -->
            </div>
        </div>

        <div class="mt-6 space-y-3 border-t border-gray-100 pt-4">
            <button type="button" onclick="addStoryField()"
                class="hover:border-primary hover:text-primary flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 py-3 text-sm font-semibold text-gray-500 transition-colors hover:bg-green-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                    </path>
                </svg>
                Tambah Kisah Budaya
            </button>
            <button type="button" onclick="closeStoryModal()"
                class="bg-primary hover:bg-primary-600 shadow-primary/20 w-full rounded-xl py-3 text-sm font-semibold text-white shadow-lg transition-all">Selesai
                & Tutup</button>
        </div>
    </x-modal>
</div>
