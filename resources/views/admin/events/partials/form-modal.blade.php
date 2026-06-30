{{-- Create / Edit Event Form Modal --}}
<x-modal name="event-form-modal" maxWidth="2xl" desktopLayout="drawer">
    {{-- Modal Header --}}
    <div class="mb-5 flex items-start justify-between gap-4 border-b border-gray-100 pb-3">
        <div>
            <h3 class="font-display text-charcoal text-xl font-bold" x-text="formTitle"></h3>
            <p class="mt-0.5 text-xs text-gray-500">Lengkapi detail event budaya desa di bawah ini.</p>
        </div>
        <button @click="$dispatch('close-event-form-modal')"
            class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 md:hidden">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Form --}}
    <form :action="formAction" method="POST" class="space-y-5">
        @csrf
        <template x-if="formMethod === 'PUT'">
            <input type="hidden" name="_method" value="PUT">
        </template>
        <input type="hidden" name="id" x-model="formFields.id">

        {{-- Locale Tabs --}}
        <div x-data="{ locale: 'id' }">
            <div class="sticky top-0 z-10 bg-white py-3 border-b border-gray-100 mb-4 flex gap-2">
                <button @click="locale = 'id'" :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all" type="button">Indonesia</button>
                <button @click="locale = 'en'" :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all" type="button">English</button>
            </div>

            {{-- Row 1: Nama Event & Kategori --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <div x-show="locale === 'en'">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-700">Name
                            (EN) <span class="text-red-500">*</span></label>
                        <input id="tour-form-name" type="text" name="name[en]" x-model="formFields['name[en]']"
                            placeholder="e.g. Penglipuran Bamboo Festival 2026"
                            class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1"
                            required>
                        @error('name.en')
                            <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div x-show="locale === 'id'">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-700">Nama
                            Event (ID) <span class="text-red-500">*</span></label>
                        <input type="text" name="name[id]" x-model="formFields['name[id]']"
                            placeholder="Contoh: Festival Bambu Penglipuran 2026"
                            class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1"
                            required>
                        @error('name.id')
                            <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-700">Kategori
                        <span class="text-red-500">*</span></label>
                    <select id="tour-form-category" name="category" x-model="formFields.category"
                        class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-1">
                        @foreach (['Upacara Adat', 'Festival', 'Workshop', 'Pameran', 'Pertunjukan Seni'] as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Row 2: Deskripsi --}}
            <div x-show="locale === 'en'">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-700">Description
                    (EN)</label>
                <textarea name="description[en]" rows="3" x-model="formFields['description[en]']"
                    placeholder="Describe the background and activities of this event..."
                    class="focus:border-primary focus:ring-primary/30 w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1"></textarea>
                @error('description.en')
                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <div x-show="locale === 'id'">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-700">Deskripsi
                    Event (ID)</label>
                <textarea name="description[id]" rows="3" x-model="formFields['description[id]']"
                    placeholder="Jelaskan latar belakang dan kegiatan dalam event ini..."
                    class="focus:border-primary focus:ring-primary/30 w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1"></textarea>
                @error('description.id')
                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                @enderror
            </div>

        {{-- Row 3: Waktu Mulai & Waktu Selesai --}}
        <div id="tour-form-dates" class="grid grid-cols-1 gap-4 rounded-2xl border border-gray-100 bg-gray-50/50 p-4 md:grid-cols-2">
            <div>
                <span class="text-primary mb-2 block text-xs font-bold uppercase tracking-wider">Pelaksanaan
                    Mulai</span>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] font-semibold uppercase text-gray-500">Tanggal <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="start_date" x-model="formFields.start_date"
                            class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-1"
                            required>
                        @error('start_date')
                            <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-semibold uppercase text-gray-500">Jam</label>
                        <input type="time" name="start_time" x-model="formFields.start_time"
                            class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-1">
                        @error('start_time')
                            <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div>
                <span class="text-primary mb-2 block text-xs font-bold uppercase tracking-wider">Pelaksanaan
                    Selesai</span>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] font-semibold uppercase text-gray-500">Tanggal <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="end_date" x-model="formFields.end_date"
                            class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-1"
                            required>
                        @error('end_date')
                            <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-semibold uppercase text-gray-500">Jam</label>
                        <input type="time" name="end_time" x-model="formFields.end_time"
                            class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-1">
                        @error('end_time')
                            <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Alpine client-side date warning --}}
            <div x-show="isDateInvalid"
                class="flex items-center gap-2 rounded-xl border border-red-100 bg-red-50 p-2.5 text-xs text-red-600 md:col-span-2"
                style="display: none;">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Peringatan: Tanggal & Waktu Selesai harus setelah Waktu Mulai!</span>
            </div>
        </div>

        {{-- Row 4: Lokasi Tempat --}}
        <div id="tour-form-location" class="space-y-3">
            <div x-show="locale === 'en'">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-700">Location
                    Name (EN) <span class="text-red-500">*</span></label>
                <input type="text" name="location_name[en]" x-model="formFields['location_name[en]']"
                    placeholder="e.g. Bale Banjar or Penataran Agung Temple"
                    class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1"
                    required>
                @error('location_name.en')
                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <div x-show="locale === 'id'">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-700">Lokasi
                    Tempat (ID) <span class="text-red-500">*</span></label>
                <input type="text" name="location_name[id]" x-model="formFields['location_name[id]']"
                    placeholder="Contoh: Bale Banjar atau Pura Penataran Agung"
                    class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1"
                    required>
                @error('location_name.id')
                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                @enderror
            </div>
            {{-- Map Selection --}}
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-700">Pilih Lokasi Peta <span class="text-[10px] font-normal text-gray-400">(Klik pada peta untuk menentukan koordinat)</span></label>
                <div id="form-location-map" class="relative h-64 w-full rounded-xl border border-gray-200 shadow-inner" style="z-index: 0;">
                    <x-map-style-fab size="sm" class="absolute bottom-3 right-3 z-1000" />
                </div>
                <input type="hidden" name="latitude" x-model="formFields.latitude">
                <input type="hidden" name="longitude" x-model="formFields.longitude">
                @error('latitude')
                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                @enderror
                @error('longitude')
                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                @enderror
                
                {{-- Coordinate Badge --}}
                <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                    <div>
                        <span x-show="formFields.latitude && formFields.longitude">
                            Koordinat Terpilih: <strong x-text="parseFloat(formFields.latitude).toFixed(6)"></strong>, <strong x-text="parseFloat(formFields.longitude).toFixed(6)"></strong>
                        </span>
                        <span x-show="!formFields.latitude || !formFields.longitude" class="text-amber-600 font-medium">
                            ⚠️ Belum ada lokasi peta yang dipilih
                        </span>
                    </div>
                    <button type="button" x-show="formFields.latitude && formFields.longitude" @click="formFields.latitude = ''; formFields.longitude = ''; if(formMarker) { formMap.removeLayer(formMarker); formMarker = null; }" class="text-red-500 hover:text-red-700 hover:underline">Hapus Lokasi</button>
                </div>
            </div>
        </div>{{-- /tour-form-location --}}
        </div>{{-- /x-data locale --}}

        {{-- Row 5: Harga & Kapasitas --}}
        <div class="grid grid-cols-1 gap-4 border-t border-gray-100 pt-2 md:grid-cols-2">
            <div class="flex flex-col justify-center">
                <div class="flex items-center gap-2.5 py-2">
                    <input type="checkbox" id="is_free_form" name="is_free" value="1"
                        x-model="formFields.is_free"
                        class="text-primary focus:ring-primary h-4 w-4 rounded border-gray-300">
                    <label for="is_free_form"
                        class="cursor-pointer text-xs font-bold uppercase tracking-wider text-gray-700">Event
                        Gratis</label>
                </div>
            </div>
            <div x-show="!formFields.is_free" x-transition class="space-y-1.5" style="display: none;">
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700">Harga Tiket
                    (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="price" x-model="formFields.price" placeholder="Contoh: 50000"
                    :required="!formFields.is_free"
                    class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1">
                @error('price')
                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-700">Kapasitas
                Maksimal (opsional)</label>
            <input type="number" name="max_participants" x-model="formFields.max_participants"
                placeholder="Maks. pengunjung"
                class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1">
            @error('max_participants')
                <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
            @enderror
        </div>

        {{-- Modal Footer Buttons --}}
        <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
            <button id="tour-form-cancel-btn" type="button" @click="$dispatch('close-event-form-modal')"
                class="rounded-xl border border-gray-200 px-5 py-2.5 text-xs font-bold text-gray-500 transition-all hover:bg-gray-50">
                Batal
            </button>
            <button type="submit" :disabled="isDateInvalid"
                :class="isDateInvalid ? 'opacity-50 cursor-not-allowed' : ''"
                class="bg-primary shadow-primary/20 hover:bg-primary-600 rounded-xl px-5 py-2.5 text-xs font-bold text-white shadow-lg transition-all active:scale-[0.98]">
                Simpan Event
            </button>
        </div>
    </form>
</x-modal>
