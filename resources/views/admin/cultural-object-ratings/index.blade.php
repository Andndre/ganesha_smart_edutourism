@extends('layouts.dashboard')

@section('title', 'Rating Objek Budaya')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-6">
    <h1 class="mb-6 text-2xl font-bold text-charcoal">Rating Objek Budaya (Internal)</h1>
    <p class="mb-6 text-sm text-gray-500">Rating ini hanya terlihat oleh admin/pengelola, tidak ditampilkan ke publik.</p>

    @if (session('status'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Objek Dinilai</p>
            <p class="mt-1 text-2xl font-bold text-charcoal">{{ $stats['total_objects_rated'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Ulasan Masuk</p>
            <p class="mt-1 text-2xl font-bold text-charcoal">{{ $stats['total_ratings_count'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Rata-rata Rating Global</p>
            <p class="mt-1 text-2xl font-bold text-charcoal">{{ number_format($stats['global_avg_rating'], 1) }} / 5</p>
        </div>
    </div>

    <form method="GET" class="mb-6 flex flex-col gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama objek budaya..."
            class="w-full flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
        <select name="rating_filter" class="rounded-xl border border-gray-200 px-3 py-2.5 text-sm">
            <option value="">Semua Rating</option>
            <option value="high" {{ request('rating_filter') === 'high' ? 'selected' : '' }}>Rating Tinggi (&ge; 4.0)</option>
            <option value="low" {{ request('rating_filter') === 'low' ? 'selected' : '' }}>Rating Rendah (&le; 3.0)</option>
        </select>
        <select name="sort" class="rounded-xl border border-gray-200 px-3 py-2.5 text-sm">
            <option value="most_rated" {{ request('sort', 'most_rated') === 'most_rated' ? 'selected' : '' }}>Paling Banyak Diulas</option>
            <option value="highest" {{ request('sort') === 'highest' ? 'selected' : '' }}>Rating Tertinggi</option>
            <option value="lowest" {{ request('sort') === 'lowest' ? 'selected' : '' }}>Rating Terendah</option>
        </select>
        <button type="submit" class="bg-primary rounded-xl px-5 py-2.5 text-sm font-bold text-white">Terapkan</button>
    </form>

    @forelse ($objects as $object)
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-bold text-charcoal">{{ $object->name }}</h2>
                <span class="text-sm font-semibold text-gray-600">
                    {{ number_format($object->ratings_avg_rating, 1) }} / 5 ({{ $object->ratings_count }})
                </span>
            </div>
            <div class="space-y-3">
                @foreach ($object->ratings as $rating)
                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium">
                                {{ $rating->user->name ?? 'Pengguna dihapus' }}
                                <span class="font-normal text-gray-400">&middot; {{ $rating->created_at->diffForHumans() }}</span>
                            </span>
                            <div class="flex items-center gap-3">
                                <span>{{ str_repeat('★', $rating->rating) . str_repeat('☆', 5 - $rating->rating) }}</span>
                                <form action="{{ route('admin.cultural-object-ratings.destroy', $rating) }}" method="POST"
                                    onsubmit="return confirm('Hapus rating ini secara permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </div>
                        </div>
                        @if ($rating->comment)
                            <p class="mt-1 text-sm text-gray-600">{{ $rating->comment }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-gray-500">Belum ada rating masuk.</p>
    @endforelse

    {{ $objects->links() }}
</div>
@endsection
