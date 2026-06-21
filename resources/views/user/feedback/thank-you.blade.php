@extends('layouts.app')
@section('title', 'Ulasan Terkirim')
@section('header_title', 'Terima Kasih!')

@section('content')
<div class="mx-auto max-w-2xl px-5 py-6 text-center">
    <div class="mb-6 flex justify-center">
        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-green-100">
            <svg class="h-12 w-12 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
    </div>
    <h2 class="text-charcoal mb-2 text-2xl font-bold">Terima Kasih!</h2>
    <p class="text-gray-500 mb-8">Ulasan Anda sangat berharga untuk pengembangan Desa Wisata Penglipuran.</p>
    
    <div class="mb-8 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-500">Ringkasan Penilaian</div>
        <div class="mb-2 flex items-center justify-center gap-1">
            @for ($i = 1; $i <= 5; $i++)
                <svg class="h-8 w-8 {{ $i <= $feedback->rating ? 'text-accent' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            @endfor
        </div>
        @if ($feedback->comment)
            <p class="text-charcoal text-sm">{{ $feedback->comment }}</p>
        @endif
    </div>

    <div class="flex flex-col gap-3">
        <a href="{{ route('feedback.show', $feedback) }}"
            class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center rounded-2xl font-bold text-white shadow-lg transition-all active:scale-[0.98]">
            Lihat Ulasan Saya
        </a>
        <a href="{{ route('home') }}"
            class="flex h-14 w-full items-center justify-center rounded-2xl font-semibold text-gray-600 transition-all active:scale-[0.98]">
            Kembali ke Beranda
        </a>
    </div>
</div>
@endsection
