@extends('layouts.app')
@section('title', 'Ulasan Saya')
@section('header_title', 'Ulasan Saya')

@section('content')
<div class="mx-auto max-w-2xl px-5 py-6">
    <!-- Rating Summary Card -->
    <div class="mb-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm text-center">
        <div class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-500">Penilaian Anda</div>
        <div class="mb-2 flex items-center justify-center gap-1">
            @for ($i = 1; $i <= 5; $i++)
                <svg class="h-10 w-10 {{ $i <= $feedback->rating ? 'text-accent' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            @endfor
        </div>
        
        @if ($feedback->comment)
            <p class="text-charcoal mt-4 text-sm leading-relaxed">{{ $feedback->comment }}</p>
        @endif

        @if ($feedback->reservation_id)
            <p class="mt-4 text-xs text-gray-400">
                Terkait dengan reservasi {{ $feedback->reservation_id }}
            </p>
        @endif
    </div>

    <!-- Photos Grid -->
    @if ($feedback->photos && count($feedback->photos) > 0)
        <div class="mb-6">
            <h3 class="text-charcoal mb-3 text-sm font-bold">Foto</h3>
            <div class="grid grid-cols-3 gap-2">
                @foreach ($feedback->photos as $photo)
                    <div class="aspect-square overflow-hidden rounded-2xl bg-gray-100">
                        <img src="{{ Storage::url($photo) }}" alt="Foto ulasan" class="h-full w-full object-cover">
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Admin Reply -->
    @if ($feedback->admin_response)
        <div class="mb-6 rounded-3xl border border-green-100 bg-green-50 p-5">
            <div class="mb-2 flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-200 text-green-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-green-800">Balasan dari Pengelola</p>
                    <p class="text-xs text-green-600">{{ $feedback->updated_at->diffForHumans() }}</p>
                </div>
            </div>
            <p class="text-sm leading-relaxed text-green-900">{{ $feedback->admin_response }}</p>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex flex-col gap-3">
        <a href="{{ route('feedback.edit', $feedback) }}"
            class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center rounded-2xl font-bold text-white shadow-lg transition-all active:scale-[0.98]">
            Edit Penilaian
        </a>
        <a href="{{ route('profile') }}"
            class="flex h-14 w-full items-center justify-center rounded-2xl font-semibold text-gray-600 transition-all active:scale-[0.98]">
            Kembali ke Profil
        </a>
    </div>
</div>
@endsection
