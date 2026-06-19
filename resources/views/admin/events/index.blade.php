@extends('layouts.dashboard')

@section('title', 'Event & Kalender')

@section('push_scripts')
    {{-- We use push('styles') to import stylesheets or JS files if needed --}}
@endsection

@push('styles')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <style>
        /* FullCalendar Premium Design Overrides */
        .fc {
            --fc-border-color: #f3f4f6;
            --fc-button-bg-color: #ffffff;
            --fc-button-border-color: #e5e7eb;
            --fc-button-text-color: #4b5563;
            --fc-button-active-bg-color: #1E5128;
            --fc-button-active-border-color: #1E5128;
            --fc-button-hover-bg-color: #f9fafb;
            --fc-button-hover-border-color: #d1d5db;
            --fc-today-bg-color: rgba(30, 81, 40, 0.04);
            --fc-event-border-color: transparent;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .fc .fc-toolbar-title {
            font-size: 1.125rem !important;
            font-weight: 700;
            color: #191A19;
        }

        .fc .fc-button {
            padding: 0.5rem 0.875rem !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            border-radius: 0.75rem !important;
            text-transform: capitalize !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            transition: all 0.2s ease !important;
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background-color: #1E5128 !important;
            border-color: #1E5128 !important;
            color: #ffffff !important;
        }

        .fc .fc-button:focus {
            box-shadow: 0 0 0 3px rgba(30, 81, 40, 0.2) !important;
        }

        .fc .fc-toolbar-chunk {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .fc .fc-button-group {
            gap: 0.375rem;
        }

        .fc .fc-button-group>.fc-button {
            margin: 0 !important;
            border-radius: 0.75rem !important;
        }

        .fc .fc-col-header-cell-cushion {
            padding: 0.75rem 0.25rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            color: #9ca3af !important;
        }

        .fc .fc-daygrid-day-number {
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            color: #4b5563 !important;
            padding: 0.5rem !important;
        }

        .fc-event {
            cursor: pointer !important;
            border-radius: 0.5rem !important;
            padding: 0.125rem 0.375rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
            transition: transform 0.15s ease, opacity 0.15s ease !important;
        }

        .fc-event:hover {
            transform: translateY(-1px);
            opacity: 0.9;
        }

        .fc-event-title {
            font-size: 0.75rem !important;
            font-weight: 700 !important;
        }

        .fc .fc-timegrid-axis-cushion {
            font-size: 0.725rem !important;
            font-weight: 600 !important;
        }

        @keyframes loading-bar {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(300%);
            }
        }

        .animate-loading-bar {
            animation: loading-bar 1.2s infinite linear;
        }
    </style>
@endpush

@section('content')

    <div x-data="adminEvents"
        @open-event-modal.window="selectedEvent = $event.detail; $dispatch('open-event-detail-modal')"
        @open-create-modal.window="openCreate($event.detail.date, $event.detail.time)"
        @open-edit-modal.window="openEdit($event.detail)" class="space-y-6">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div id="tour-header">
                <h1 class="font-display text-charcoal text-2xl font-bold">Event & Kalender Budaya</h1>
                <p class="mt-0.5 text-sm text-gray-500">Jadwalkan dan kelola upacara adat, festival, dan event desa.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                {{-- Toggle Button View --}}
                <div id="tour-tabs" class="inline-flex shrink-0 rounded-xl border border-gray-200 bg-white p-1 shadow-sm">
                    <button id="tour-tab-calendar" type="button" @click="viewMode = 'calendar'"
                        :class="viewMode === 'calendar' ? 'bg-primary text-white shadow-sm' :
                            'text-gray-500 hover:text-charcoal'"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Kalender
                    </button>
                    <button id="tour-tab-list" type="button" @click="viewMode = 'list'"
                        :class="viewMode === 'list' ? 'bg-primary text-white shadow-sm' :
                            'text-gray-500 hover:text-charcoal'"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Daftar
                    </button>
                </div>

                {{-- Interactive Tour Trigger Button --}}
                <button id="tour-trigger-btn" onclick="startTutorial()"
                    class="hover:bg-gray-100 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all active:scale-[0.98]"
                    title="Panduan Interaktif">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>

                <button id="tour-add-btn" type="button" @click="openCreate()"
                    class="bg-primary shadow-primary/20 hover:bg-primary-600 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Event
                </button>
            </div>
        </div>

        {{-- Upcoming events (timeline stats) --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @php
                $upcoming = [
                    ['label' => 'Mendatang', 'count' => $upcomingCount, 'color' => 'text-primary bg-primary/10'],
                    ['label' => 'Bulan Ini', 'count' => $thisMonthCount, 'color' => 'text-secondary bg-secondary/10'],
                    ['label' => 'Sudah Lewat', 'count' => $pastCount, 'color' => 'text-gray-400 bg-gray-100'],
                ];
            @endphp
            @foreach ($upcoming as $u)
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">{{ $u['label'] }}</p>
                        <span
                            class="{{ $u['color'] }} rounded-full px-2.5 py-0.5 text-xs font-bold">{{ $u['count'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- TAB 1: CALENDAR VIEW --}}
        <div x-show="viewMode === 'calendar'" x-transition
            class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div id="calendar"></div>
        </div>

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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari event..."
                        class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm focus:outline-none focus:ring-1">
                </div>
                <select name="category" @change="searchEvents"
                    class="focus:border-primary rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm focus:outline-none">
                    <option value="Semua Kategori">Semua Kategori</option>
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
                                                data-confirm="Apakah Anda yakin ingin menghapus event ini?">
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
                    data-confirm="Apakah Anda yakin ingin menghapus event ini?">
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

                {{-- Row 1: Nama Event & Kategori --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-700">Nama
                            Event <span class="text-red-500">*</span></label>
                        <input id="tour-form-name" type="text" name="name" x-model="formFields.name"
                            placeholder="Contoh: Festival Bambu Penglipuran 2026"
                            class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1"
                            required>
                        @error('name')
                            <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                        @enderror
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
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-700">Deskripsi
                        Event</label>
                    <textarea name="description" rows="3" x-model="formFields.description"
                        placeholder="Jelaskan latar belakang dan kegiatan dalam event ini..."
                        class="focus:border-primary focus:ring-primary/30 w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1"></textarea>
                    @error('description')
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
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-700">Lokasi
                            Tempat <span class="text-red-500">*</span></label>
                        <input type="text" name="location_name" x-model="formFields.location_name"
                            placeholder="Contoh: Bale Banjar atau Pura Penataran Agung"
                            class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1"
                            required>
                        @error('location_name')
                            <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Latitude
                                (opsional)</label>
                            <input type="number" step="any" name="latitude" x-model="formFields.latitude"
                                placeholder="Contoh: -8.4312"
                                class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1">
                            @error('latitude')
                                <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Longitude
                                (opsional)</label>
                            <input type="number" step="any" name="longitude" x-model="formFields.longitude"
                                placeholder="Contoh: 115.3521"
                                class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-1">
                            @error('longitude')
                                <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

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

    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminEvents', () => ({
                viewMode: 'calendar',
                showModal: false,
                selectedEvent: {},

                showFormModal: false,
                formAction: '',
                formMethod: 'POST',
                formTitle: 'Tambah Event Baru',

                formFields: {
                    id: @json(old('id', '')),
                    name: @json(old('name', '')),
                    description: @json(old('description', '')),
                    category: @json(old('category', 'Upacara Adat')),
                    start_date: @json(old('start_date', '')),
                    start_time: @json(old('start_time', '')),
                    end_date: @json(old('end_date', '')),
                    end_time: @json(old('end_time', '')),
                    location_name: @json(old('location_name', '')),
                    latitude: @json(old('latitude', '')),
                    longitude: @json(old('longitude', '')),
                    is_free: {{ old('is_free') !== null || !$errors->any() ? 'true' : 'false' }},
                    price: @json(old('price', '')),
                    max_participants: @json(old('max_participants', ''))
                },

                openCreate(dateStr = '', timeStr = '') {
                    this.formTitle = 'Tambah Event Baru';
                    this.formAction = '{{ route('admin.events.store') }}';
                    this.formMethod = 'POST';

                    this.formFields = {
                        id: '',
                        name: '',
                        description: '',
                        category: 'Upacara Adat',
                        start_date: dateStr || new Date().toISOString().split('T')[0],
                        start_time: timeStr || '10:00',
                        end_date: dateStr || new Date().toISOString().split('T')[0],
                        end_time: timeStr ? this.addHours(timeStr, 2) : '12:00',
                        location_name: '',
                        latitude: '',
                        longitude: '',
                        is_free: true,
                        price: '',
                        max_participants: ''
                    };

                    window.dispatchEvent(new CustomEvent('open-event-form-modal'));
                },

                openEdit(eventData) {
                    this.formTitle = 'Ubah Event';
                    this.formAction = '{{ route('admin.events.update', 'EVENT_ID') }}'.replace(
                        'EVENT_ID', eventData.id);
                    this.formMethod = 'PUT';

                    this.formFields = {
                        id: eventData.id,
                        name: eventData.name,
                        description: eventData.description || '',
                        category: eventData.category,
                        start_date: eventData.start_date,
                        start_time: eventData.start_time || '',
                        end_date: eventData.end_date,
                        end_time: eventData.end_time || '',
                        location_name: eventData.location_name,
                        latitude: eventData.latitude || '',
                        longitude: eventData.longitude || '',
                        is_free: !!eventData.is_free,
                        price: eventData.price || '',
                        max_participants: eventData.max_participants || ''
                    };

                    window.dispatchEvent(new CustomEvent('open-event-form-modal'));
                },

                addHours(timeStr, hours) {
                    if (!timeStr) return '';
                    const parts = timeStr.split(':');
                    const h = parseInt(parts[0], 10);
                    const m = parts[1] || '00';
                    const newH = (h + hours) % 24;
                    return `${String(newH).padStart(2, '0')}:${m}`;
                },

                get isDateInvalid() {
                    if (!this.formFields.start_date || !this.formFields.end_date) return false;
                    const start = new Date(this.formFields.start_date + ' ' + (this.formFields
                        .start_time || '00:00'));
                    const end = new Date(this.formFields.end_date + ' ' + (this.formFields
                        .end_time || '23:59'));
                    return end < start;
                },

                isLoadingList: false,

                searchEvents() {
                    const form = document.querySelector('#tour-search-form');
                    const formData = new FormData(form);
                    const searchParams = new URLSearchParams(formData).toString();
                    const url = '{{ route('admin.events') }}?' + searchParams;
                    this.fetchEventsFromUrl(url);
                },

                handlePaginationClick(event) {
                    const link = event.target.closest('a');
                    if (link && link.href) {
                        event.preventDefault();
                        this.fetchEventsFromUrl(link.href);
                    }
                },

                fetchEventsFromUrl(url, shouldPushState = true) {
                    this.isLoadingList = true;
                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.text();
                        })
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newTable = doc.querySelector('#events-table-wrapper');
                            const currentTable = document.querySelector('#events-table-wrapper');
                            if (newTable && currentTable) {
                                currentTable.innerHTML = newTable.innerHTML;
                            }

                            // Sync form input values
                            const newSearch = doc.querySelector('input[name="search"]');
                            const currentSearch = document.querySelector('input[name="search"]');
                            if (newSearch && currentSearch) {
                                currentSearch.value = newSearch.value;
                            }
                            const newCategory = doc.querySelector('select[name="category"]');
                            const currentCategory = document.querySelector(
                                'select[name="category"]');
                            if (newCategory && currentCategory) {
                                currentCategory.value = newCategory.value;
                            }

                            if (shouldPushState) {
                                window.history.pushState({}, '', url);
                            }
                            this.isLoadingList = false;
                        })
                        .catch(err => {
                            console.error('AJAX Error:', err);
                            this.isLoadingList = false;
                        });
                },

                init() {
                    @if ($errors->any())
                        this.formTitle =
                            '{{ old('_method') === 'PUT' ? 'Ubah Event' : 'Tambah Event Baru' }}';
                        this.formAction =
                            '{{ old('_method') === 'PUT' ? route('admin.events.update', old('id') ?: 0) : route('admin.events.store') }}';
                        this.formMethod = '{{ old('_method', 'POST') }}';
                        this.showFormModal = true;
                    @elseif (isset($openCreateOnLoad) && $openCreateOnLoad)
                        this.openCreate();
                    @elseif (isset($editEventRaw))
                        this.openEdit(@json($editEventRaw));
                    @endif

                    // Check if URL parameters require switching to list view
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('search') || urlParams.has('category') || urlParams.has('page')) {
                        this.viewMode = 'list';
                    }

                    // Handle back/forward navigation
                    window.addEventListener('popstate', () => {
                        this.fetchEventsFromUrl(window.location.href, false);
                    });
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendarEvents = @json($calendarEvents);

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                displayEventTime: false,
                allDayText: 'Sehari',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    week: 'Minggu',
                    list: 'Agenda'
                },
                events: calendarEvents,
                selectable: true,
                select: function(info) {
                    const datePart = info.startStr.split('T')[0];
                    const timePart = info.startStr.includes('T') ? info.startStr.split('T')[1]
                        .substring(0, 5) : '10:00';

                    window.dispatchEvent(new CustomEvent('open-create-modal', {
                        detail: {
                            date: datePart,
                            time: timePart
                        }
                    }));
                    calendar.unselect();
                },
                eventClick: function(info) {
                    const eventObj = info.event;
                    const props = eventObj.extendedProps;

                    const options = {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    };
                    const startStr = new Date(eventObj.start).toLocaleDateString('id-ID', options);
                    const endStr = eventObj.end ? new Date(eventObj.end).toLocaleDateString('id-ID',
                        options) : null;

                    window.dispatchEvent(new CustomEvent('open-event-modal', {
                        detail: {
                            title: eventObj.title,
                            category: props.category,
                            start: startStr,
                            end: endStr,
                            location: props.location,
                            description: props.description,
                            price: props.price,
                            max_participants: props.max_participants,
                            edit_url: props.edit_url,
                            delete_action: props.delete_action,
                            raw: props.raw
                        }
                    }));
                }
            });

            calendar.render();

            // Fix calendar size calculation when Alpine switches tab dynamically
            window.addEventListener('click', function() {
                setTimeout(() => {
                    calendar.updateSize();
                }, 50);
            });
        });

        // --- Driver.js Panduan Interaktif ---
        function startTutorial() {
            const driver = window.driver.js.driver;

            // Helper to make element clickable and advance tour on click
            const makeStepInteractive = (selector, delay = 100) => {
                return {
                    onHighlighted: (element, step, { driver }) => {
                        const clickHandler = () => {
                            setTimeout(() => {
                                driver.moveNext();
                            }, delay);
                        };
                        element.addEventListener('click', clickHandler, { once: true });
                        element._tourClickHandler = clickHandler;
                    },
                    onDeselected: (element) => {
                        if (element && element._tourClickHandler) {
                            element.removeEventListener('click', element._tourClickHandler);
                            delete element._tourClickHandler;
                        }
                    },
                    onNextClick: (element, step, { driver }) => {
                        element.click();
                    }
                };
            };

            const steps = [
                {
                    element: '#tour-header',
                    popover: {
                        title: '📅 Event & Kalender Budaya',
                        description: 'Selamat datang! Halaman ini digunakan untuk mengelola semua kegiatan adat, festival, dan pameran seni di desa wisata.',
                        side: 'bottom',
                        align: 'start'
                    }
                },
                {
                    element: '#tour-tabs',
                    popover: {
                        title: '🔄 Mode Tampilan',
                        description: 'Anda dapat beralih antara tampilan Kalender Interaktif dan tampilan Daftar Tabel.',
                        side: 'bottom',
                        align: 'end'
                    }
                },
                {
                    element: '#tour-tab-list',
                    ...makeStepInteractive('#tour-tab-list', 300),
                    popover: {
                        title: '📋 Tampilan Daftar',
                        description: 'Silakan klik tab <strong>Daftar</strong> untuk beralih ke tampilan list tabel.',
                        side: 'bottom',
                        align: 'end'
                    }
                },
                {
                    element: '#tour-search-form',
                    onHighlightStarted: (element) => {
                        // Pastikan tab Daftar aktif
                        const tabList = document.getElementById('tour-tab-list');
                        if (tabList) tabList.click();
                    },
                    popover: {
                        title: '🔍 Pencarian & Filter',
                        description: 'Di tampilan daftar ini, Anda dapat mencari event secara cepat atau menyaringnya berdasarkan kategori tertentu.',
                        side: 'bottom',
                        align: 'start'
                    }
                },
                {
                    element: '#tour-tab-calendar',
                    ...makeStepInteractive('#tour-tab-calendar', 300),
                    popover: {
                        title: '📅 Tampilan Kalender',
                        description: 'Silakan klik tab <strong>Kalender</strong> untuk kembali ke tampilan kalender utama.',
                        side: 'bottom',
                        align: 'end'
                    }
                },
                {
                    element: '#calendar',
                    onHighlightStarted: (element) => {
                        // Pastikan tab Kalender aktif
                        const tabCalendar = document.getElementById('tour-tab-calendar');
                        if (tabCalendar) tabCalendar.click();
                    },
                    popover: {
                        title: '📆 Kalender Interaktif',
                        description: 'Ini adalah Kalender Event desa. Anda dapat melihat event yang terjadwal. Klik pada tanggal kosong untuk membuat event baru, atau klik event yang ada untuk detailnya.',
                        side: 'top',
                        align: 'start'
                    }
                },
                {
                    element: '#tour-add-btn',
                    ...makeStepInteractive('#tour-add-btn', 300),
                    onHighlightStarted: (element) => {
                        // Pastikan modal ditutup ketika di step ini
                        window.dispatchEvent(new CustomEvent('close-event-form-modal'));
                    },
                    popover: {
                        title: '➕ Tambah Event',
                        description: 'Klik tombol <strong>Tambah Event</strong> untuk membuka formulir pembuatan event baru.',
                        side: 'bottom',
                        align: 'end'
                    }
                },
                {
                    element: '#tour-form-name',
                    onHighlightStarted: (element) => {
                        // Pastikan modal dibuka ketika masuk ke step ini
                        window.dispatchEvent(new CustomEvent('open-event-form-modal'));
                    },
                    popover: {
                        title: '📝 Nama & Kategori Event',
                        description: 'Masukkan nama event dan pilih kategori event yang sesuai.',
                        side: 'left',
                        align: 'start'
                    }
                },
                {
                    element: '#tour-form-dates',
                    popover: {
                        title: '⏱️ Tanggal & Waktu',
                        description: 'Tentukan tanggal mulai dan selesai. Jika tanggal selesai sebelum tanggal mulai, sistem akan menampilkan peringatan.',
                        side: 'left',
                        align: 'start'
                    }
                },
                {
                    element: '#tour-form-location',
                    popover: {
                        title: '📍 Lokasi Kegiatan',
                        description: 'Tuliskan nama tempat pelaksanaan dan koordinat GPS (latitude/longitude) jika ingin ditampilkan pada peta.',
                        side: 'left',
                        align: 'start'
                    }
                },
                {
                    element: '#tour-form-cancel-btn',
                    ...makeStepInteractive('#tour-form-cancel-btn', 300),
                    popover: {
                        title: '❌ Selesai',
                        description: 'Klik tombol <strong>Batal</strong> untuk menutup formulir dan menyelesaikan panduan ini.',
                        side: 'top',
                        align: 'end'
                    }
                }
            ];

            const driverObj = driver({
                showProgress: true,
                allowClose: true,
                steps: steps,
                popoverClass: 'driverjs-theme'
            });

            driverObj.drive();
        }

        // Auto-run for first-time visitors
        document.addEventListener('DOMContentLoaded', () => {
            const tourCompleted = localStorage.getItem('admin_events_tour_completed');
            if (!tourCompleted) {
                setTimeout(() => {
                    startTutorial();
                    localStorage.setItem('admin_events_tour_completed', 'true');
                }, 1000);
            }
        });
    </script>
@endpush
