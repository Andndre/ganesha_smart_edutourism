{{-- TAB 2: LIST VIEW --}}
<div x-show="viewMode === 'list'" x-transition class="space-y-4" style="display: none;">
    <x-dashboard.table title="Event" :columns="['Nama event', 'Tanggal', 'Lokasi', 'Status', ['label' => 'Aksi', 'align' => 'right']]" :paginator="$events" id="events-table-wrapper" @click="handlePaginationClick($event)" x-bind:aria-busy="isLoadingList">
        <x-slot:toolbar>
            <x-dashboard.table-filters :action="route('admin.events')" id="tour-search-form" @submit.prevent="searchEvents" search-name="search" label="Cari event" placeholder="Nama event" :filtered="request()->filled('search') || (request()->filled('category') && request('category') !== 'Semua Kategori')" :reset-url="route('admin.events', ['view' => 'list'])">
                <div class="sm:w-48">
                    <label for="events-category" class="mb-1.5 block text-xs font-semibold text-gray-700">Kategori</label>
                    <select id="events-category" name="category" class="dashboard-table-input w-full">
                        <option value="Semua Kategori">Semua kategori</option>
                        @foreach (['Upacara Adat', 'Festival', 'Workshop', 'Pameran', 'Pertunjukan Seni'] as $cat)
                            <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </x-dashboard.table-filters>
        </x-slot:toolbar>
        <x-slot:beforeTable>
            <div x-show="isLoadingList" x-cloak class="space-y-3 border-b border-gray-200 p-5" role="status">
                <span class="sr-only">Memuat data event...</span>
                <div class="h-3 w-2/3 animate-pulse rounded bg-gray-200 motion-reduce:animate-none"></div>
                <div class="h-3 w-1/2 animate-pulse rounded bg-gray-200 motion-reduce:animate-none"></div>
            </div>
            <p x-show="listError" x-cloak x-text="listError" role="alert" class="border-b border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800"></p>
        </x-slot:beforeTable>

        @forelse ($events as $e)
            @php
                $now = now();
                if ($e->start_datetime > $now) {
                    $statusLabel = 'Mendatang';
                    $statusTone = 'neutral';
                } elseif ($e->end_datetime < $now) {
                    $statusLabel = 'Selesai';
                    $statusTone = 'neutral';
                } else {
                    $statusLabel = 'Berlangsung';
                    $statusTone = 'success';
                }
            @endphp
            <tr>
                <th scope="row" class="text-charcoal font-medium">
                    <div>
                        <p>{{ $e->name }}</p>
                        <x-dashboard.table-badge class="mt-1">{{ $e->getCategoryLabel() }}</x-dashboard.table-badge>
                    </div>
                </th>
                <td class="text-gray-500">
                    {{ $e->start_datetime->format('d M Y') }}
                    @if ($e->start_datetime->format('d M Y') !== $e->end_datetime->format('d M Y'))
                        - {{ $e->end_datetime->format('d M Y') }}
                    @endif
                </td>
                <td class="text-gray-500">{{ $e->location_name }}</td>
                <td>
                    <x-dashboard.table-badge :tone="$statusTone">{{ $statusLabel }}</x-dashboard.table-badge>
                </td>
                <td>
                    <div class="table-actions">
                        <x-dashboard.table-action type="button"
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
                            ]) }})">Ubah</x-dashboard.table-action>
                        <form method="POST" action="{{ route('admin.events.destroy', $e->id) }}"
                            class="delete-form inline"
                            data-confirm="{{ 'Apakah Anda yakin ingin menghapus event ini?' }}">
                            @csrf
                            @method('DELETE')
                            <x-dashboard.table-action type="submit"
                                variant="danger">Hapus</x-dashboard.table-action>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-dashboard.table-empty :colspan="5" :filtered="request()->filled('search') || (request()->filled('category') && request('category') !== 'Semua Kategori')" message="Belum ada data event." />
        @endforelse
    </x-dashboard.table>
</div>
