{{-- TAB 2: LIST VIEW --}}
<div x-show="viewMode === 'list'" x-transition class="space-y-4" style="display: none;">
    {{-- Search + Filter --}}
    <form id="tour-search-form" @submit.prevent="searchEvents" method="GET" action="{{ route('admin.events') }}"
        class="flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ 'Cari event...' }}"
                class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm focus:outline-none focus:ring-1">
        </div>
        <select name="category" @change="searchEvents"
            class="focus:border-primary rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm focus:outline-none">
            <option value="Semua Kategori">{{ 'Semua Kategori' }}</option>
            @foreach (['Upacara Adat', 'Festival', 'Workshop', 'Pameran', 'Pertunjukan Seni'] as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                    {{ $cat }}</option>
            @endforeach
        </select>
    </form>

    {{-- Table --}}
    <div id="events-table-wrapper"
        class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm"
        @click="handlePaginationClick($event)"
        :class="isLoadingList ? 'opacity-60 pointer-events-none transition-opacity duration-200' :
            'transition-opacity duration-200'">

        {{-- Dynamic Loading Bar --}}
        <div x-show="isLoadingList" class="bg-primary/10 absolute left-0 right-0 top-0 z-10 h-1 overflow-hidden">
            <div class="bg-primary animate-loading-bar absolute left-0 top-0 h-full w-1/3 rounded-full"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th
                            class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Nama Event</th>
                        <th
                            class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Tanggal</th>
                        <th
                            class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Lokasi</th>
                        <th
                            class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Status</th>
                        <th
                            class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($events as $e)
                        @php
                            $now = now();
                            if ($e->start_datetime > $now) {
                                $statusLabel = 'Mendatang';
                                $statusClass = 'bg-primary/10 text-primary';
                            } elseif ($e->end_datetime < $now) {
                                $statusLabel = 'Selesai';
                                $statusClass = 'bg-gray-100 text-gray-400';
                            } else {
                                $statusLabel = 'Berlangsung';
                                $statusClass = 'bg-secondary/10 text-secondary-800';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="text-charcoal px-5 py-4 font-medium">
                                <div>
                                    <p>{{ $e->name }}</p>
                                    <span
                                        class="bg-primary/8 text-primary mt-0.5 inline-block rounded px-1.5 py-0.5 text-[10px] font-semibold">{{ $e->getCategoryLabel() }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-500">
                                {{ $e->start_datetime->format('d M Y') }}
                                @if ($e->start_datetime->format('d M Y') !== $e->end_datetime->format('d M Y'))
                                    - {{ $e->end_datetime->format('d M Y') }}
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-500">{{ $e->location_name }}</td>
                            <td class="px-5 py-4">
                                <span
                                    class="{{ $statusClass }} rounded-full px-2.5 py-0.5 text-xs font-semibold">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                        @click="openEdit({{ json_encode([
                                            'id' => $e->id,
                                            'name' => $e->name,
                                            'description' => $e->description,
                                            'category' => $e->getCategoryLabel(),
                                            'start_date' => $e->start_datetime->format('Y-m-d'),
                                            'start_time' => $e->start_datetime->format('H:i'),
                                            'end_date' => $e->end_datetime->format('Y-m-d'),
                                            'end_time' => $e->end_datetime->format('H:i'),
                                            'location_name' => $e->location_name,
                                            'latitude' => $e->mapLocation->latitude ?? '',
                                            'longitude' => $e->mapLocation->longitude ?? '',
                                            'is_free' => $e->is_free,
                                            'price' => $e->price,
                                            'max_participants' => $e->max_participants,
                                        ]) }})"
                                        class="hover:bg-primary/10 hover:text-primary rounded-lg p-1.5 text-gray-400 transition-colors"
                                        title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.events.destroy', $e->id) }}"
                                        class="delete-form inline"
                                        data-confirm="{{ 'Apakah Anda yakin ingin menghapus event ini?' }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="hover:bg-warning/10 hover:text-warning rounded-lg p-1.5 text-gray-400 transition-colors"
                                            title="Hapus">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-400">Belum ada data event.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($events->hasPages())
            <div class="border-t border-gray-100 px-5 py-3.5">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>
