@extends('layouts.app')
@section('title', 'Kalender Event & Budaya')
@section('header_title', 'Kalender Event')

@push('styles')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <style>
        /* FullCalendar Custom Premium Styling */
        .fc {
            --fc-border-color: #f3f4f6;
            --fc-daygrid-event-dot-width: 8px;
            --fc-button-bg-color: #ffffff;
            --fc-button-border-color: #e5e7eb;
            --fc-button-text-color: #374151;
            --fc-button-active-bg-color: #1E5128;
            --fc-button-active-border-color: #1E5128;
            --fc-button-hover-bg-color: #f9fafb;
            --fc-button-hover-border-color: #d1d5db;
            --fc-today-bg-color: rgba(30, 81, 40, 0.04);
            --fc-event-border-color: transparent;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .fc .fc-toolbar-title {
            font-size: 1.1rem !important;
            font-weight: 800 !important;
            color: #1f2937;
            letter-spacing: -0.02em;
        }
        @media (min-width: 768px) {
            .fc .fc-toolbar-title {
                font-size: 1.5rem !important;
            }
        }
        .fc .fc-button {
            padding: 0.5rem 0.875rem !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            border-radius: 0.75rem !important;
            text-transform: capitalize !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active, 
        .fc .fc-button-primary:not(:disabled):active {
            background-color: #1E5128 !important;
            border-color: #1E5128 !important;
            color: #ffffff !important;
        }
        .fc .fc-event {
            padding: 4px 8px !important;
            border-radius: 8px !important;
            font-size: 0.7rem !important;
            font-weight: 700 !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
            border: none !important;
            transition: transform 0.15s ease, opacity 0.15s ease !important;
        }
        .fc .fc-event:hover {
            transform: scale(1.02);
            opacity: 0.95;
        }
        .fc .fc-daygrid-day-number {
            font-size: 0.825rem !important;
            font-weight: 700 !important;
            color: #4b5563 !important;
            padding: 6px !important;
        }
        .fc .fc-col-header-cell-cushion {
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            color: #9ca3af !important;
            text-transform: uppercase !important;
            padding: 8px 0 !important;
        }
        .fc-scroller {
            scrollbar-width: thin;
        }
        .fc-scroller::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        .fc-scroller::-webkit-scrollbar-track {
            background: transparent;
        }
        .fc-scroller::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 10px;
        }
        /* Custom Keyframe entrance for list items */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.4s ease forwards;
        }
    </style>
@endpush

