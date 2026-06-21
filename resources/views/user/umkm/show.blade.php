@extends('layouts.app')
@section('title', 'Detail Produk - Penglipuran')
@section('header_title', 'Detail Produk')

@section('content')
    <div class="relative pb-32">
        @include('user.umkm.partials.show._image')
        @include('user.umkm.partials.show._info')
        @include('user.umkm.partials.show._store_info')
        @include('user.umkm.partials.show._description')
    </div>

    @include('user.umkm.partials.show._sticky_cta')
@endsection
