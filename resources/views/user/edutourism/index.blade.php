@extends('layouts.app')
@section('title', __('Smart Edutourism - Penglipuran'))
@section('header_title', 'Smart Edutourism')

@section('content')
    <div class="min-h-screen space-y-8 bg-surface px-4 py-6" x-data>

        {{-- Header --}}
        <div class="relative text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 shadow-sm">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
            </div>
            <h2 class="font-display text-charcoal text-2xl font-bold md:text-3xl">{{ __('Jelajahi Penglipuran') }}</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-gray-500">
                {{ __('Tiga rute interaktif untuk memandu penjelajahan budaya Anda di Desa Penglipuran.') }}
            </p>
        </div>

        {{-- Route Journey --}}
        <div class="relative">
            {{-- Connector line: vertical on mobile, horizontal on desktop --}}
            <div
                class="absolute left-8 top-0 bottom-0 w-px bg-gradient-to-b from-emerald-200 via-emerald-300 to-transparent md:left-0 md:right-0 md:top-12 md:h-px md:w-auto md:bg-gradient-to-r">
            </div>

            <div class="relative grid grid-cols-1 gap-6 md:grid-cols-3 md:gap-5">
                @forelse($routes as $index => $route)
                    @php
                        $isCompleted = $completedRouteIds->contains($route['id']);
                        $number = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                    @endphp

                    <article
                        class="group relative overflow-hidden rounded-[2rem] border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg {{ $isCompleted ? 'ring-1 ring-emerald-100' : '' }}">

                        {{-- Large watermark number --}}
                        <span
                            class="font-display pointer-events-none absolute -right-2 -top-3 text-8xl font-bold text-gray-50 transition-colors group-hover:text-emerald-50/80 md:text-7xl lg:text-8xl">
                            {{ $number }}
                        </span>

                        {{-- Top row: status + icon --}}
                        <div class="relative flex items-start justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($isCompleted)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700 shadow-sm">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ __('Selesai') }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-gray-500">
                                        {{ __('Rute') }} {{ $number }}
                                    </span>
                                @endif
                            </div>

                            <div
                                class="shrink-0 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-3 text-white shadow-md transition-transform group-hover:scale-105">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="relative mt-4">
                            <h3 class="font-display text-charcoal text-xl font-bold leading-tight md:text-lg lg:text-xl">
                                {{ $route['name'] }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-gray-500">
                                {{ $route['description'] ?? __('Nikmati petualangan mendalam menelusuri tradisi dan kearifan lokal.') }}
                            </p>
                        </div>

                        {{-- Stats as compact chips --}}
                        <div class="relative mt-5 flex flex-wrap gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700">
                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $route['estimated_duration_minutes'] ?? 60 }} {{ __('Menit') }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700">
                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ $route['route_points_count'] ?? 0 }} {{ __('Titik') }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700">
                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                {{ $route['distance_meters'] ? round($route['distance_meters'] / 1000, 1) . ' km' : '--' }}
                            </span>
                        </div>

                        {{-- CTA --}}
                        <button
                            @click="@auth $dispatch('open-route-preview-modal'); fetchRoutePreview({{ $route['id'] }}) @else $dispatch('open-save-progress-confirm-modal'); window.pendingRouteId = {{ $route['id'] }} @endauth; if (navigator.vibrate) navigator.vibrate(40)"
                            class="relative mt-6 w-full rounded-2xl py-3.5 text-center text-sm font-bold shadow-sm transition-all active:scale-95 {{ $isCompleted ? 'border-2 border-emerald-600 bg-white text-emerald-700 hover:bg-emerald-50' : 'border-2 border-[#1E5128] bg-white text-[#1E5128] hover:bg-emerald-50' }}">
                            {{ $isCompleted ? __('Ulangi Eksplorasi') : __('Mulai Jelajah') }}
                        </button>
                    </article>
                @empty
                    <div class="relative col-span-full rounded-[2rem] border border-dashed border-gray-300 bg-white p-10 text-center">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                        </div>
                        <h4 class="font-display text-charcoal text-lg font-bold">{{ __('Belum Ada Rute Aktif') }}</h4>
                        <p class="mx-auto mt-2 max-w-xs text-sm text-gray-500">
                            {{ __('Saat ini belum ada jalur wisata edukasi yang dapat ditampilkan.') }}
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Footer note --}}
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4 text-center">
            <p class="text-xs font-medium text-emerald-800">
                {{ __('Setiap langkah adalah cerita. Pilih rute dan mulai petualangan budaya Anda.') }}
            </p>
        </div>
    </div>

    @push('modals')
        <!-- Route Preview Modal -->
        <x-modal name="route-preview-modal">
            <div class="space-y-5">
                <div class="flex items-center justify-between">
                    <span
                        class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[10px] font-extrabold uppercase tracking-wide text-emerald-600">
                        {{ __('Smart Edutourism') }}
                    </span>
                    <button type="button" @click="isOpen = false"
                        class="tap-target flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:text-gray-600 md:hidden"
                        title="{{ __('Tutup') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div>
                    <h3 id="preview-title"
                        class="font-display text-charcoal text-2xl font-black leading-snug tracking-tight">
                        {{ __('Memuat...') }}</h3>
                    <p id="preview-desc" class="mt-2 text-sm leading-relaxed text-gray-500"></p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <h4 class="mb-4 text-xs font-bold uppercase tracking-wide text-gray-400">
                        {{ __('Titik Perhentian') }}</h4>
                    <ul id="preview-points" class="space-y-3">
                        <li class="flex items-center gap-3 text-sm text-gray-500">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                            {{ __('Memuat rute...') }}
                        </li>
                    </ul>
                </div>

                <div id="preview-avatar-picker" class="hidden">
                    <h4 class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-400">{{ __('Pilih Avatarmu') }}
                    </h4>
                    <div id="preview-avatar-options" class="grid grid-cols-2 gap-3"></div>
                </div>

                <form id="start-route-form" method="POST" action="">
                    @csrf
                    <button type="button" onclick="startRoute()" id="btn-start-route" disabled
                        class="tap-target mt-2 w-full rounded-2xl bg-[#1E5128] py-4 text-center text-sm font-bold text-white shadow-lg shadow-emerald-900/10 transition-all active:scale-95 disabled:opacity-50">
                        {{ __('Mulai Eksplorasi') }}
                    </button>
                </form>
            </div>
        </x-modal>

        <!-- Save Progress Confirmation Modal -->
        <x-modal name="save-progress-confirm-modal">
            <div class="space-y-5 py-2 text-center">
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 shadow-sm">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>

                <div>
                    <h3 class="font-display text-charcoal text-xl font-black leading-snug tracking-tight">
                        {{ __('Simpan Progres Perjalanan Anda?') }}</h3>
                    <p class="mt-2 px-2 text-sm leading-relaxed text-gray-500">
                        {{ __('Anda dapat memulai rute ini tanpa akun. Namun, jika Anda masuk terlebih dahulu, poin dan misi perjalanan Anda akan tersimpan secara permanen di akun Anda.') }}
                    </p>
                </div>

                <div class="space-y-3 pt-2">
                    <a href="{{ route('login') }}?redirect={{ urlencode(route('edutourism.index')) }}"
                        class="tap-target block w-full rounded-2xl bg-[#1E5128] py-3.5 text-center text-sm font-bold text-white shadow-md transition-all hover:bg-[#152E1D] active:scale-95">
                        {{ __('Masuk & Simpan') }}
                    </a>
                    <button type="button" onclick="continueAsGuest()"
                        class="tap-target w-full rounded-2xl border border-gray-200 py-3.5 text-center text-sm font-bold text-gray-600 transition-all hover:bg-gray-50 active:scale-95">
                        {{ __('Lanjut Tanpa Akun') }}
                    </button>
                </div>
            </div>
        </x-modal>
    @endpush

    @push('scripts')
        <script>
            window.selectedAvatar = null;

            function selectAvatar(key) {
                window.selectedAvatar = key;
                document.querySelectorAll('#preview-avatar-options button').forEach(btn => {
                    const isSelected = btn.dataset.avatar === key;
                    btn.className = isSelected
                        ? 'rounded-2xl border-2 border-[#1E5128] bg-emerald-50 p-3 text-center text-xs font-bold text-[#1E5128] transition-all active:scale-95'
                        : 'rounded-2xl border-2 border-gray-200 bg-white p-3 text-center text-xs font-semibold text-gray-700 transition-all hover:border-gray-300 active:scale-95';
                });
            }

            function fetchRoutePreview(id) {
                document.getElementById('preview-title').textContent = '{{ __('Memuat...') }}';
                document.getElementById('preview-desc').textContent = '';
                document.getElementById('preview-points').innerHTML = `
                    <li class="flex items-center gap-3 text-sm text-gray-500">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                        {{ __('Memuat rute...') }}
                    </li>`;
                document.getElementById('btn-start-route').disabled = true;
                document.getElementById('start-route-form').action = `/edutourism/routes/${id}/start`;
                window.selectedAvatar = null;
                document.getElementById('preview-avatar-picker').classList.add('hidden');
                document.getElementById('preview-avatar-options').innerHTML = '';

                fetch(`/edutourism/routes/${id}/preview`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('preview-title').textContent = data.route.name;
                        document.getElementById('preview-desc').textContent = data.route.description ||
                            `{{ __('Estimasi') }} ${data.route.estimated_duration_minutes} {{ __('Menit') }}`;

                        const ul = document.getElementById('preview-points');
                        ul.innerHTML = '';

                        if (data.points && data.points.length > 0) {
                            data.points.forEach((pt, index) => {
                                ul.innerHTML += `
                                <li class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">${index + 1}</div>
                                    <span class="text-sm font-medium text-gray-700">${pt.name}</span>
                                </li>
                            `;
                            });
                        } else {
                            ul.innerHTML = '<li class="text-sm text-gray-500">{{ __('Tidak ada titik perhentian.') }}</li>';
                        }

                        if (data.avatar_options && data.avatar_options.length > 0) {
                            const wrap = document.getElementById('preview-avatar-options');
                            data.avatar_options.forEach(av => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.dataset.avatar = av.key;
                                btn.className = 'rounded-2xl border-2 border-gray-200 bg-white p-3 text-center text-xs font-semibold text-gray-700 transition-all hover:border-gray-300 active:scale-95';
                                btn.innerHTML = `<span class="mb-1 block text-2xl">${av.icon}</span>${av.label}`;
                                btn.onclick = () => selectAvatar(av.key);
                                wrap.appendChild(btn);
                            });
                            document.getElementById('preview-avatar-picker').classList.remove('hidden');
                        }

                        document.getElementById('btn-start-route').disabled = false;
                    })
                    .catch(err => {
                        document.getElementById('preview-title').textContent = '{{ __('Gagal memuat data') }}';
                        document.getElementById('preview-points').innerHTML =
                            '<li class="text-sm text-red-500">{{ __('Gagal memuat titik perhentian.') }}</li>';
                    });
            }

            function startRoute() {
                const form = document.getElementById('start-route-form');
                const btn = document.getElementById('btn-start-route');
                btn.disabled = true;
                btn.textContent = '{{ __('Memulai...') }}';

                fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            avatar: window.selectedAvatar
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.message || '{{ __('Terjadi kesalahan.') }}');
                            btn.disabled = false;
                            btn.textContent = '{{ __('Mulai Eksplorasi') }}';
                        }
                    })
                    .catch(err => {
                        alert('{{ __('Gagal memulai rute.') }}');
                        btn.disabled = false;
                        btn.textContent = '{{ __('Mulai Eksplorasi') }}';
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
