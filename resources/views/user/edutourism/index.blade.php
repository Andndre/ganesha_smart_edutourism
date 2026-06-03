@extends('layouts.app')
@section('title', 'Smart Edutourism - Penglipuran')
@section('header_title', 'Smart Edutourism')

@section('content')
<div class="space-y-5 px-4 py-6" x-data>

    <div class="mb-4">
        <h2 class="font-display text-charcoal text-xl font-bold">Jalur Edukasi & Budaya</h2>
        <p class="mt-1 text-sm text-gray-500">Pilih rute interaktif untuk memandu penjelajahan budaya Anda di Desa Penglipuran.</p>
    </div>

    <div class="space-y-4">
        @forelse($routes as $route)
            <div class="flex flex-col justify-between rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($route->is_smart_route)
                                <span class="rounded-lg bg-blue-50 border border-blue-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-600">
                                    Smart GPS
                                </span>
                            @endif
                        </div>
                        
                        <h3 class="font-display text-charcoal text-lg font-bold mt-1.5 leading-tight">{{ $route->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $route->description ?? 'Nikmati petualangan mendalam menelusuri tradisi dan kearifan lokal.' }}</p>
                    </div>

                    <div class="bg-emerald-500/10 text-emerald-700 rounded-2xl p-3 shrink-0">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 border-t border-gray-50 mt-4 pt-4 text-center">
                    <div class="flex flex-col items-center">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider leading-none">Estimasi</span>
                        <span class="text-sm font-black text-gray-700 mt-1">{{ $route->estimated_duration_minutes ?? 60 }} Menit</span>
                    </div>
                    <div class="flex flex-col items-center border-x border-gray-100">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider leading-none">Objek</span>
                        <span class="text-sm font-black text-gray-700 mt-1">{{ $route->route_points_count ?? 0 }} Titik</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider leading-none">Jarak</span>
                        <span class="text-sm font-black text-gray-700 mt-1">{{ $route->distance_meters ? round($route->distance_meters / 1000, 1) . ' km' : '--' }}</span>
                    </div>
                </div>

                <button @click="@auth $dispatch('open-route-preview-modal'); fetchRoutePreview({{ $route->id }}) @else $dispatch('open-save-progress-confirm-modal'); window.pendingRouteId = {{ $route->id }} @endauth"
                    class="bg-[#1E5128] hover:bg-[#152E1D] mt-5 block w-full rounded-2xl py-3 text-center text-sm font-bold text-white transition-transform active:scale-95 shadow-sm">
                    Mulai Jelajah
                </button>
            </div>
        @empty
            <div class="rounded-3xl border border-dashed border-gray-200 bg-white p-8 text-center">
                <div class="mx-auto w-12 h-12 text-gray-400 mb-3 flex items-center justify-center bg-gray-50 rounded-full">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-gray-700">Belum Ada Rute Aktif</h4>
                <p class="text-xs text-gray-500 mt-1">Saat ini belum ada jalur wisata edukasi yang dapat ditampilkan.</p>
            </div>
        @endforelse
    </div>

</div>

