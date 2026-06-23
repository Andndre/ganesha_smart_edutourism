@extends('layouts.dashboard')

@section('title', 'Paket Wisata')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Paket Wisata</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola paket wisata dan harga yang ditawarkan kepada pengunjung.</p>
    </div>
    <a href="{{ route('admin.packages.create') }}"
        class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Paket
    </a>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse ($packages as $pkg)
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md">
            <div class="mb-3 flex items-start justify-between">
                <h3 class="font-semibold text-charcoal">{{ $pkg->name }}</h3>
                @if ($pkg->is_active)
                    <span class="shrink-0 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-bold text-primary">Aktif</span>
                @else
                    <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-400">Nonaktif</span>
                @endif
            </div>
            <p class="text-sm text-gray-500 line-clamp-3 min-h-15">{{ $pkg->description }}</p>
            
            {{-- Inclusions --}}
            @if(is_array($pkg->inclusions) && count($pkg->inclusions) > 0)
                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach($pkg->inclusions as $inc)
                        <span class="text-[10px] bg-gray-50 text-gray-500 rounded px-1.5 py-0.5 border border-gray-100">{{ $inc }}</span>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 flex items-center justify-between border-t border-gray-50 pt-3">
                <div>
                    <p class="text-xl font-bold text-primary">Rp {{ number_format($pkg->price, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400">per orang @if($pkg->duration_hours) · {{ $pkg->duration_hours }} jam @endif</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-charcoal">{{ $pkg->sold_count ?? 0 }}</p>
                    <p class="text-xs text-gray-400">terjual</p>
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('admin.packages.edit', $pkg->id) }}" class="flex-1 text-center rounded-xl border border-gray-200 py-2 text-xs font-semibold text-gray-600 transition-colors hover:bg-gray-50">Edit</a>
                <form method="POST" action="{{ route('admin.packages.destroy', $pkg->id) }}" class="delete-form flex-1" data-confirm="{{ __('Apakah Anda yakin ingin menghapus paket ini?') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full rounded-xl border border-warning/30 py-2 text-xs font-semibold text-warning transition-colors hover:bg-warning/5">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-full rounded-2xl border border-dashed border-gray-200 p-8 text-center text-gray-400">
            Belum ada paket wisata. Klik "Tambah Paket" untuk membuat baru.
        </div>
    @endforelse
</div>

@endsection
