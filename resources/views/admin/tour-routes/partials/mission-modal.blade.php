<x-modal name="missions-modal" maxWidth="2xl" desktopLayout="drawer" onCloseAttempt="missionsModalCloseAttempt">
    <div class="mb-4">
        <h3 class="font-display text-charcoal text-lg font-bold">Kelola Misi Gamifikasi</h3>
        <p class="mt-1 text-xs text-gray-500">Misi ini muncul saat turis tiba di titik ini. Titik yang punya misi tidak menampilkan alur kuis.</p>
    </div>

    <div class="max-h-[55vh] space-y-6 overflow-y-auto p-1" id="missions-list"></div>

    <x-slot:footer>
        <div class="space-y-3">
            <button type="button" onclick="addMissionField()"
                class="hover:border-primary hover:text-primary flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 py-3 text-sm font-semibold text-gray-500 transition-colors hover:bg-green-50">
                + Tambah Misi
            </button>
            <button type="button" onclick="closeMissionModal()"
                class="bg-primary hover:bg-primary-600 w-full rounded-xl py-3 text-sm font-semibold text-white shadow-lg transition-all">Selesai &amp; Tutup</button>
        </div>
    </x-slot:footer>
</x-modal>
