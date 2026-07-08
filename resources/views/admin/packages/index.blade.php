@extends('layouts.dashboard')

@section('title', 'Paket Wisata')

@section('content')

    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div id="tour-header">
            <h1 class="font-display text-charcoal text-2xl font-bold">Paket Wisata</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola paket wisata dan harga yang ditawarkan kepada pengunjung.</p>
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
            <a id="tour-add-btn" href="{{ route('admin.packages.create') }}"
                class="bg-primary shadow-primary/20 hover:bg-primary-600 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Paket
            </a>
        </div>
    </div>

    <div id="tour-package-grid" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($packages as $pkg)
            <div @if ($loop->first) id="tour-first-card" @endif
                class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                <div class="mb-3 flex items-start justify-between">
                    <div>
                        <h3 class="text-charcoal font-semibold">{{ $pkg->name }}</h3>
                        <span
                            class="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-bold {{ $pkg->isTicket() ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600' }}">
                            {{ $pkg->isTicket() ? 'Entrance Ticket' : 'Tour Package' }}
                        </span>
                    </div>
                    @if ($pkg->is_active)
                        <span
                            class="bg-primary/10 text-primary shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold">Aktif</span>
                    @else
                        <span
                            class="shrink-0 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-400">Nonaktif</span>
                    @endif
                </div>
                <p class="min-h-15 line-clamp-3 text-sm text-gray-500">{{ $pkg->description }}</p>

                {{-- Inclusions --}}
                @if (\is_array($pkg->inclusions) && count($pkg->inclusions) > 0)
                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach ($pkg->inclusions as $inc)
                            <span
                                class="rounded border border-gray-100 bg-gray-50 px-1.5 py-0.5 text-[10px] text-gray-500">{{ $inc }}</span>
                        @endforeach
                    </div>
                @endif

                <div @if ($loop->first) id="tour-price" @endif
                    class="mt-4 flex items-center justify-between border-t border-gray-50 pt-3">
                    <div>
                        <p class="text-primary text-xl font-bold">Rp {{ number_format($pkg->price, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400">per orang @if ($pkg->duration_hours)
                                · {{ $pkg->duration_hours }} jam
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-charcoal text-sm font-bold">{{ $pkg->sold_count ?? 0 }}</p>
                        <p class="text-xs text-gray-400">terjual</p>
                    </div>
                </div>
                <div @if ($loop->first) id="tour-actions" @endif class="mt-4 flex gap-2">
                    <a href="{{ route('admin.packages.edit', $pkg->id) }}"
                        class="flex-1 rounded-xl border border-gray-200 py-2 text-center text-xs font-semibold text-gray-600 transition-colors hover:bg-gray-50">Edit</a>
                    <form method="POST" action="{{ route('admin.packages.destroy', $pkg->id) }}" class="delete-form flex-1"
                        data-confirm="{{ 'Apakah Anda yakin ingin menghapus paket ini?' }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="border-warning/30 text-warning hover:bg-warning/5 w-full rounded-xl border py-2 text-xs font-semibold transition-colors">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div id="tour-empty-state"
                class="col-span-full rounded-2xl border border-dashed border-gray-200 p-8 text-center text-gray-400">
                Belum ada paket wisata. Klik "Tambah Paket" untuk membuat baru.
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
                    description: 'Panduan ini akan menunjukkan cara mengelola paket wisata yang ditawarkan kepada pengunjung desa.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            // Langkah 2: Tombol Tambah Paket
            steps.push({
                element: '#tour-add-btn',
                popover: {
                    title: '➕ Tambah Paket Wisata',
                    description: 'Klik tombol ini untuk membuat paket wisata baru, lengkap dengan harga, durasi, dan fasilitas yang termasuk.',
                    side: 'bottom',
                    align: 'end'
                }
            });

            if (hasCard) {
                // Langkah 3: Kartu Paket
                steps.push({
                    element: '#tour-first-card',
                    popover: {
                        title: '📦 Kartu Paket Wisata',
                        description: 'Setiap paket yang dibuat akan tampil di sini, beserta status aktif/nonaktif dan fasilitas yang termasuk.',
                        side: 'top',
                        align: 'start'
                    }
                });

                // Langkah 4: Harga & Penjualan
                steps.push({
                    element: '#tour-price',
                    popover: {
                        title: '💰 Harga & Statistik Terjual',
                        description: 'Lihat harga per orang, durasi paket, dan jumlah paket yang sudah terjual di bagian ini.',
                        side: 'top',
                        align: 'start'
                    }
                });

                // Langkah 5: Edit & Hapus
                steps.push({
                    element: '#tour-actions',
                    popover: {
                        title: '⚙️ Aksi Cepat',
                        description: 'Gunakan tombol ini untuk mengubah detail paket atau menghapusnya jika sudah tidak ditawarkan lagi.',
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
                        description: 'Setelah Anda menambahkan paket wisata pertama, kartu paket akan muncul di area ini.',
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
            const tourCompleted = localStorage.getItem('admin_packages_tour_completed');
            if (!tourCompleted) {
                setTimeout(() => {
                    startTutorial();
                    localStorage.setItem('admin_packages_tour_completed', 'true');
                }, 1000);
            }
        });
    </script>
@endpush
