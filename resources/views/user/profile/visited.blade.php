@extends('layouts.app')
@section('title', __('Riwayat Kunjungan'))
@section('header_title', __('Riwayat Kunjungan'))

@section('content')
    <div class="px-4 py-6">
        @php
            $visitedItems = Auth::user()->visitedItems();
        @endphp

        @if($visitedItems->isEmpty())
            <x-empty-state
                title="{{ __('Belum Ada Kunjungan') }}"
                text="{{ __('Mulai jelajahi rute Edutourism untuk mencatat kunjungan Anda!') }}"
                cta-url="{{ route('edutourism.index') }}"
                cta-text="{{ __('Mulai Jelajahi') }}">
                <x-slot:icon>
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </x-slot:icon>
            </x-empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($visitedItems as $item)
                    <x-place-card :item="$item" :favorited="Auth::user()->hasFavorited($item)" />
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    function toggleFavorite(type, id, btn) {
        const svg = btn.querySelector('svg');
        fetch('/favorites/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ favoritable_type: type, favoritable_id: id })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'added') {
                svg.classList.remove('text-gray-300');
                svg.classList.add('text-yellow-400', 'fill-current');
            } else {
                svg.classList.remove('text-yellow-400', 'fill-current');
                svg.classList.add('text-gray-300');
            }
        })
        .catch(e => console.error('Toggle error:', e));
    }
</script>
@endpush
