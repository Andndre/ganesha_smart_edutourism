{{-- Event Detail Modal --}}
<x-modal name="event-detail-modal" maxWidth="lg" desktopLayout="drawer">
    {{-- Modal Header --}}
    <div class="mb-4 flex items-start justify-between gap-4">
        <div>
            <span class="bg-primary/10 text-primary rounded-lg px-2.5 py-1 text-xs font-semibold"
                x-text="selectedEvent.category"></span>
            <h3 class="font-display text-charcoal mt-2 text-xl font-bold" x-text="selectedEvent.title"></h3>
        </div>
        <button @click="$dispatch('close-event-detail-modal')"
            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 md:hidden">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Modal Body --}}
    <div class="space-y-4 text-sm text-gray-600">
        <div class="flex items-start gap-3">
            <div class="shrink-0 rounded-lg bg-gray-50 p-2 text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Waktu Pelaksanaan</p>
                <p class="text-charcoal mt-0.5 font-medium" x-text="selectedEvent.start"></p>
                <template x-if="selectedEvent.end">
                    <p class="text-charcoal mt-0.5 font-medium"><span
                            class="font-normal text-gray-400">sampai</span> <span
                            x-text="selectedEvent.end"></span></p>
                </template>
            </div>
        </div>

        <div class="flex items-start gap-3">
            <div class="shrink-0 rounded-lg bg-gray-50 p-2 text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Lokasi Tempat</p>
                <p class="text-charcoal mt-0.5 font-medium" x-text="selectedEvent.location"></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex items-start gap-3">
                <div class="shrink-0 rounded-lg bg-gray-50 p-2 text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Harga Tiket</p>
                    <p class="text-charcoal mt-0.5 font-medium" x-text="selectedEvent.price"></p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="shrink-0 rounded-lg bg-gray-50 p-2 text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Maks. Peserta</p>
                    <p class="text-charcoal mt-0.5 font-medium" x-text="selectedEvent.max_participants"></p>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-2">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Deskripsi Event</p>
            <p class="mt-1 rounded-xl bg-gray-50 p-3 text-sm leading-relaxed text-gray-600"
                x-text="selectedEvent.description || 'Belum ada deskripsi.'"></p>
        </div>
    </div>

    {{-- Modal Footer --}}
    <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
        <button type="button" @click="$dispatch('close-event-detail-modal'); openEdit(selectedEvent.raw)"
            class="border-primary/20 bg-primary/5 text-primary hover:bg-primary inline-flex items-center justify-center gap-1.5 rounded-xl border px-4 py-2.5 text-xs font-bold transition-all hover:text-white">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Ubah Event
        </button>

        <form :action="selectedEvent.delete_action" method="POST" class="delete-form inline"
            data-confirm="{{ 'Apakah Anda yakin ingin menghapus event ini?' }}">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="border-warning/20 bg-warning/5 text-warning hover:bg-warning flex items-center gap-1.5 rounded-xl border px-4 py-2.5 text-xs font-bold transition-all hover:text-white">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus
            </button>
        </form>
    </div>
</x-modal>
