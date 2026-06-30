@extends('layouts.dashboard')

@section('title', 'Pengaturan Desa')

@section('content')
    <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div id="tour-header">
            <h1 class="font-display text-3xl font-extrabold text-charcoal tracking-tight">Pengaturan Desa</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola jam operasional Desa Wisata Penglipuran.</p>
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
        </div>
    </div>

    <div id="tour-section-hours" class="max-w-lg rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Jam Buka <span class="text-warning">*</span></label>
                    <input type="time" name="open_time" value="{{ old('open_time', $settings->open_time ? \Carbon\Carbon::parse($settings->open_time)->format('H:i') : '08:00') }}"
                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">
                    @error('open_time')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Jam Tutup <span class="text-warning">*</span></label>
                    <input type="time" name="close_time" value="{{ old('close_time', $settings->close_time ? \Carbon\Carbon::parse($settings->close_time)->format('H:i') : '18:00') }}"
                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">
                    @error('close_time')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex justify-end border-t border-gray-100 pt-6">
                <button id="tour-save-btn" type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function startTutorial() {
            const driver = window.driver.js.driver;
            const steps = [];

            // Langkah 1: Pengantar
            steps.push({
                element: '#tour-header',
                popover: {
                    title: '👋 Selamat Datang!',
                    description: 'Panduan ini akan menunjukkan cara mengatur jam operasional Desa Wisata Penglipuran.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            // Langkah 2: Jam Operasional
            if (document.getElementById('tour-section-hours') !== null) {
                steps.push({
                    element: '#tour-section-hours',
                    popover: {
                        title: '🕒 Jam Operasional',
                        description: 'Atur jam buka dan jam tutup desa wisata di sini. Jam ini akan digunakan sebagai acuan operasional bagi pengunjung dan sistem.',
                        side: 'top',
                        align: 'start'
                    }
                });
            }

            // Langkah 3: Simpan Perubahan
            if (document.getElementById('tour-save-btn') !== null) {
                steps.push({
                    element: '#tour-save-btn',
                    popover: {
                        title: '💾 Simpan Perubahan',
                        description: 'Jangan lupa klik tombol ini setelah mengubah jam operasional agar perubahan tersimpan.',
                        side: 'top',
                        align: 'end'
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
            const tourCompleted = localStorage.getItem('admin_settings_tour_completed');
            if (!tourCompleted) {
                setTimeout(() => {
                    startTutorial();
                    localStorage.setItem('admin_settings_tour_completed', 'true');
                }, 1000);
            }
        });
    </script>
@endpush
