@extends('layouts.scanner')

@section('title', 'AR Scanner')

@section('content')
    <!-- Partials for Header & Back Button -->
    @include('user.ar.partials.header')

    <!-- Partials for Camera Scanner -->
    @include('user.ar.partials.scanner-view')

    <!-- Partials for 3D Model Viewer -->
    @include('user.ar.partials.model-view')

    <!-- Partials for Loading Overlay -->
    @include('user.ar.partials.loading-overlay')
@endsection

@push('scripts')
    <!-- HTML5 QR Code Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <!-- Model Viewer & Meshopt Decoder -->
    <script>
        self.ModelViewerElement = self.ModelViewerElement || {};
        self.ModelViewerElement.meshoptDecoderLocation = 'https://cdn.jsdelivr.net/npm/meshoptimizer/meshopt_decoder.js';
    </script>
    <script type="module" src="{{ asset('js/model-viewer.min.js') }}"></script>

    <!-- Refactored AR Scanner Logic -->
    <script src="{{ asset('js/ar-scanner.js') }}?v={{ time() }}"></script>
@endpush
