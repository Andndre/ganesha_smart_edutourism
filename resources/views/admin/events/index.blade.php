@extends('layouts.dashboard')

@section('title', 'Event & Kalender')

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
        
        .fc .fc-button-group > .fc-button {
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
    </style>
@endpush

@section('content')

<div x-data="{ 
    viewMode: 'calendar', 
    showModal: false,
    selectedEvent: {} 
}" @open-event-modal.window="selectedEvent = $event.detail; showModal = true" class="space-y-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold text-charcoal">Event & Kalender Budaya</h1>
            <p class="mt-0.5 text-sm text-gray-500">Jadwalkan dan kelola upacara adat, festival, dan event desa.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            {{-- Toggle Button View --}}
            <div class="inline-flex rounded-xl border border-gray-200 bg-white p-1 shadow-sm shrink-0">
                <button type="button" @click="viewMode = 'calendar'" :class="viewMode === 'calendar' ? 'bg-primary text-white shadow-sm' : 'text-gray-500 hover:text-charcoal'" class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-all">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Kalender
                </button>
                <button type="button" @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-primary text-white shadow-sm' : 'text-gray-500 hover:text-charcoal'" class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-all">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    Daftar
                </button>
            </div>

            <a href="{{ route('admin.events.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Event
            </a>
        </div>
    </div>

    {{-- Upcoming events (timeline stats) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @php
            $upcoming = [
                ['label' => 'Mendatang',    'count' => $upcomingCount, 'color' => 'text-primary bg-primary/10'],
                ['label' => 'Bulan Ini',    'count' => $thisMonthCount, 'color' => 'text-secondary bg-secondary/10'],
                ['label' => 'Sudah Lewat',  'count' => $pastCount, 'color' => 'text-gray-400 bg-gray-100'],
            ];
        @endphp
        @foreach ($upcoming as $u)
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-500">{{ $u['label'] }}</p>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $u['color'] }}">{{ $u['count'] }}</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- TAB 1: CALENDAR VIEW --}}
    <div x-show="viewMode === 'calendar'" x-transition class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <div id="calendar"></div>
    </div>

    {{-- TAB 2: LIST VIEW --}}
    <div x-show="viewMode === 'list'" x-transition class="space-y-4" style="display: none;">
        {{-- Search + Filter --}}
        <form method="GET" action="{{ route('admin.events') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari event..." class="w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 bg-white">
            </div>
            <select name="category" onchange="this.form.submit()" class="rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-primary focus:outline-none bg-white">
                <option value="Semua Kategori">Semua Kategori</option>
                @foreach(['Upacara Adat', 'Festival', 'Workshop', 'Pameran', 'Pertunjukan Seni'] as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </form>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nama Event</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Tanggal</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Lokasi</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
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
                                <td class="px-5 py-4 font-medium text-charcoal">
                                    <div>
                                        <p>{{ $e->name }}</p>
                                        <span class="inline-block mt-0.5 rounded bg-primary/8 px-1.5 py-0.5 text-[10px] font-semibold text-primary">{{ $e->getCategoryLabel() }}</span>
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
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.events.edit', $e->id) }}" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-primary/10 hover:text-primary" title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.events.destroy', $e->id) }}" class="delete-form inline" data-confirm="Apakah Anda yakin ingin menghapus event ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-warning/10 hover:text-warning" title="Hapus">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-gray-400">Belum ada data event.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($events->hasPages())
                <div class="border-t border-gray-100 px-5 py-3.5">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Event Detail Modal --}}
    <div x-show="showModal" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-charcoal/60 backdrop-blur-sm px-4" 
        style="display: none;" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl relative" @click.away="showModal = false">
            {{-- Modal Header --}}
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <span class="rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary" x-text="selectedEvent.category"></span>
                    <h3 class="font-display text-xl font-bold text-charcoal mt-2" x-text="selectedEvent.title"></h3>
                </div>
                <button @click="showModal = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="space-y-4 text-sm text-gray-600">
                <div class="flex items-start gap-3">
                    <div class="rounded-lg bg-gray-50 p-2 text-gray-400 shrink-0">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Waktu Pelaksanaan</p>
                        <p class="mt-0.5 text-charcoal font-medium" x-text="selectedEvent.start"></p>
                        <template x-if="selectedEvent.end">
                            <p class="mt-0.5 text-gray-400 text-xs">sampai <span class="text-charcoal font-medium" x-text="selectedEvent.end"></span></p>
                        </template>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="rounded-lg bg-gray-50 p-2 text-gray-400 shrink-0">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Lokasi Tempat</p>
                        <p class="mt-0.5 text-charcoal font-medium" x-text="selectedEvent.location"></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-start gap-3">
                        <div class="rounded-lg bg-gray-50 p-2 text-gray-400 shrink-0">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Harga Tiket</p>
                            <p class="mt-0.5 text-charcoal font-medium" x-text="selectedEvent.price"></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="rounded-lg bg-gray-50 p-2 text-gray-400 shrink-0">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Maks. Peserta</p>
                            <p class="mt-0.5 text-charcoal font-medium" x-text="selectedEvent.max_participants"></p>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Deskripsi Event</p>
                    <p class="mt-1 text-gray-600 leading-relaxed text-sm bg-gray-50 rounded-xl p-3" x-text="selectedEvent.description || 'Belum ada deskripsi.'"></p>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a :href="selectedEvent.edit_url" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-primary/20 bg-primary/5 px-4 py-2.5 text-xs font-bold text-primary transition-all hover:bg-primary hover:text-white">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Ubah Event
                </a>

                <form :action="selectedEvent.delete_action" method="POST" class="delete-form inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-warning/20 bg-warning/5 px-4 py-2.5 text-xs font-bold text-warning transition-all hover:bg-warning hover:text-white flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const calendarEvents = @json($calendarEvents);
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
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
            eventClick: function(info) {
                const eventObj = info.event;
                const props = eventObj.extendedProps;
                
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                const startStr = new Date(eventObj.start).toLocaleDateString('id-ID', options);
                const endStr = eventObj.end ? new Date(eventObj.end).toLocaleDateString('id-ID', options) : null;
                
                window.dispatchEvent(new CustomEvent('open-event-modal', { detail: {
                    title: eventObj.title,
                    category: props.category,
                    start: startStr,
                    end: endStr,
                    location: props.location,
                    description: props.description,
                    price: props.price,
                    max_participants: props.max_participants,
                    edit_url: props.edit_url,
                    delete_action: props.delete_action
                }}));
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
</script>
@endpush
