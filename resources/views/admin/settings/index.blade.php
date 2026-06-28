@extends('layouts.dashboard')

@section('title', 'Pengaturan Desa')

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-3xl font-extrabold text-charcoal tracking-tight">Pengaturan Desa</h1>
        <p class="mt-1 text-sm text-gray-500">Kelola jam operasional Desa Wisata Penglipuran.</p>
    </div>

    <div class="max-w-lg rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
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
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
