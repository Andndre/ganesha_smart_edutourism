@extends('layouts.app')
@section('title', __('Profil Saya'))
@section('header_title', __('Profil Saya'))

@section('content')
    <div class="px-4 pb-24 pt-[calc(env(safe-area-inset-top)+6rem)]">

        @if (session('success'))
            <div
                class="mb-4 flex items-center gap-2 rounded-2xl border border-green-100 bg-green-50 p-4 text-sm text-green-700">
                <svg class="h-5 w-5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- User Info Card -->
        <div class="mb-6 flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-full border-2 border-white shadow-md">
                <img src="{{ Auth::user()->avatarUrl() }}" alt="Avatar {{ Auth::user()->name }}"
                    class="h-full w-full object-cover">
            </div>
            <div>
                <h2 class="text-charcoal text-xl font-bold">{{ Auth::user()->name }}</h2>
                <p class="text-sm text-gray-500">
                    {{ str(Auth::user()->email)->before('@')->substr(0, 2)->append('***@')->append(str(Auth::user()->email)->after('@')) }}
                </p>
                <div class="mt-1 flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    <span class="text-xs font-semibold text-green-600">{{ __('Akun Terverifikasi') }}</span>
                </div>
            </div>
        </div>

        <!-- Other Menu Options -->
        <h3 class="text-charcoal mb-4 text-lg font-bold">{{ __('Pengaturan') }}</h3>
        <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
            @if (Auth::user()->isUmkmOwner())
                <a href="{{ route('owner.dashboard') }}"
                    class="bg-primary/5 hover:bg-primary/10 flex items-center justify-between border-b border-gray-50 p-4 transition-colors active:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary/20 text-primary flex h-8 w-8 items-center justify-center rounded-full">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                            </svg>
                        </div>
                        <span class="text-charcoal text-sm font-semibold">{{ __('Panel Pemilik UMKM') }}</span>
                    </div>
                    <svg class="text-primary h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @endif

            <a href="{{ route('profile.edit') }}"
                class="flex items-center justify-between border-b border-gray-50 p-4 active:bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-50 text-green-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span class="text-charcoal text-sm font-medium">{{ __('Ubah Profil') }}</span>
                </div>
                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>

            @php
                $visitCount = Auth::user()->visits()->distinct('visitable_id')->count();
            @endphp

            <a href="{{ route('visited') }}"
                class="flex items-center justify-between border-b border-gray-50 p-4 active:bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-50 text-green-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-charcoal text-sm font-medium">{{ __('Riwayat Kunjungan') }}
                        @if ($visitCount > 0)
                            <span class="ml-1 text-[11px] font-bold text-green-600">({{ $visitCount }})</span>
                        @endif
                    </span>
                </div>
                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>

            <a href="{{ route('feedback.index') }}"
                class="flex items-center justify-between border-b border-gray-50 p-4 active:bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="text-accent flex h-8 w-8 items-center justify-center rounded-full bg-amber-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <span class="text-charcoal text-sm font-medium">{{ __('Riwayat Penilaian & Ulasan') }}</span>
                </div>
                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>

            <a href="{{ route('help') }}"
                class="flex items-center justify-between border-b border-gray-50 p-4 active:bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-50 text-sky-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <span class="text-charcoal text-sm font-medium">{{ __('Bantuan') }}</span>
                </div>
                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>

            <div class="p-4">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 text-red-500">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-red-500">{{ __('Keluar (Logout)') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection
