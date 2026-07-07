@extends('layouts.dashboard')

@section('title', 'Rating Objek Budaya')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-6">
    <h1 class="mb-6 text-2xl font-bold text-charcoal">Rating Objek Budaya (Internal)</h1>
    <p class="mb-6 text-sm text-gray-500">Rating ini hanya terlihat oleh admin/pengelola, tidak ditampilkan ke publik.</p>

    @forelse ($objects as $object)
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-bold text-charcoal">{{ $object->getTranslation('name', 'en') }}</h2>
                <span class="text-sm font-semibold text-gray-600">
                    {{ number_format($object->ratings_avg_rating, 1) }} / 5 ({{ $object->ratings_count }})
                </span>
            </div>
            <div class="space-y-3">
                @foreach ($object->ratings as $rating)
                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium">{{ $rating->user->name ?? 'Pengguna dihapus' }}</span>
                            <span>{{ str_repeat('★', $rating->rating) . str_repeat('☆', 5 - $rating->rating) }}</span>
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