@section('content')
    <div x-data="publicEvents" 
        @open-detail-modal.window="openDetail($event.detail)" 
        class="px-4 py-6 max-w-lg mx-auto md:max-w-4xl space-y-6">

        {{-- Editorial Header Section --}}
        <div class="relative overflow-hidden rounded-3xl bg-linear-to-tr from-primary to-[#2a6836] p-6 text-white shadow-xl">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -left-10 -bottom-10 h-32 w-32 rounded-full bg-[#D4AF37]/10 blur-2xl"></div>
            <div class="relative z-10">
                <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-[9px] font-bold uppercase tracking-wider backdrop-blur-md">Budaya & Event</span>
                <h1 class="font-display mt-2 text-2xl font-extrabold tracking-tight">Agenda Budaya Penglipuran</h1>
                <p class="mt-1.5 text-xs text-green-50/90 leading-relaxed">
                    Saksikan keluhuran ritus adat Bali, keceriaan festival budaya, dan jelajahi kearifan lokal melalui workshop interaktif di desa adat kami.
                </p>
            </div>
        </div>

        {{-- Dynamic Tab & Filter Toolbar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-white border border-gray-100 p-3 rounded-2xl shadow-sm">
            <!-- View Switcher -->
            <div class="inline-flex rounded-xl bg-gray-50 p-1">
                <button type="button" @click="viewMode = 'calendar'"
                    :class="viewMode === 'calendar' ? 'bg-white text-primary shadow-sm font-bold' : 'text-gray-500 hover:text-charcoal font-medium'"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs transition-all duration-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Kalender
                </button>
                <button type="button" @click="viewMode = 'list'"
                    :class="viewMode === 'list' ? 'bg-white text-primary shadow-sm font-bold' : 'text-gray-500 hover:text-charcoal font-medium'"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs transition-all duration-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    Daftar Acara
                </button>
            </div>

            <!-- Custom Elegant Filter -->
            <div class="relative shrink-0">
                <select @change="filterCategory($event.target.value)"
                    class="w-full sm:w-auto appearance-none focus:border-primary rounded-xl border border-gray-200 bg-white py-2 pl-3 pr-8 text-xs font-semibold text-gray-700 focus:outline-none focus:ring-1 focus:ring-primary/20">
                    <option value="Semua">Semua Kategori</option>
                    <option value="Upacara Adat">Upacara Adat</option>
                    <option value="Festival">Festival</option>
                    <option value="Workshop">Workshop</option>
                    <option value="Kuliner">Kuliner</option>
                </select>
                <div class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- VIEW MODE 1: INTERACTIVE CALENDAR --}}
        <div x-show="viewMode === 'calendar'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            class="rounded-3xl border border-gray-100 bg-white p-4 shadow-sm md:p-6" style="display: none;">
            <div id="calendar-public"></div>
        </div>

        {{-- VIEW MODE 2: TIMELINE LIST --}}
        <div x-show="viewMode === 'list'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                <div>
                    <h2 class="text-charcoal text-base font-bold">Acara Mendatang</h2>
                    <p class="text-[11px] text-gray-500 mt-0.5">Daftar agenda kebudayaan terdekat yang terdaftar</p>
                </div>
                <span class="text-[10px] font-bold text-gray-400 uppercase bg-gray-50 px-2 py-1 rounded-lg" x-text="filteredTimelineEvents.length + ' Acara'"></span>
            </div>

            <!-- Timeline Cards Container -->
            <div class="relative space-y-6 border-l-2 border-gray-100 pl-4 ml-2">
                <template x-for="(e, index) in filteredTimelineEvents" :key="e.id">
                    <div class="relative animate-fade-in-up" :style="'animation-delay: ' + (index * 60) + 'ms'">
                        <!-- Timeline Pin -->
                        <div class="left-[-22px] absolute top-1.5 z-10 h-3 w-3 rounded-full border-2 border-white bg-primary shadow-sm"></div>

                        <!-- Card Element -->
                        <div class="group overflow-hidden rounded-2xl border border-gray-100 bg-white p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                            <div class="mb-3 flex items-center justify-between flex-wrap gap-2">
                                <span :class="{
                                    'bg-amber-50 text-amber-600 border-amber-100': e.category.toLowerCase().includes('adat') || e.category.toLowerCase().includes('upacara'),
                                    'bg-emerald-50 text-emerald-600 border-emerald-100': e.category.toLowerCase().includes('festival') || e.category.toLowerCase().includes('seni'),
                                    'bg-blue-50 text-blue-600 border-blue-100': e.category.toLowerCase().includes('workshop'),
                                    'bg-rose-50 text-rose-600 border-rose-100': e.category.toLowerCase().includes('kuliner')
                                }" class="rounded-lg border px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider" x-text="e.category"></span>
                                
                                <span :class="e.is_free ? 'bg-green-50 text-green-600' : 'bg-gray-50 text-gray-600'" 
                                    class="rounded-md px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider" 
                                    x-text="e.is_free ? 'Gratis' : 'Rp ' + Number(e.price).toLocaleString('id-ID')"></span>
                            </div>

                            <h3 class="text-charcoal group-hover:text-primary mb-1 text-base font-extrabold transition-colors duration-200" x-text="e.name"></h3>
                            <p class="mb-4 text-xs leading-relaxed text-gray-500 line-clamp-2" x-text="e.description || 'Tidak ada deskripsi tambahan untuk event budaya ini.'"></p>
                            
                            <!-- Parameters Grid -->
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-gray-50 pt-3 text-[11px] text-gray-500">
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span x-text="formatDateLong(e.start_date)"></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="e.start_time + ' WITA'"></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <span class="truncate max-w-[120px] md:max-w-none" x-text="e.location_name"></span>
                                </div>
                            </div>

                            <!-- Click Details Trigger -->
                            <div class="mt-4 flex gap-2">
                                <button type="button" @click="openDetail(e)"
                                    class="flex-1 text-center bg-gray-50 hover:bg-primary hover:text-white rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 transition-all duration-300 active:scale-[0.98]">
                                    Lihat Detail & Waktu
                                </button>
                                <template x-if="e.latitude && e.longitude">
                                    <a :href="'https://www.google.com/maps/search/?api=1&query=' + e.latitude + ',' + e.longitude" 
                                        target="_blank"
                                        class="aspect-square bg-gray-50 text-gray-400 hover:bg-amber-50 hover:text-amber-600 rounded-xl p-2 flex items-center justify-center transition-all duration-300">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="filteredTimelineEvents.length === 0" 
                class="rounded-3xl border border-dashed border-gray-200 bg-white p-8 text-center" style="display: none;">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-charcoal mt-3 text-sm font-bold">Tidak Ada Event Ditemukan</h3>
                <p class="mt-1 text-xs text-gray-400">Tidak ada event untuk kategori yang dipilih di bulan ini.</p>
            </div>
        </div>

        {{-- PREMIUM DETAIL EVENT MODAL (MOBILE BOTTOM-SHEET / DESKTOP MODAL) --}}
        <div x-show="showModal"
            class="bg-charcoal/60 fixed -inset-10 z-50 flex items-end justify-center px-0 md:items-center md:px-4 backdrop-blur-sm"
            style="display: none; will-change: transform; transform: translate3d(0,0,0);"
            x-transition:enter="transition ease-out duration-300" 
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" 
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" 
            x-transition:leave-end="opacity-0">
            
            <div class="relative w-full rounded-t-[2.5rem] bg-white p-6 shadow-2xl md:rounded-3xl max-w-lg pb-10 md:pb-6" 
                @click.away="showModal = false"
                x-show="showModal"
                x-transition:enter="transition ease-out duration-300 transform" 
                x-transition:enter-start="translate-y-full md:translate-y-4 md:scale-95"
                x-transition:enter-end="translate-y-0 md:translate-y-0 md:scale-100" 
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 md:translate-y-0 md:scale-100" 
                x-transition:leave-end="translate-y-full md:translate-y-4 md:scale-95">
                
                <!-- Pull Bar on Mobile -->
                <div class="mx-auto -mt-2 mb-5 h-1.5 w-12 rounded-full bg-gray-200 md:hidden"></div>
                
                <!-- Close Button on Desktop -->
                <button type="button" @click="showModal = false" 
                    class="absolute right-4 top-4 hidden items-center justify-center h-8 w-8 rounded-full bg-gray-50 text-gray-400 hover:text-gray-600 transition-colors md:flex"
                    title="Tutup">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Modal Body -->
                <div class="space-y-5" x-show="showModal">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span :class="{
                            'bg-amber-50 text-amber-600 border-amber-100': selectedEvent.category && (selectedEvent.category.toLowerCase().includes('adat') || selectedEvent.category.toLowerCase().includes('upacara')),
                            'bg-emerald-50 text-emerald-600 border-emerald-100': selectedEvent.category && (selectedEvent.category.toLowerCase().includes('festival') || selectedEvent.category.toLowerCase().includes('seni')),
                            'bg-blue-50 text-blue-600 border-blue-100': selectedEvent.category && selectedEvent.category.toLowerCase().includes('workshop'),
                            'bg-rose-50 text-rose-600 border-rose-100': selectedEvent.category && selectedEvent.category.toLowerCase().includes('kuliner')
                        }" class="rounded-lg border px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider" x-text="selectedEvent.category"></span>
                        
                        <button type="button" @click="showModal = false" class="text-xs font-bold text-gray-400 hover:text-gray-600 md:hidden">Tutup</button>
                    </div>

                    <h3 class="font-display text-charcoal text-xl font-black tracking-tight leading-snug" x-text="selectedEvent.name"></h3>

                    <!-- Visual Date Card Widget -->
                    <div class="flex items-center gap-3 bg-gray-50/70 p-3.5 rounded-2xl border border-gray-100">
                        <div class="flex flex-col items-center justify-center w-12 h-12 rounded-xl bg-primary text-white font-black shrink-0 shadow-sm shadow-primary/10">
                            <span class="text-[9px] uppercase tracking-wider leading-none" x-text="formatDateCard(selectedEvent.start_date).month"></span>
                            <span class="text-lg leading-none mt-1" x-text="formatDateCard(selectedEvent.start_date).day"></span>
                        </div>
                        <div>
                            <p class="text-xs font-black text-gray-700" x-text="formatDateLong(selectedEvent.start_date)"></p>
                            <p class="text-[11px] text-gray-500 mt-0.5" x-text="selectedEvent.start_time + ' - ' + selectedEvent.end_time + ' WITA'"></p>
                        </div>
                    </div>

                    <!-- Location Widget -->
                    <div class="flex items-start gap-3 bg-gray-50/70 p-3.5 rounded-2xl border border-gray-100">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-[#D4AF37] flex items-center justify-center shrink-0">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-black text-gray-700">Lokasi Acara</p>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-relaxed" x-text="selectedEvent.location_name"></p>
                        </div>
                    </div>

                    <!-- Price & Capacity Widget -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-center gap-2.5 bg-gray-50/70 p-3.5 rounded-xl border border-gray-100">
                            <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider leading-none">Harga Tiket</p>
                                <p class="text-xs font-black text-gray-700 mt-1" x-text="selectedEvent.is_free ? 'Gratis' : 'Rp ' + Number(selectedEvent.price).toLocaleString('id-ID')"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 bg-gray-50/70 p-3.5 rounded-xl border border-gray-100">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 005.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider leading-none">Kapasitas</p>
                                <p class="text-xs font-black text-gray-700 mt-1" x-text="selectedEvent.max_participants ? selectedEvent.max_participants + ' Orang' : '-'"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="border-t border-gray-50 pt-3">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi Acara</h4>
                        <p class="text-xs text-gray-500 leading-relaxed max-h-36 overflow-y-auto" x-text="selectedEvent.description || 'Tidak ada deskripsi tambahan.'"></p>
                    </div>

                    <!-- Open in Maps GPS Button -->
                    <template x-if="selectedEvent.latitude && selectedEvent.longitude">
                        <a :href="'https://www.google.com/maps/search/?api=1&query=' + selectedEvent.latitude + ',' + selectedEvent.longitude" 
                            target="_blank"
                            class="mt-6 flex items-center justify-center gap-2 rounded-2xl bg-primary hover:bg-[#152E1D] px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/10 transition-all duration-200 active:scale-[0.98] w-full">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            Buka Petunjuk Arah (Maps GPS)
                        </a>
                    </template>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('publicEvents', () => ({
                viewMode: 'calendar',
                showModal: false,
                selectedEvent: {},
                selectedCategory: 'Semua',
                calendarEvents: @json($calendarEvents),
                upcomingEvents: @json($upcomingEvents),

                openDetail(eventObj) {
                    this.selectedEvent = eventObj;
                    this.showModal = true;
                },

                filterCategory(cat) {
                    this.selectedCategory = cat;
                    if (window.fcInstance) {
                        window.fcInstance.removeAllEvents();
                        const filtered = this.calendarEvents.filter(e => {
                            if (cat === 'Semua') return true;
                            return e.category.toLowerCase() === cat.toLowerCase();
                        });
                        window.fcInstance.addEventSource(filtered);
                    }
                },

                get filteredTimelineEvents() {
                    if (this.selectedCategory === 'Semua') {
                        return this.upcomingEvents;
                    }
                    return this.upcomingEvents.filter(e => e.category === this.selectedCategory);
                },

                formatDateCard(dateStr) {
                    if (!dateStr) return { month: 'JAN', day: '01' };
                    const d = new Date(dateStr);
                    const months = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGS', 'SEP', 'OKT', 'NOV', 'DES'];
                    return {
                        month: months[d.getMonth()],
                        day: String(d.getDate()).padStart(2, '0')
                    };
                },

                formatDateLong(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
                },

                init() {
                    // Default to 'list' for mobile screens and 'calendar' for wider views
                    if (window.innerWidth < 768) {
                        this.viewMode = 'list';
                    }
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar-public');
            if (!calendarEl) return;

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth',
                locale: 'id',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    list: 'Agenda'
                },
                events: @json($calendarEvents),
                eventClick: function(info) {
                    const rawData = info.event.extendedProps.raw;
                    if (rawData) {
                        // Dispatch custom event to notify Alpine
                        const event = new CustomEvent('open-detail-modal', {
                            detail: rawData
                        });
                        window.dispatchEvent(event);
                    }
                },
                height: 'auto',
                handleWindowResize: true
            });

            calendar.render();
            window.fcInstance = calendar; // Save global reference for filtering
        });
    </script>
@endpush