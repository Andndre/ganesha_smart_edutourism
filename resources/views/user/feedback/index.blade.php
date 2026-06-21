@extends('layouts.app')
@section('title', 'Riwayat Penilaian Saya')
@section('header_title', 'Riwayat Penilaian')

@section('content')
<div class="mx-auto max-w-6xl px-5 py-6">
    @if ($feedbacks->isEmpty())
        {{-- Empty State --}}
        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-amber-50">
                <svg class="h-10 w-10 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
            </div>
            <h3 class="text-charcoal mb-2 text-xl font-bold">Belum Ada Penilaian</h3>
            <p class="mb-6 text-sm text-gray-500">Anda belum memberikan penilaian atau ulasan apapun.</p>
            <a href="{{ route('feedback') }}"
                class="bg-primary shadow-primary/30 inline-flex h-12 items-center gap-2 rounded-2xl px-6 font-bold text-white shadow-lg transition-all active:scale-[0.98]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Beri Penilaian Sekarang
            </a>
        </div>
    @else
        {{-- Header: Count + New Feedback Button --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-500">{{ $feedbacks->count() }} ulasan</p>
            <a href="{{ route('feedback') }}"
                class="bg-primary shadow-primary/30 inline-flex h-11 items-center gap-2 self-start rounded-2xl px-5 font-bold text-white shadow-lg transition-all active:scale-[0.98]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Buat Feedback Baru
            </a>
        </div>

        {{-- Feedback Grid --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($feedbacks as $fb)
                <a href="{{ route('feedback.show', $fb) }}"
                    class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition-all active:scale-[0.98]">
                    
                    {{-- Rating + Date --}}
                    <div class="mb-2 flex items-center justify-between">
                        <div class="flex items-center gap-0.5">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-5 w-5 {{ $i <= $fb->rating ? 'text-accent' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <span class="text-xs text-gray-400">{{ $fb->created_at->translatedFormat('d M Y') }}</span>
                    </div>

                    {{-- Comment Preview --}}
                    @if ($fb->comment)
                        <p class="text-charcoal mb-2 line-clamp-2 text-sm leading-relaxed">{{ $fb->comment }}</p>
                    @endif

                    {{-- Meta: Photos + Admin Reply Badge --}}
                    <div class="flex items-center gap-3">
                        @if ($fb->photos && count($fb->photos) > 0)
                            <span class="flex items-center gap-1 text-xs text-gray-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ count($fb->photos) }} foto
                            </span>
                        @endif
                        @if ($fb->admin_response)
                            <span class="flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                Dibalas
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Back to Profile --}}
    <div class="mt-6 text-center">
        <a href="{{ route('profile') }}"
            class="inline-flex h-12 items-center gap-2 rounded-2xl px-6 font-semibold text-gray-600 transition-all active:scale-[0.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Profil
        </a>
    </div>
</div>
@endsection
