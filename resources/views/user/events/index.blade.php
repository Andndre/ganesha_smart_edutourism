@extends('layouts.app')
@section('title', __('Kalender Event & Budaya'))
@section('header_title', __('Kalender Event'))

@section('content')
    <!-- FullCalendar CDN and Styles -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js" data-navigate-once></script>
    @include('user.events.partials.events-styles')

    <script>
        (function() {
            const registerComponent = () => {
                Alpine.data('eventCalendarPage', () => ({
                    viewMode: 'calendar',
                    showModal: false,
                    selectedEvent: {},
                    selectedCategory: 'All',
                    calendarEvents: @json($calendarEvents),
                    upcomingEvents: @json($upcomingEvents),

                    openDetail(eventObj) {
                        this.selectedEvent = eventObj;
                        window.dispatchEvent(new CustomEvent('open-event-detail'));
                    },

                    filterCategory(cat) {
                        this.selectedCategory = cat;
                        if (window.fcInstance) {
                            window.fcInstance.removeAllEvents();
                            const filtered = this.calendarEvents.filter(e => {
                                if (cat === 'All') return true;
                                return e.category.toLowerCase() === cat.toLowerCase();
                            });
                            window.fcInstance.addEventSource(filtered);
                        }
                    },

                    get filteredTimelineEvents() {
                        if (this.selectedCategory === 'All') {
                            return this.upcomingEvents;
                        }
                        return this.upcomingEvents.filter(e => e.category === this.selectedCategory);
                    },

                    formatDateCard(dateStr) {
                        if (!dateStr) return {
                            month: 'JAN',
                            day: '01'
                        };
                        const d = new Date(dateStr);
                        const months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP',
                            'OCT', 'NOV', 'DEC'
                        ];
                        return {
                            month: months[d.getMonth()],
                            day: String(d.getDate()).padStart(2, '0')
                        };
                    },

                    formatDateLong(dateStr) {
                        if (!dateStr) return '';
                        const d = new Date(dateStr);
                        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July',
                            'August', 'September', 'October', 'November', 'December'
                        ];
                        return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
                    },

                    initCalendar() {
                        const checkAndInit = () => {
                            if (typeof FullCalendar === 'undefined') {
                                setTimeout(checkAndInit, 100);
                                return;
                            }
                            const calendarEl = document.getElementById('calendar-public');
                            if (!calendarEl) return;

                            const calendar = new FullCalendar.Calendar(calendarEl, {
                                initialView: 'dayGridMonth',
                                locale: 'en',
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
                                    today: '{{ __('Hari Ini') }}',
                                    month: '{{ __('Bulan') }}',
                                    list: '{{ __('Agenda') }}'
                                },
                                events: this.calendarEvents,
                                eventClick: (info) => {
                                    const rawData = info.event.extendedProps.raw;
                                    if (rawData) {
                                        this.openDetail(rawData);
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
                        };

                        checkAndInit();
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
                        
                        this.initCalendar();
                    }
                }));
            };

            if (window.Alpine) {
                registerComponent();
            } else {
                document.addEventListener('alpine:init', registerComponent);
            }
        })();
    </script>

    <div x-data="eventCalendarPage" @open-detail-modal.window="openDetail($event.detail)"
        class="mx-auto max-w-lg space-y-6 px-4 py-6 md:max-w-4xl">

        @include('user.events.partials.events-header')

        @include('user.events.partials.events-toolbar')

        @include('user.events.partials.events-calendar')

        @include('user.events.partials.events-list')

        @include('user.events.partials.events-detail-modal')

    </div>
@endsection
