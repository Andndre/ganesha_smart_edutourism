@extends('layouts.app')
@section('title', $umkm->business_name . ' - Penglipuran')
@section('header_title', __('Detail UMKM'))

@section('content')
    @include('user.umkm.partials.recommended._styles')

    <div class="relative pb-32 lg:pb-8">
        <div class="mx-auto w-full max-w-5xl sm:px-6 lg:px-8 lg:py-8">
            <div class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-8">

                {{-- Kolom Kiri: Gambar (sticky di desktop) --}}
                <div class="lg:sticky lg:top-8">
                    @include('user.umkm.partials.show._image')
                </div>

                {{-- Kolom Kanan: Info + Produk + Peta --}}
                <div class="space-y-4 md:space-y-6 mt-4 lg:mt-0">
                    @include('user.umkm.partials.show._info')
                    @include('user.umkm.partials.show._products')
                    @include('user.umkm.partials.show._map_section')
                </div>

            </div>
        </div>
    </div>

    @include('user.umkm.partials.show._sticky_cta')
    @include('user.umkm.partials.show._product_modal')
@endsection
