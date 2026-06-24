@extends('layouts.app')
@section('title', $umkm->business_name . ' - Penglipuran')
@section('header_title', __('Detail UMKM'))

@section('content')
    @include('user.umkm.partials.recommended._styles')

    <div class="relative pb-32">
        @include('user.umkm.partials.show._image')
        @include('user.umkm.partials.show._info')
        @include('user.umkm.partials.show._map_section')
        @include('user.umkm.partials.show._products')
    </div>

    @include('user.umkm.partials.show._sticky_cta')
    @include('user.umkm.partials.show._product_modal')
@endsection
