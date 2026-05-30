@extends('layouts.app')
@section('title', 'Kalender Event & Budaya')
@section('header_title', 'Kalender Event')

@push('styles')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    @include('user.events.partials.events-styles')
@endpush

@section('content')
    <div x-data="publicEvents" 
        @open-detail-modal.window="openDetail($event.detail)" 
        class="px-4 py-6 max-w-lg mx-auto md:max-w-4xl space-y-6">

        @include('user.events.partials.events-header')

        @include('user.events.partials.events-toolbar')

        @include('user.events.partials.events-calendar')

        @include('user.events.partials.events-list')

        @include('user.events.partials.events-detail-modal')

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

                    // Watch viewMode changes to recalculate sizes when switching tabs
                    this.$watch('viewMode', (value) => {
                        if (value === 'calendar') {
                            setTimeout(() => {
                                if (window.fcInstance) {
                                    window.fcInstance.updateSize();
                                }
                            }, 50);
                        }
                    });
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar-public');
            if (!calendarEl) return;

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                headerToolbar: window.innerWidth < 768 ? {
                    left: 'prev,next',
                    center: 'title',
                    right: 'today'
                } : {
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

            // Force dynamic layout sync after render to prevent initial 0px container collapse
            setTimeout(() => {
                calendar.updateSize();
            }, 50);
        });
    </script>
@endpush