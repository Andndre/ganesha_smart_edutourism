@extends('layouts.app')
@section('title', 'Rute Belanja UMKM - Penglipuran')
@section('header_title', 'Rute Belanja UMKM')

@push('styles')
    @include('user.umkm.partials.multi_recommended._styles')
@endpush

@section('content')
    @php $totalPrice = 0; @endphp
    <div class="px-4 pb-32 pt-6">
        <div class="mb-6">
            <h2 class="text-charcoal text-xl font-bold">Rute Belanja Anda</h2>
            <p class="mt-1 text-sm text-gray-500">Kami telah menemukan beberapa UMKM terdekat agar Anda mendapatkan semua
                pesanan Anda.</p>
        </div>

        <!-- Map Container -->
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-2 shadow-sm">
            @include('user.umkm.partials.multi_recommended._map')
        </div>

        @include('user.umkm.partials.multi_recommended._route_steps')
    </div>

    @include('user.umkm.partials.multi_recommended._sticky_cta')
@endsection

@push('scripts')
    @include('user.umkm.partials.multi_recommended._scripts')
@endpush
