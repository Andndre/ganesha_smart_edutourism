@extends('layouts.app')
@section('title', __('Katalog UMKM - Penglipuran'))
@section('header_title', __('Katalog UMKM'))

@section('content')
    <div class="px-4 pb-40 pt-[calc(env(safe-area-inset-top)+6rem)]">
        @include('user.umkm.partials.index._alerts')

        <div class="mb-6">
            <h2 class="text-charcoal text-xl font-bold">{{ __('Jelajah UMKM') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Pilih satu atau lebih kategori yang Anda inginkan. Sistem kami akan membantu mencarikan lokasi UMKM yang memiliki produk tersebut.') }}</p>
        </div>

        <form action="{{ route('umkm.recommend') }}" method="POST">
            @csrf
            @include('user.umkm.partials.index._category_grid')

            @php
                $hasActiveSession = false;
                if (auth()->check() || session()->has('guest_token')) {
                    $hasActiveSession = \App\Models\RouteSession::where('status', 'active')
                        ->where(function ($q) {
                            $q->where('user_id', auth()->id())->orWhere('guest_token', session('guest_token'));
                        })
                        ->exists();
                }
            @endphp
            @include('user.umkm.partials.index._sticky_bar')
        </form>
    </div>

    @include('user.umkm.partials.index._multi_stop_modal')
    @include('user.umkm.partials.index._category_detail_modal')
    @include('user.umkm.partials.index._scripts')
@endsection
