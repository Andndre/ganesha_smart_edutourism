@extends('layouts.app')
@section('title', __('Favorit Saya'))
@section('header_title', __('Favorit Saya'))

@section('content')
    <div class="px-4 py-6">
        @php
            $items = Auth::user()->favoriteItems();
        @endphp

        @if($items->isEmpty())
            <x-empty-state
                title="{{ __('Belum Ada Favorit') }}"
                text="{{ __('Belum ada favorit. Kunjungi tempat dan tambahkan ke favorit!') }}"
                cta-url="{{ route('edutourism.index') }}"
                cta-text="{{ __('Mulai Jelajahi') }}">
                <x-slot:icon>
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </x-slot:icon>
            </x-empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($items as $item)
                    <x-place-card :item="$item" :favorited="true" />
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    function toggleFavorite(type, id, btn) {
        const card = btn.closest('.favorite-card');
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
            if (data.status === 'removed') {
                card.style.transition = 'opacity 0.3s ease';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
            }
        })
        .catch(e => console.error('Toggle error:', e));
    }
</script>
@endpush
