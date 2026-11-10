@extends('layouts.dashboard')

@section('title', 'Rumah Adat')

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-xs font-bold uppercase tracking-[.18em] text-primary">Warisan Desa</p>
        <h1 class="font-display text-3xl font-bold text-charcoal">Rumah Adat</h1>
        <p class="mt-2 text-sm text-gray-600">Publikasikan pekarangan yang telah diverifikasi, hubungkan UMKM penghuninya, dan cetak satu penanda untuk setiap rumah.</p>
    </div>
    @if(auth()->user()?->isAdmin())
        <a href="{{ route('admin.houses.create') }}" class="tap-target rounded-full bg-primary px-5 text-sm font-bold text-white">Tambah Rumah</a>
    @endif
</div>

<form method="GET" action="{{ route('admin.houses.index') }}" class="mb-5 flex max-w-lg gap-2">
    <label class="sr-only" for="house-search">Cari rumah adat</label>
    <input id="house-search" name="q" value="{{ request('q') }}" placeholder="Nomor rumah atau nama kepala keluarga" class="min-h-11 min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-4 text-sm">
    <button class="tap-target rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold">Cari</button>
</form>
<div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
    <table class="w-full min-w-[650px] text-left text-sm">
        <thead class="bg-surface text-xs uppercase tracking-wider text-gray-500"><tr><th class="p-4">Rumah</th><th class="p-4">Kepala Keluarga</th><th class="p-4">UMKM</th><th class="p-4">Status</th><th class="p-4">Tindakan</th></tr></thead>
        <tbody class="divide-y divide-gray-100">
        @forelse($houses as $house)
            <tr>
                <td class="p-4 font-semibold">No. {{ $house->number }}</td>
                <td class="p-4">{{ $house->head_name }}</td>
                <td class="p-4">{{ $house->umkm_profiles_count }}</td>
                <td class="p-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $house->is_published ? 'bg-green-50 text-primary' : 'bg-amber-50 text-amber-800' }}">{{ $house->is_published ? 'Terbit' : 'Draf' }}</span></td>
                <td class="p-4"><a class="font-semibold text-primary underline-offset-4 hover:underline" href="{{ route('admin.houses.edit', $house) }}">{{ auth()->user()?->isAdmin() ? 'Ubah' : 'Lihat' }}</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="p-8 text-center text-gray-500">Belum ada rumah adat. Tambahkan data desa yang telah diverifikasi untuk memulai direktori.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $houses->links() }}</div>
@endsection
