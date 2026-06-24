<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-gesture-handling@1.2.2/dist/leaflet-gesture-handling.min.js"></script>
<script>
    const PENGLIPURAN_LAT = {{ config('services.penglipuran.latitude') }};
    const PENGLIPURAN_LNG = {{ config('services.penglipuran.longitude') }};
    const PENGLIPURAN_ZOOM = {{ config('services.penglipuran.zoom') }};

    document.addEventListener('alpine:init', () => {
        Alpine.data('adminEvents', () => ({
            viewMode: 'calendar',
            showModal: false,
            selectedEvent: {},

            showFormModal: false,
            formAction: '',
            formMethod: 'POST',
            formTitle: 'Tambah Event Baru',

            formMap: null,
            formMarker: null,

            formFields: {
                id: @json(old('id', '')),
                'name[en]': @json(old('name.en', '')),
                'name[id]': @json(old('name.id', '')),
                'description[en]': @json(old('description.en', '')),
                'description[id]': @json(old('description.id', '')),
                category: @json(old('category', 'Upacara Adat')),
                start_date: @json(old('start_date', '')),
                start_time: @json(old('start_time', '')),
                end_date: @json(old('end_date', '')),
                end_time: @json(old('end_time', '')),
                'location_name[en]': @json(old('location_name.en', '')),
                'location_name[id]': @json(old('location_name.id', '')),
                latitude: @json(old('latitude', '')),
                longitude: @json(old('longitude', '')),
                is_free: {{ old('is_free') !== null || !$errors->any() ? 'true' : 'false' }},
                price: @json(old('price', '')),
                max_participants: @json(old('max_participants', ''))
            },

            initFormMap() {
                if (this.formMap) return;
                const mapContainer = document.getElementById('form-location-map');
                if (!mapContainer) return;

                const lat = parseFloat(this.formFields.latitude) || PENGLIPURAN_LAT;
                const lng = parseFloat(this.formFields.longitude) || PENGLIPURAN_LNG;

                this.formMap = L.map('form-location-map', { zoomControl: true, attributionControl: false, gestureHandling: true })
                    .setView([lat, lng], PENGLIPURAN_ZOOM);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(this.formMap);

                this.formMap.on('click', (e) => {
                    this.setFormMarker(e.latlng.lat, e.latlng.lng);
                });
            },

            setFormMarker(lat, lng) {
                this.formFields.latitude = lat;
                this.formFields.longitude = lng;

                const color = '#1E5128';
                const formPinIcon = L.divIcon({
                    className: 'custom-pin-selected',
                    html: `
                        <div class="relative flex items-center justify-center marker-selected-glow" style="width: 32px; height: 32px;">
                            <span class="absolute inline-flex h-6 w-6 animate-ping rounded-full opacity-40" style="background-color: ${color};"></span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white shadow-lg text-white" style="background-color: ${color}; z-index: 10;">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                            </div>
                        </div>
                    `,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                if (this.formMarker) {
                    this.formMarker.setLatLng([lat, lng]);
                } else {
                    this.formMarker = L.marker([lat, lng], { icon: formPinIcon, draggable: true }).addTo(this.formMap);
                    this.formMarker.on('dragend', () => {
                        const position = this.formMarker.getLatLng();
                        this.formFields.latitude = position.lat;
                        this.formFields.longitude = position.lng;
                    });
                }
            },

            syncFormMap() {
                setTimeout(() => {
                    this.initFormMap();
                    if (this.formMap) {
                        this.formMap.invalidateSize();
                        if (this.formFields.latitude && this.formFields.longitude) {
                            const lat = parseFloat(this.formFields.latitude);
                            const lng = parseFloat(this.formFields.longitude);
                            this.formMap.setView([lat, lng], PENGLIPURAN_ZOOM);
                            this.setFormMarker(lat, lng);
                        } else {
                            this.formMap.setView([PENGLIPURAN_LAT, PENGLIPURAN_LNG], PENGLIPURAN_ZOOM);
                            if (this.formMarker) {
                                this.formMap.removeLayer(this.formMarker);
                                this.formMarker = null;
                            }
                        }
                    }
                }, 300);
            },

            openCreate(dateStr = '', timeStr = '') {
                this.formTitle = 'Tambah Event Baru';
                this.formAction = '{{ route('admin.events.store') }}';
                this.formMethod = 'POST';

                this.formFields = {
                    id: '',
                    'name[en]': '',
                    'name[id]': '',
                    'description[en]': '',
                    'description[id]': '',
                    category: 'Upacara Adat',
                    start_date: dateStr || new Date().toISOString().split('T')[0],
                    start_time: timeStr || '10:00',
                    end_date: dateStr || new Date().toISOString().split('T')[0],
                    end_time: timeStr ? this.addHours(timeStr, 2) : '12:00',
                    'location_name[en]': '',
                    'location_name[id]': '',
                    latitude: '',
                    longitude: '',
                    is_free: true,
                    price: '',
                    max_participants: ''
                };

                window.dispatchEvent(new CustomEvent('open-event-form-modal'));
                this.syncFormMap();
            },

            openEdit(eventData) {
                this.formTitle = 'Ubah Event';
                this.formAction = '{{ route('admin.events.update', 'EVENT_ID') }}'.replace(
                    'EVENT_ID', eventData.id);
                this.formMethod = 'PUT';

                this.formFields = {
                    id: eventData.id,
                    'name[en]': eventData.name?.en || eventData.name || '',
                    'name[id]': eventData.name?.id || eventData.name || '',
                    'description[en]': eventData.description?.en || eventData.description || '',
                    'description[id]': eventData.description?.id || eventData.description || '',
                    category: eventData.category,
                    start_date: eventData.start_date,
                    start_time: eventData.start_time || '',
                    end_date: eventData.end_date,
                    end_time: eventData.end_time || '',
                    'location_name[en]': eventData.location_name?.en || eventData.location_name || '',
                    'location_name[id]': eventData.location_name?.id || eventData.location_name || '',
                    latitude: eventData.latitude || '',
                    longitude: eventData.longitude || '',
                    is_free: !!eventData.is_free,
                    price: eventData.price || '',
                    max_participants: eventData.max_participants || ''
                };

                window.dispatchEvent(new CustomEvent('open-event-form-modal'));
                this.syncFormMap();
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
                    this.syncFormMap();
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
