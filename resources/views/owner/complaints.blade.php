@extends('layouts.dashboard')

@section('title', 'Daftar Saran')

@section('content')
    <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between max-w-6xl">
        <div>
            <h1 class="font-display text-3xl font-extrabold text-charcoal tracking-tight">Saran Wisatawan</h1>
            <p class="mt-1 text-sm text-gray-500">Berikut adalah daftar masukan, saran, dan keluhan yang dikirimkan oleh wisatawan secara tertutup.</p>
        </div>
    </div>

    {{-- Ringkasan Stats --}}
    <div class="grid gap-6 sm:grid-cols-2 max-w-6xl mb-8">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Rata-rata Penilaian</p>
                    <p class="mt-2 font-display text-3xl font-bold text-charcoal">
                        {{ number_format($profile->rating ?? 5.0, 1) }} / 5.0
                    </p>
                </div>
                <div class="rounded-xl bg-amber-500/10 p-3.5 text-amber-500">
                    <svg class="h-6 w-6 fill-current" viewBox="0 0 20 20" style="color: #D4AF37;">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-gray-500">
                <span class="flex" style="color: #D4AF37;">
                    @for ($i = 1; $i <= 5; $i++)
                        {{ $i <= round($profile->rating ?? 5.0) ? '★' : '☆' }}
                    @endfor
                </span>
                <span>Nilai kepuasan pelayanan toko Anda</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Masukan</p>
                    <p class="mt-2 font-display text-3xl font-bold text-charcoal">
                        {{ $complaints->total() }}
                    </p>
                </div>
                <div class="rounded-xl bg-primary/10 p-3.5 text-primary">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-gray-500">
                <span>Jumlah semua saran & keluhan yang tercatat</span>
            </div>
        </div>
    </div>

    {{-- Daftar Saran --}}
    <div class="max-w-6xl">
        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                <h3 class="font-bold text-charcoal">Riwayat Masukan</h3>
            </div>

            @if ($complaints->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-50 text-gray-400">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-charcoal text-base font-bold">Belum ada keluhan masuk</h3>
                    <p class="mt-1 text-xs text-gray-500">Semua keluhan atau saran yang dikirimkan pengunjung akan tampil di sini.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($complaints as $complaint)
                        <div class="p-6 transition-all hover:bg-gray-50/30">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-charcoal">
                                        {{ $complaint->user ? $complaint->user->name : 'Wisatawan Anonim' }}
                                    </span>
                                    <span class="text-xs text-gray-400">&bull;</span>
                                    <span class="text-xs text-gray-400">
                                        {{ $complaint->created_at->format('d M Y H:i') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="flex" style="color: #D4AF37;">
                                        @for ($i = 1; $i <= 5; $i++)
                                            {{ $i <= $complaint->rating ? '★' : '☆' }}
                                        @endfor
                                    </span>
                                    <span class="text-xs font-semibold text-gray-500">({{ $complaint->rating }}/5)</span>
                                </div>
                            </div>

                            @if ($complaint->comment)
                                <p class="text-sm text-gray-700 leading-relaxed">{{ $complaint->comment }}</p>
                            @else
                                <p class="text-sm italic text-gray-400">Tidak ada komentar detail.</p>
                            @endif

                            {{-- Foto Lampiran --}}
                            @if ($complaint->photos && count($complaint->photos) > 0)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($complaint->photos as $photo)
                                        <a href="{{ asset('storage/' . $photo) }}" target="_blank" class="block h-16 w-16 overflow-hidden rounded-xl border border-gray-100 bg-gray-50 hover:opacity-90">
                                            <img src="{{ asset('storage/' . $photo) }}" class="h-full w-full object-cover" alt="Lampiran keluhan">
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Tanggapan Admin --}}
                            @if ($complaint->admin_response)
                                <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50/50 p-4">
                                    <div class="flex items-center gap-2 mb-1">
                                        <svg class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-xs font-bold text-blue-900">Tanggapan Pengelola Desa</span>
                                    </div>
                                    <p class="text-xs text-blue-800">{{ $complaint->admin_response }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($complaints->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        {{ $complaints->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
