@extends('layouts.app')
@section('title', 'Rekomendasi UMKM - Penglipuran')
@section('header_title', 'Rekomendasi UMKM')

@section('content')
    @include('user.umkm.partials.recommended._styles')
    <div class="relative pb-32">
        @include('user.umkm.partials.recommended._header')
        @include('user.umkm.partials.recommended._info_card')
        @include('user.umkm.partials.recommended._map_section')
        @include('user.umkm.partials.recommended._products')
    </div>

    @include('user.umkm.partials.recommended._sticky_cta')
    @include('user.umkm.partials.recommended._product_modal')
    @include('user.umkm.partials.recommended._scripts')
@endsection
