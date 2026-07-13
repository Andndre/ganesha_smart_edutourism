@extends('layouts.app')

@section('title', __('Peta Interaktif - Penglipuran Smart Tour'))

@section('content')
    <div class="absolute inset-0 z-0 overflow-hidden bg-[#E5E3DF]">
        <div id="map" class="absolute inset-0 z-0"></div>

        <!-- Heatmap Overlay Container -->

        @include('user.explore.components.map-search')
        @include('user.explore.components.map-fab')
    </div>

    <x-map-style-modal />
    @include('user.explore.components.location-sheet')


    @include('components.map-style-script')

    @include('user.explore.components.map-script')
@endsection