@push('modals')
    <!-- Route Preview Modal -->
    <x-modal name="route-preview-modal">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-emerald-600">Smart Edutourism</span>
                <button type="button" @click="isOpen = false" class="text-xs font-bold text-gray-400 hover:text-gray-600 md:hidden">Tutup</button>
            </div>
            
            <h3 id="preview-title" class="font-display text-charcoal text-xl font-black leading-snug tracking-tight">Memuat...</h3>
            <p id="preview-desc" class="text-sm text-gray-500"></p>
            
            <div class="mt-4 rounded-xl border border-gray-100 bg-gray-50 p-4 max-h-[300px] overflow-y-auto">
                <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">Titik Perhentian</h4>
                <ul id="preview-points" class="space-y-3">
                    <li class="text-sm text-gray-500">Memuat rute...</li>
                </ul>
            </div>
            
            <form id="start-route-form" method="POST" action="">
                @csrf
                <button type="button" onclick="startRoute()" id="btn-start-route" disabled class="bg-[#1E5128] mt-6 w-full rounded-xl py-3 text-center text-sm font-bold text-white shadow-sm transition-transform active:scale-95 disabled:opacity-50">
                    Mulai Eksplorasi
                </button>
            </form>
        </div>
    </x-modal>

    <!-- Save Progress Confirmation Modal -->
    <x-modal name="save-progress-confirm-modal">
        <div class="space-y-4 text-center py-2">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 shadow-sm">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            
            <h3 class="font-display text-charcoal text-lg font-black leading-snug tracking-tight mt-3">Simpan Progres Perjalanan Anda?</h3>
            <p class="text-xs text-gray-500 leading-relaxed px-2">Anda dapat memulai rute ini tanpa akun. Namun, jika Anda masuk (login) terlebih dahulu, poin dan misi perjalanan Anda akan tersimpan secara permanen di akun Anda.</p>
            
            <div class="space-y-2.5 pt-4">
                <a href="{{ route('login') }}?redirect={{ urlencode(route('edutourism.index')) }}" class="bg-[#1E5128] hover:bg-[#152E1D] block w-full rounded-xl py-3 text-center text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
                    Masuk & Simpan
                </a>
                <button type="button" onclick="continueAsGuest()" class="w-full rounded-xl border border-gray-200 py-3 text-center text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors active:scale-95">
                    Lanjut Tanpa Akun
                </button>
            </div>
        </div>
    </x-modal>
@endpush

@push('scripts')
    <script>
        function fetchRoutePreview(id) {
            document.getElementById('preview-title').textContent = 'Memuat...';
            document.getElementById('preview-desc').textContent = '';
            document.getElementById('preview-points').innerHTML = '<li class="text-sm text-gray-500">Memuat rute...</li>';
            document.getElementById('btn-start-route').disabled = true;
            document.getElementById('start-route-form').action = `/edutourism/routes/${id}/start`;

            fetch(`/edutourism/routes/${id}/preview`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('preview-title').textContent = data.route.name;
                    document.getElementById('preview-desc').textContent = data.route.description || `Estimasi ${data.route.estimated_duration_minutes} Menit`;
                    
                    const ul = document.getElementById('preview-points');
                    ul.innerHTML = '';
                    
                    if (data.points && data.points.length > 0) {
                        data.points.forEach((pt, index) => {
                            ul.innerHTML += `
                                <li class="flex items-center gap-3">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">${index + 1}</div>
                                    <span class="text-sm font-medium text-gray-700">${pt.name}</span>
                                </li>
                            `;
                        });
                    } else {
                        ul.innerHTML = '<li class="text-sm text-gray-500">Tidak ada titik perhentian.</li>';
                    }
                    
                    document.getElementById('btn-start-route').disabled = false;
                })
                .catch(err => {
                    document.getElementById('preview-title').textContent = 'Gagal memuat data';
                });
        }

        function startRoute() {
            const form = document.getElementById('start-route-form');
            const btn = document.getElementById('btn-start-route');
            btn.disabled = true;
            btn.textContent = 'Memulai...';

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Terjadi kesalahan.');
                    btn.disabled = false;
                    btn.textContent = 'Mulai Eksplorasi';
                }
            })
            .catch(err => {
                alert('Gagal memulai rute.');
                btn.disabled = false;
                btn.textContent = 'Mulai Eksplorasi';
            });
        }

        window.pendingRouteId = null;

        function continueAsGuest() {
            window.dispatchEvent(new CustomEvent('close-save-progress-confirm-modal'));
            if (window.pendingRouteId) {
                window.dispatchEvent(new CustomEvent('open-route-preview-modal'));
                fetchRoutePreview(window.pendingRouteId);
            }
        }
    </script>
@endpush
@endsection
