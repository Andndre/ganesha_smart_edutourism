@extends('layouts.admin')

@section('title', 'Peta Lokasi & Titik')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .leaflet-control-attribution {
            display: none !important;
        }

        @media (min-width: 1024px) {
            #admin-main {
                height: 100vh;
                overflow: hidden !important;
                display: flex;
                flex-direction: column;
                padding-bottom: 2rem !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-charcoal">Peta Lokasi & Titik</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola objek budaya, UMKM, dan fasilitas desa langsung di atas peta
            interaktif.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:flex-1 lg:min-h-0">

        {{-- Left Side Panel: Instructions, Filters, and Dynamic Forms --}}
        <div class="lg:col-span-4 lg:h-full lg:overflow-y-auto lg:pr-2 flex flex-col space-y-4">
            {{-- IDLE PANEL: Default instructions & filters --}}
            <div id="panel-idle" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm space-y-5">
                <div>
                    <h2 class="font-semibold text-charcoal flex items-center gap-2">
                        <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Panduan Navigasi Peta
                    </h2>
                    <p class="mt-2 text-xs text-gray-500 leading-relaxed">
                        1. <strong>Klik area kosong</strong> pada peta untuk meletakkan pin baru dan menambahkan data
                        lokasi.<br>
                        2. <strong>Klik marker/penanda</strong> yang sudah ada untuk melihat detail, mengubah informasi,
                        atau menyeret (drag) lokasinya.<br>
                        3. <strong>Gunakan filter</strong> di bawah untuk menyaring tampilan penanda di peta.
                    </p>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Filter Kategori Peta</h3>
                    <div class="space-y-2.5">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" id="filter-cultural" checked
                                class="rounded border-gray-300 text-primary focus:ring-primary h-4.5 w-4.5"
                                onchange="filterMarkers()">
                            <span class="h-3.5 w-3.5 rounded-full" style="background-color: #1E5128"></span>
                            <span class="text-xs font-semibold text-gray-700">Objek Budaya (<span
                                    id="count-cultural">0</span>)</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" id="filter-umkm" checked
                                class="rounded border-gray-300 text-primary focus:ring-primary h-4.5 w-4.5"
                                onchange="filterMarkers()">
                            <span class="h-3.5 w-3.5 rounded-full" style="background-color: #8B5CF6"></span>
                            <span class="text-xs font-semibold text-gray-700">UMKM / Toko (<span
                                    id="count-umkm">0</span>)</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" id="filter-facility" checked
                                class="rounded border-gray-300 text-primary focus:ring-primary h-4.5 w-4.5"
                                onchange="filterMarkers()">
                            <span class="h-3.5 w-3.5 rounded-full" style="background-color: #3B82F6"></span>
                            <span class="text-xs font-semibold text-gray-700">Fasilitas Umum (<span
                                    id="count-facility">0</span>)</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" id="filter-toilet" checked
                                class="rounded border-gray-300 text-primary focus:ring-primary h-4.5 w-4.5"
                                onchange="filterMarkers()">
                            <span class="h-3.5 w-3.5 rounded-full" style="background-color: #06B6D4"></span>
                            <span class="text-xs font-semibold text-gray-700">Toilet (<span
                                    id="count-toilet">0</span>)</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- EDITOR PANEL: Create / Edit Form container --}}
            <div id="panel-editor"
                class="hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm flex-col space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h2 id="editor-title" class="font-bold text-charcoal text-lg">Tambah Lokasi Baru</h2>
                    <button type="button" onclick="cancelEditor()"
                        class="text-gray-400 hover:text-charcoal transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Type Selector --}}
                <div id="selector-container">
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tipe Lokasi <span
                            class="text-warning">*</span></label>
                    <select id="type-selector" onchange="switchForm(this.value)"
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                        <option value="cultural">Objek Budaya</option>
                        <option value="umkm">UMKM / Toko</option>
                        <option value="facility">Fasilitas Umum</option>
                    </select>
                </div>

                {{-- FORM 1: Cultural Object --}}
                <form id="form-cultural" action="{{ route('admin.cultural-objects.store') }}" method="POST"
                    enctype="multipart/form-data" class="hidden space-y-4">
                    @csrf
                    <div id="method-cultural"></div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Objek Budaya <span
                                class="text-warning">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Pura Penataran Agung"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kategori Budaya <span
                                class="text-warning">*</span></label>
                        <select name="category" required
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                            <option value="temple">Pura / Tempat Suci</option>
                            <option value="house">Pekarangan Adat / Rumah</option>
                            <option value="craft">Kerajinan Seni</option>
                            <option value="tradition">Tradisi Adat / Upacara</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi</label>
                        <textarea name="description" rows="3" placeholder="Tulis deskripsi singkat objek budaya..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none"></textarea>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">ID Marker AR (Opsional)</label>
                        <input type="text" name="ar_marker_id" placeholder="Contoh: MARKER_PURA_01"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Model 3D (.glb, Max 20MB)</label>
                        <input type="file" name="model_3d_file" accept=".glb"
                            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                        <span id="current-model-3d" class="text-[10px] text-gray-400 block mt-1"></span>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Audio Narasi (.mp3, Max
                            10MB)</label>
                        <input type="file" name="audio_narration_file" accept="audio/*"
                            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                        <span id="current-audio" class="text-[10px] text-gray-400 block mt-1"></span>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Foto Sejarah (Dapat memilih beberapa
                            file)</label>
                        <input type="file" name="historical_images[]" multiple accept="image/*"
                            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                        <div id="current-images" class="flex flex-wrap gap-1 mt-2"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase">Latitude</label>
                            <input type="text" name="latitude" readonly
                                class="w-full rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase">Longitude</label>
                            <input type="text" name="longitude" readonly
                                class="w-full rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" id="cultural_is_accessible" name="is_accessible" value="1" checked
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <label for="cultural_is_accessible" class="text-sm font-semibold text-gray-700">Akses Ramah
                            Disabilitas</label>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas</label>
                        <textarea name="accessibility_notes" rows="2"
                            placeholder="Contoh: Pintu masuk landai, ramah kursi roda..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none resize-none">Akses jalan datar ramah kursi roda dan stroller bayi.</textarea>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit"
                            class="flex-1 rounded-xl bg-primary py-2.5 text-sm font-semibold text-white transition-all hover:bg-primary-600">Simpan</button>
                        <button type="button" onclick="cancelEditor()"
                            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
                    </div>
                </form>

                {{-- FORM 2: UMKM Profile --}}
                <form id="form-umkm" action="{{ route('admin.umkm.profile.store') }}" method="POST"
                    class="hidden space-y-4">
                    @csrf
                    <div id="method-umkm"></div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Toko / Warung UMKM <span
                                class="text-warning">*</span></label>
                        <input type="text" name="business_name" required placeholder="Contoh: Warung Dedari"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Pemilik <span
                                class="text-warning">*</span></label>
                        <input type="text" name="owner_name" required placeholder="Contoh: Wayan Sudira"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kategori UMKM <span
                                class="text-warning">*</span></label>
                        <select name="category" required
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                            <option value="culinary">Kuliner / Makanan</option>
                            <option value="craft">Kerajinan / Kerajinan Tangan</option>
                            <option value="souvenir">Oleh-oleh / Cendera Mata</option>
                            <option value="service">Jasa Wisata / Massage</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Bisnis</label>
                        <textarea name="description" rows="3"
                            placeholder="Jelaskan mengenai menu atau layanan yang ditawarkan..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Rating Awal</label>
                            <input type="number" step="0.1" name="rating" min="0" max="5" value="5.0"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700">ID Marker AR (Opsional)</label>
                            <input type="text" name="ar_marker_id" placeholder="Contoh: UMKM_DEDARI_01"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase">Latitude</label>
                            <input type="text" name="latitude" readonly
                                class="w-full rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase">Longitude</label>
                            <input type="text" name="longitude" readonly
                                class="w-full rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4 py-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" checked
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="text-sm font-semibold text-gray-700">Aktifkan Toko</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" id="umkm_is_accessible" name="is_accessible" value="1" checked
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="text-sm font-semibold text-gray-700">Akses Ramah Disabilitas</span>
                        </label>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas</label>
                        <textarea name="accessibility_notes" rows="2" placeholder="Contoh: Pintu masuk ramah kursi roda..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none resize-none">Pintu masuk landai, staf siap membantu akses disabilitas.</textarea>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit"
                            class="flex-1 rounded-xl bg-primary py-2.5 text-sm font-semibold text-white transition-all hover:bg-primary-600">Simpan</button>
                        <button type="button" onclick="cancelEditor()"
                            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
                    </div>
                </form>

                {{-- FORM 3: Facility --}}
                <form id="form-facility" action="{{ route('admin.facilities.store') }}" method="POST"
                    class="hidden space-y-4">
                    @csrf
                    <div id="method-facility"></div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Fasilitas <span
                                class="text-warning">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Toilet Umum Pura"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tipe Fasilitas <span
                                class="text-warning">*</span></label>
                        <select name="type" required
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                            <option value="toilet">Toilet</option>
                            <option value="information">Pusat Informasi / Balai</option>
                            <option value="parking">Area Parkir</option>
                            <option value="emergency">Pos Keamanan / Kesehatan (Emergency)</option>
                            <option value="accessibility">Layanan Disabilitas (Accessibility)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi</label>
                        <textarea name="description" rows="3"
                            placeholder="Tambahkan informasi pelengkap untuk fasilitas ini..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase">Latitude</label>
                            <input type="text" name="latitude" readonly
                                class="w-full rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase">Longitude</label>
                            <input type="text" name="longitude" readonly
                                class="w-full rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4 py-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" checked
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="text-sm font-semibold text-gray-700">Aktifkan Fasilitas</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" id="facility_is_accessible" name="is_accessible" value="1" checked
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="text-sm font-semibold text-gray-700">Akses Ramah Disabilitas</span>
                        </label>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas</label>
                        <textarea name="accessibility_notes" rows="2"
                            placeholder="Contoh: Toilet dilengkapi dengan pegangan besi..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none resize-none">Akses jalan datar ramah kursi roda.</textarea>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit"
                            class="flex-1 rounded-xl bg-primary py-2.5 text-sm font-semibold text-white transition-all hover:bg-primary-600">Simpan</button>
                        <button type="button" onclick="cancelEditor()"
                            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
                    </div>
                </form>

                {{-- DELETE BUTTON FORM (Hidden on Create) --}}
                <div id="delete-container" class="hidden pt-2 border-t border-gray-100">
                    <form id="form-delete" action="" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full rounded-xl border border-red-200 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 transition-all flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus Lokasi Ini
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- Right Side Panel: The Interactive Map --}}
        <div class="lg:col-span-8 lg:h-full">
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm h-full flex flex-col">
                <div id="location-map"
                    class="w-full rounded-xl border border-gray-200 shadow-inner flex-1 min-h-[450px] lg:min-h-0"
                    style="z-index: 0;"></div>

                {{-- Legend --}}
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-gray-500 shrink-0">
                    <div class="flex items-center gap-1.5">
                        <span class="h-3 w-3 rounded-full" style="background-color: #1E5128"></span> Objek Budaya
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-3 w-3 rounded-full" style="background-color: #8B5CF6"></span> UMKM / Toko
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-3 w-3 rounded-full" style="background-color: #3B82F6"></span> Fasilitas Umum
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-3 w-3 rounded-full" style="background-color: #06B6D4"></span> Toilet
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        // ==========================================
        // CONFIG & INITS
        // ==========================================
        const PENGLIPURAN_LAT = -8.421750367447837;
        const PENGLIPURAN_LNG = 115.35900208148409;
        const PENGLIPURAN_ZOOM = 17;

        // Loaded locations from Controller
        const locations = @json($locations);
        const storageUrl = "{{ asset('storage') }}";

        let map = null;
        let markers = []; // List of L.marker instances
        let activeMarker = null; // Currently selected/edited marker
        let tempMarker = null; // Temp marker when creating new location
        let currentMode = 'idle'; // 'idle', 'create', 'edit'

        // Set up category colors
        const categoryColors = {
            umkm: '#8B5CF6',         // Violet
            facility: '#3B82F6',     // Blue
            toilet: '#06B6D4',       // Cyan
            cultural: '#1E5128'      // Green
        };

        document.addEventListener('DOMContentLoaded', function () {
            initCounts();
            initMap();
        });

        function initCounts() {
            let countCultural = 0;
            let countUmkm = 0;
            let countFacility = 0;
            let countToilet = 0;

            locations.forEach(loc => {
                if (loc.category === 'cultural') countCultural++;
                else if (loc.category === 'umkm') countUmkm++;
                else if (loc.category === 'facility') {
                    if (loc.locationable && loc.locationable.type === 'toilet') countToilet++;
                    else countFacility++;
                }
            });

            document.getElementById('count-cultural').innerText = countCultural;
            document.getElementById('count-umkm').innerText = countUmkm;
            document.getElementById('count-facility').innerText = countFacility;
            document.getElementById('count-toilet').innerText = countToilet;
        }

        function initMap() {
            map = L.map('location-map', { zoomControl: true, attributionControl: false })
                .setView([PENGLIPURAN_LAT, PENGLIPURAN_LNG], PENGLIPURAN_ZOOM);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            renderMarkers();

            // Map Click handler: trigger create mode
            map.on('click', function (e) {
                handleMapClick(e.latlng.lat, e.latlng.lng);
            });
        }

        // Dynamic marker icon helper
        function getMarkerIcon(category, type = null) {
            let color = categoryColors[category] || '#1E5128';
            if (category === 'facility' && type === 'toilet') {
                color = categoryColors.toilet;
            }

            return L.divIcon({
                className: 'custom-pin',
                html: `
                    <div class="flex items-center justify-center rounded-full border-2 border-white shadow-md hover:scale-110 transition-transform duration-200" 
                         style="background-color: ${color}; width: 22px; height: 22px;">
                    </div>
                `,
                iconSize: [22, 22],
                iconAnchor: [11, 11]
            });
        }

        // Dynamic icon for selected/draggable marker
        getSelectedMarkerIcon = function (category, type = null) {
            let color = categoryColors[category] || '#1E5128';
            if (category === 'facility' && type === 'toilet') {
                color = categoryColors.toilet;
            }

            return L.divIcon({
                className: 'custom-pin-selected',
                html: `
                    <div class="relative flex items-center justify-center animate-bounce" style="width: 32px; height: 32px; margin-top: -10px;">
                        <span class="absolute inline-flex h-6 w-6 animate-ping rounded-full opacity-40" style="background-color: ${color};"></span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white shadow-lg text-white" style="background-color: ${color}; z-index: 10;">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                        </div>
                    </div>
                `,
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });
        }

        function renderMarkers() {
            // Clear all markers from map
            markers.forEach(m => map.removeLayer(m));
            markers = [];

            locations.forEach(loc => {
                if (!loc.latitude || !loc.longitude) return;

                const isToilet = (loc.category === 'facility' && loc.locationable && loc.locationable.type === 'toilet');
                const marker = L.marker([loc.latitude, loc.longitude], {
                    icon: getMarkerIcon(loc.category, loc.locationable ? loc.locationable.type : null)
                });

                // Store custom info
                marker.locationData = loc;

                // Marker Click handler: edit mode
                marker.on('click', function (e) {
                    L.DomEvent.stopPropagation(e); // Stop from triggering map click
                    handleMarkerClick(marker);
                });

                // Attach marker to the map and array
                marker.addTo(map);
                markers.push(marker);
            });
        }

        function filterMarkers() {
            const showCultural = document.getElementById('filter-cultural').checked;
            const showUmkm = document.getElementById('filter-umkm').checked;
            const showFacility = document.getElementById('filter-facility').checked;
            const showToilet = document.getElementById('filter-toilet').checked;

            markers.forEach(m => {
                const loc = m.locationData;
                let visible = false;

                if (loc.category === 'cultural' && showCultural) visible = true;
                else if (loc.category === 'umkm' && showUmkm) visible = true;
                else if (loc.category === 'facility') {
                    const isToilet = loc.locationable && loc.locationable.type === 'toilet';
                    if (isToilet && showToilet) visible = true;
                    if (!isToilet && showFacility) visible = true;
                }

                if (visible) {
                    if (!map.hasLayer(m)) m.addTo(map);
                } else {
                    if (map.hasLayer(m)) map.removeLayer(m);
                }
            });
        }

        // ==========================================
        // CREATE / ADD NEW LOCATION LOGIC
        // ==========================================
        function handleMapClick(lat, lng) {
            if (currentMode === 'edit') {
                // In edit mode, clicking the map moves the selected marker's position
                if (activeMarker) {
                    activeMarker.setLatLng([lat, lng]);
                    updateCoordinateInputs(lat, lng);
                }
                return;
            }

            currentMode = 'create';

            // Show panel & reset forms
            document.getElementById('panel-idle').classList.add('hidden');
            document.getElementById('panel-editor').classList.remove('hidden');
            document.getElementById('editor-title').innerText = "Tambah Lokasi Baru";
            document.getElementById('selector-container').classList.remove('hidden');
            document.getElementById('delete-container').classList.add('hidden');

            // Remove active marker animations if editing before
            resetSelectedMarkerVisuals();

            // Place temporary marker
            if (tempMarker) {
                tempMarker.setLatLng([lat, lng]);
            } else {
                tempMarker = L.marker([lat, lng], {
                    icon: getSelectedMarkerIcon('cultural'), // default
                    draggable: true
                }).addTo(map);

                tempMarker.on('dragend', function (e) {
                    const pos = tempMarker.getLatLng();
                    updateCoordinateInputs(pos.lat, pos.lng);
                });
            }

            // Reset and switch to default (cultural) form
            resetForms();

            updateCoordinateInputs(lat, lng);

            const typeSelect = document.getElementById('type-selector');
            typeSelect.disabled = false;
            typeSelect.value = 'cultural';

            switchForm('cultural');
        }

        function updateCoordinateInputs(lat, lng) {
            const fixedLat = parseFloat(lat).toFixed(8);
            const fixedLng = parseFloat(lng).toFixed(8);

            document.querySelectorAll('input[name="latitude"]').forEach(input => input.value = fixedLat);
            document.querySelectorAll('input[name="longitude"]').forEach(input => input.value = fixedLng);
        }

        function switchForm(type) {
            // Hide all forms
            document.getElementById('form-cultural').classList.add('hidden');
            document.getElementById('form-umkm').classList.add('hidden');
            document.getElementById('form-facility').classList.add('hidden');

            // Show active form
            const formId = `form-${type}`;
            document.getElementById(formId).classList.remove('hidden');

            // Update temp marker color to match type
            if (tempMarker) {
                let catType = type;
                if (type === 'facility') {
                    const subType = document.querySelector('#form-facility select[name="type"]').value;
                    tempMarker.setIcon(getSelectedMarkerIcon('facility', subType));
                } else {
                    tempMarker.setIcon(getSelectedMarkerIcon(type));
                }
            }
        }

        // Attach listener to facility type select to update icon color
        document.querySelector('#form-facility select[name="type"]').addEventListener('change', function () {
            if (tempMarker && currentMode === 'create') {
                tempMarker.setIcon(getSelectedMarkerIcon('facility', this.value));
            }
            if (activeMarker && currentMode === 'edit') {
                activeMarker.setIcon(getSelectedMarkerIcon('facility', this.value));
            }
        });

        // ==========================================
        // EDIT LOCATION LOGIC
        // ==========================================
        function handleMarkerClick(marker) {
            // Remove temp marker if it exists
            if (tempMarker) {
                map.removeLayer(tempMarker);
                tempMarker = null;
            }

            // Reset any previous active marker visuals
            resetSelectedMarkerVisuals();

            currentMode = 'edit';
            activeMarker = marker;

            const loc = marker.locationData;
            const details = loc.locationable;

            // Change marker icon to selected
            const type = details ? details.type : null;
            marker.setIcon(getSelectedMarkerIcon(loc.category, type));

            // Enable dragging for this marker
            marker.dragging.enable();
            marker.on('dragend', function (e) {
                const pos = marker.getLatLng();
                updateCoordinateInputs(pos.lat, pos.lng);
            });

            // Toggle panel
            document.getElementById('panel-idle').classList.add('hidden');
            document.getElementById('panel-editor').classList.remove('hidden');
            document.getElementById('editor-title').innerText = "Edit Lokasi";

            // Disable type selector since we can't transform type
            const typeSelect = document.getElementById('type-selector');
            typeSelect.value = loc.category;
            typeSelect.disabled = true;

            // Show delete option
            document.getElementById('delete-container').classList.remove('hidden');

            resetForms();
            updateCoordinateInputs(loc.latitude, loc.longitude);

            if (loc.category === 'cultural') {
                switchForm('cultural');
                const form = document.getElementById('form-cultural');
                form.action = `/admin/cultural-objects/${details.id}`;
                document.getElementById('method-cultural').innerHTML = '@method("PUT")';

                form.querySelector('input[name="name"]').value = details.name;
                form.querySelector('select[name="category"]').value = details.category;
                form.querySelector('textarea[name="description"]').value = details.description || '';
                form.querySelector('input[name="ar_marker_id"]').value = details.ar_marker_id || '';

                // File previews
                document.getElementById('current-model-3d').innerHTML = details.model_3d_path
                    ? `File saat ini: <a href="${storageUrl}/${details.model_3d_path}" target="_blank" class="text-primary hover:underline font-semibold">${details.model_3d_path.split('/').pop()}</a>`
                    : 'Belum ada model 3D';

                document.getElementById('current-audio').innerHTML = details.audio_narration_path
                    ? `File saat ini: <a href="${storageUrl}/${details.audio_narration_path}" target="_blank" class="text-primary hover:underline font-semibold">${details.audio_narration_path.split('/').pop()}</a>`
                    : 'Belum ada audio narasi';

                const imgContainer = document.getElementById('current-images');
                imgContainer.innerHTML = '';
                if (details.historical_images && details.historical_images.length > 0) {
                    details.historical_images.forEach(img => {
                        const imgEl = document.createElement('img');
                        imgEl.src = `${storageUrl}/${img}`;
                        imgEl.className = "w-10 h-10 object-cover rounded border border-gray-100";
                        imgContainer.appendChild(imgEl);
                    });
                }

                form.querySelector('input[name="is_accessible"]').checked = loc.is_accessible;
                form.querySelector('textarea[name="accessibility_notes"]').value = loc.accessibility_notes || '';

                // Setup Delete Action
                document.getElementById('form-delete').action = `/admin/cultural-objects/${details.id}`;

            } else if (loc.category === 'umkm') {
                switchForm('umkm');
                const form = document.getElementById('form-umkm');
                form.action = `/admin/umkm/profiles/${details.id}`;
                document.getElementById('method-umkm').innerHTML = '@method("PUT")';

                form.querySelector('input[name="business_name"]').value = details.business_name;
                form.querySelector('input[name="owner_name"]').value = details.owner_name;
                form.querySelector('select[name="category"]').value = details.category;
                form.querySelector('textarea[name="description"]').value = details.description || '';
                form.querySelector('input[name="rating"]').value = details.rating || '5.0';
                form.querySelector('input[name="ar_marker_id"]').value = details.ar_marker_id || '';
                form.querySelector('input[name="is_active"]').checked = details.is_active;
                form.querySelector('input[name="is_accessible"]').checked = loc.is_accessible;
                form.querySelector('textarea[name="accessibility_notes"]').value = loc.accessibility_notes || '';

                // Setup Delete Action
                document.getElementById('form-delete').action = `/admin/umkm/profiles/${details.id}`;

            } else if (loc.category === 'facility') {
                switchForm('facility');
                const form = document.getElementById('form-facility');
                form.action = `/admin/facilities/${details.id}`;
                document.getElementById('method-facility').innerHTML = '@method("PUT")';

                form.querySelector('input[name="name"]').value = details.name;
                form.querySelector('select[name="type"]').value = details.type;
                form.querySelector('textarea[name="description"]').value = details.description || '';
                form.querySelector('input[name="is_active"]').checked = details.is_active;
                form.querySelector('input[name="is_accessible"]').checked = loc.is_accessible;
                form.querySelector('textarea[name="accessibility_notes"]').value = loc.accessibility_notes || '';

                // Setup Delete Action
                document.getElementById('form-delete').action = `/admin/facilities/${details.id}`;
            }

            // Center map to marker
            map.panTo(marker.getLatLng());
        }

        // ==========================================
        // UTILITIES / RESETS
        // ==========================================
        function cancelEditor() {
            currentMode = 'idle';
            document.getElementById('panel-idle').classList.remove('hidden');
            document.getElementById('panel-editor').classList.add('hidden');

            // Remove temporary marker
            if (tempMarker) {
                map.removeLayer(tempMarker);
                tempMarker = null;
            }

            resetSelectedMarkerVisuals();
            resetForms();
        }

        function resetSelectedMarkerVisuals() {
            if (activeMarker) {
                const loc = activeMarker.locationData;
                const details = loc.locationable;
                const type = details ? details.type : null;

                // Revert icon to normal
                activeMarker.setIcon(getMarkerIcon(loc.category, type));

                // Disable dragging & listeners
                activeMarker.dragging.disable();
                activeMarker.off('dragend');

                activeMarker = null;
            }
        }

        function resetForms() {
            // Reset inputs and methods in forms
            const culturalForm = document.getElementById('form-cultural');
            culturalForm.reset();
            culturalForm.action = "{{ route('admin.cultural-objects.store') }}";
            document.getElementById('method-cultural').innerHTML = '';
            document.getElementById('current-model-3d').innerHTML = '';
            document.getElementById('current-audio').innerHTML = '';
            document.getElementById('current-images').innerHTML = '';

            const umkmForm = document.getElementById('form-umkm');
            umkmForm.reset();
            umkmForm.action = "{{ route('admin.umkm.profile.store') }}";
            document.getElementById('method-umkm').innerHTML = '';

            const facilityForm = document.getElementById('form-facility');
            facilityForm.reset();
            facilityForm.action = "{{ route('admin.facilities.store') }}";
            document.getElementById('method-facility').innerHTML = '';
        }
    </script>
@endpush