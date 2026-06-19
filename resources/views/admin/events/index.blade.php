@extends('layouts.dashboard')

@section('title', 'Event & Kalender')

@section('push_scripts')
    {{-- We use push('styles') to import stylesheets or JS files if needed --}}
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    @include('partials.fullcalendar-styles')
    <style>
        .leaflet-control-attribution {
            display: none !important;
        }

        /* Premium Selected Marker Animation */
        @keyframes pin-breath {
            0%, 100% {
                transform: scale(1);
                filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.15));
            }
            50% {
                transform: scale(1.15);
                filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.25));
            }
        }

        .marker-selected-glow {
            animation: pin-breath 2s infinite ease-in-out;
            transform-origin: center;
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
        @open-edit-modal.window="openEdit($event.detail)">

        <div class="space-y-6">
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

            {{-- Views Partials --}}
            @include('admin.events.partials.calendar-view')
            @include('admin.events.partials.list-view')
        </div>

        {{-- Modals Partials --}}
        @include('admin.events.partials.detail-modal')
        @include('admin.events.partials.form-modal')

    </div>

@endsection

@push('scripts')
    @include('admin.events.partials.scripts')
@endpush
