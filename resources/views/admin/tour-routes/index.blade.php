@extends('layouts.dashboard')

@section('title', 'Rute Wisata')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div id="tour-header">
        <h1 class="font-display text-2xl font-bold text-charcoal">Rute Wisata</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola jalur dan titik kunjungan yang direkomendasikan kepada wisatawan.</p>
    </div>
    <div class="flex items-center gap-2">
        <button id="tour-trigger-btn" onclick="startTutorial()"
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:bg-gray-100 active:scale-[0.98]"
            title="Panduan Interaktif">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </button>
        <a id="tour-add-btn" href="{{ route('admin.tour-routes.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Rute
        </a>
    </div>
</div>

{{-- Route Cards --}}
<div id="tour-table" class="grid grid-cols-1 gap-5 lg:grid-cols-2">
    @forelse ($routes as $route)
        <div @if ($loop->first) id="tour-first-card" @endif class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            {{-- Header --}}
            <div class="mb-4 flex items-start justify-between gap-2">
                <div>
                    <h3 class="font-semibold text-charcoal">{{ $route->name }}</h3>

                </div>
                @if ($route->is_active)
                    <span class="flex shrink-0 items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-bold text-primary">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary"></span> Aktif
                    </span>
                @else
                    <span class="flex shrink-0 items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Nonaktif
                    </span>
                @endif
            </div>

            {{-- Description --}}
            @if($route->description)
                <p class="mb-4 text-xs text-gray-500 line-clamp-2">{{ $route->description }}</p>
            @endif

            {{-- Meta --}}
            <div class="mb-4 flex gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @if ($route->estimated_duration_minutes < 60)
                        {{ $route->estimated_duration_minutes }} menit
                    @else
                        {{ round($route->estimated_duration_minutes / 60, 1) }} jam
                    @endif
                </span>
                <span class="flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    @if ($route->distance_meters < 1000)
                        {{ $route->distance_meters }} m
                    @else
                        {{ round($route->distance_meters / 1000, 1) }} km
                    @endif
                </span>
            </div>

            {{-- Waypoints --}}
            <div @if ($loop->first) id="tour-waypoints" @endif class="relative mb-4 pl-4">
                <div class="absolute left-1.5 top-2 bottom-2 w-px bg-gray-200"></div>
                @forelse ($route->routePoints as $i => $point)
                    <div class="relative mb-2 flex items-center gap-2">
                        <span class="absolute -left-3 flex h-3 w-3 items-center justify-center rounded-full
                            {{ $i === 0 || $i === count($route->routePoints) - 1
                                ? 'bg-primary'
                                : 'border-2 border-gray-300 bg-white' }}">
                        </span>
                        <p class="pl-2 text-sm {{ $i === 0 || $i === count($route->routePoints) - 1 ? 'font-semibold text-charcoal' : 'text-gray-500' }}">
                            {{ $point->locationable ? ($point->locationable->name ?? $point->locationable->business_name) : ($point->storytelling_content ?? 'Titik Kunjungan') }}
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic">Belum ada titik rute yang dikonfigurasi.</p>
                @endforelse
            </div>

            {{-- Actions --}}
            <div @if ($loop->first) id="tour-actions" @endif class="flex gap-2 border-t border-gray-50 pt-4">
                <a href="{{ route('admin.tour-routes.edit', $route->id) }}" class="flex-1 rounded-xl border border-gray-200 py-2 text-center text-xs font-semibold text-gray-600 transition-colors hover:bg-gray-50">
                    Edit Rute
                </a>
                <form method="POST" action="{{ route('admin.tour-routes.toggle', $route->id) }}" class="flex-1">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full rounded-xl border border-gray-200 py-2 text-xs font-semibold text-gray-600 transition-colors hover:bg-gray-50">
                        {{ $route->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.tour-routes.destroy', $route->id) }}" class="delete-form inline" data-confirm="{{ 'Apakah Anda yakin ingin menghapus rute ini?' }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-warning/20 px-3 py-2 text-xs font-semibold text-warning transition-colors hover:bg-warning/5">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div id="tour-empty-state" class="col-span-full rounded-2xl border border-dashed border-gray-200 p-8 text-center text-gray-400">
            Belum ada rute wisata. Klik "Tambah Rute" untuk memulai.
        </div>
    @endforelse
</div>

@endsection

@push('scripts')
    <script>
        function startTutorial() {
            const driver = window.driver.js.driver;
            const hasCard = document.getElementById('tour-first-card') !== null;
            const steps = [];

            // Langkah 1: Pengantar
            steps.push({
                element: '#tour-header',
                popover: {
                    title: '👋 Selamat Datang!',
                    description: 'Panduan ini akan menunjukkan cara mengelola Rute Wisata — jalur dan titik kunjungan yang direkomendasikan kepada wisatawan.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            // Langkah 2: Tombol Tambah Rute
            steps.push({
                element: '#tour-add-btn',
                popover: {
                    title: '➕ Tambah Rute Baru',
                    description: 'Gunakan tombol ini untuk membuat rute wisata baru beserta titik-titik kunjungan dan konten storytelling-nya.',
                    side: 'bottom',
                    align: 'end'
                }
            });

            if (hasCard) {
                // Langkah 3: Kartu Rute
                steps.push({
                    element: '#tour-first-card',
                    popover: {
                        title: '🗺️ Kartu Rute Wisata',
                        description: 'Setiap rute yang dibuat akan tampil di sini lengkap dengan status aktif, durasi, dan jarak tempuhnya.',
                        side: 'top',
                        align: 'start'
                    }
                });

                // Langkah 4: Waypoints
                steps.push({
                    element: '#tour-waypoints',
                    popover: {
                        title: '📍 Titik Kunjungan',
                        description: 'Daftar ini menampilkan urutan titik kunjungan (waypoint) dari awal hingga akhir rute, sesuai jalur yang akan dilalui wisatawan.',
                        side: 'top',
                        align: 'start'
                    }
                });

                // Langkah 5: Aksi Rute
                steps.push({
                    element: '#tour-actions',
                    popover: {
                        title: '⚙️ Aksi Rute',
                        description: 'Edit rute untuk mengatur titik kunjungan dan kontennya, aktifkan/nonaktifkan agar tampil ke wisatawan, atau hapus jika sudah tidak diperlukan.',
                        side: 'top',
                        align: 'end'
                    }
                });
            } else {
                // Langkah Alternatif jika kosong
                steps.push({
                    element: '#tour-empty-state',
                    popover: {
                        title: '📭 Belum Ada Data',
                        description: 'Setelah Anda menambahkan rute wisata pertama, kartu rute akan muncul di area ini.',
                        side: 'top',
                        align: 'start'
                    }
                });
            }

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
            const tourCompleted = localStorage.getItem('admin_tour_routes_tour_completed');
            if (!tourCompleted) {
                setTimeout(() => {
                    startTutorial();
                    localStorage.setItem('admin_tour_routes_tour_completed', 'true');
                }, 1000);
            }
        });
    </script>
@endpush
