@extends('layouts.dashboard')

@section('title', 'Pengaturan Desa')

@section('content')
    <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div id="tour-header">
            <h1 class="font-display text-3xl font-extrabold text-charcoal tracking-tight">Pengaturan Desa</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola jam operasional, ruang budaya seluruh rumah, dan sorotan beranda.</p>
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

    <div id="tour-section-hours" class="max-w-3xl rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label for="village-open-time" class="block text-sm font-semibold text-gray-700">Jam Buka <span class="text-warning">*</span></label>
                    <input id="village-open-time" type="time" name="open_time" required value="{{ old('open_time', $settings->open_time ? \Carbon\Carbon::parse($settings->open_time)->format('H:i') : '08:00') }}"
                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">
                    @error('open_time')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="village-close-time" class="block text-sm font-semibold text-gray-700">Jam Tutup <span class="text-warning">*</span></label>
                    <input id="village-close-time" type="time" name="close_time" required value="{{ old('close_time', $settings->close_time ? \Carbon\Carbon::parse($settings->close_time)->format('H:i') : '18:00') }}"
                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">
                    @error('close_time')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <fieldset class="mt-8 space-y-6 border-t border-gray-200 pt-6">
                <legend class="px-1 font-display text-xl font-bold text-charcoal">Budaya Pekarangan &amp; Unggulan</legend>
                <p class="text-sm leading-relaxed text-gray-600">Pilih sekali untuk seluruh desa. Kelola Angkul-angkul, Paon, Saka Enam, dan Merajan sebagai anak dari objek induk Pekarangan Rumah Adat.</p>

                <div>
                    <label for="house-cultural-object" class="mb-2 block text-sm font-semibold text-charcoal">Induk budaya untuk seluruh rumah</label>
                    <select id="house-cultural-object" name="house_cultural_object_id" aria-describedby="house-cultural-help @error('house_cultural_object_id') house-cultural-error @enderror" aria-invalid="{{ $errors->has('house_cultural_object_id') ? 'true' : 'false' }}"
                        class="min-h-12 w-full rounded-xl border border-gray-500 bg-white px-3 text-base focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                        <option value="">Belum ditentukan</option>
                        @foreach($culturalRoots as $root)
                            <option value="{{ $root->id }}" @selected((string) old('house_cultural_object_id', $settings->house_cultural_object_id) === (string) $root->id)>{{ translateValue($root->name, 'id') }} — {{ $root->children_count }} ruang budaya</option>
                        @endforeach
                    </select>
                    <p id="house-cultural-help" class="mt-2 text-sm text-gray-600">Semua rumah otomatis menampilkan objek anak dari induk ini, termasuk cerita, audio, dan model 3D/AR. Tidak perlu memilih objek per rumah.</p>
                    @error('house_cultural_object_id')<p id="house-cultural-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="featured-cultural-object" class="mb-2 block text-sm font-semibold text-charcoal">Objek budaya unggulan</label>
                    <select id="featured-cultural-object" name="featured_cultural_object_id" aria-describedby="featured-cultural-help @error('featured_cultural_object_id') featured-cultural-error @enderror" aria-invalid="{{ $errors->has('featured_cultural_object_id') ? 'true' : 'false' }}"
                        class="min-h-12 w-full rounded-xl border border-gray-500 bg-white px-3 text-base focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                        <option value="">Tidak ditampilkan di beranda</option>
                        @foreach($culturalRoots as $root)
                            <option value="{{ $root->id }}" @selected((string) old('featured_cultural_object_id', $settings->featured_cultural_object_id) === (string) $root->id)>{{ translateValue($root->name, 'id') }} — {{ $root->children_count }} ruang budaya</option>
                        @endforeach
                    </select>
                    <p id="featured-cultural-help" class="mt-2 text-sm text-gray-600">Ditampilkan di beranda dan urutan pertama halaman Objek Budaya. Pilih Pekarangan Rumah Adat untuk menyorot susunan ruang rumah. Pilihan ini tidak mengubah isi budaya setiap rumah.</p>
                    @error('featured_cultural_object_id')<p id="featured-cultural-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>

                @if($culturalRoots->isEmpty())
                    <p class="rounded-xl bg-surface p-4 text-sm text-gray-600">Belum ada objek budaya induk. Tambahkan Pekarangan Rumah Adat terlebih dahulu, lalu atur objek anaknya.</p>
                @endif
                <a href="{{ route('admin.cultural-objects') }}" class="inline-flex min-h-12 items-center text-sm font-semibold text-primary underline underline-offset-4">Kelola objek budaya dan susunan ruang</a>
            </fieldset>

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
                    description: 'Atur jam operasional, induk budaya untuk seluruh rumah, dan objek unggulan di beranda.',
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
