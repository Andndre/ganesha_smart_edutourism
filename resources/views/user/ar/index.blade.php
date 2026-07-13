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
        window.AR_MESSAGES = {
            touchToRotate: "{{ __('Sentuh untuk memutar/zoom') }}",
            qrNotRecognized: "{{ __('QR Tidak Dikenali!') }}",
            pointToQr: "{{ __('Arahkan ke Marker QR') }}",
            cameraNotFound: "{{ __('Kamera tidak ditemukan') }}",
            httpConnection: "{{ __('Koneksi HTTP') }}",
            browserNotSupported: "{{ __('Browser Tidak Didukung') }}",
            downloadingModel: "{{ __('Mengunduh Model...') }}",
            checkingQr: "{{ __('Memeriksa QR...') }}",
            success: "{{ __('Berhasil!') }}",
            arrivedAtLocation: "{{ __('Anda telah tiba di lokasi. Silakan selesaikan misi/kuis di lokasi ini.') }}",
            continue: "{{ __('Lanjut') }}",
            cameraDeniedBadge: "{{ __('Izin kamera ditolak / Tertahan') }}",
            cameraDeniedTitle: "{{ __('Akses Kamera Tertahan / Ditolak') }}",
            cameraDeniedBody: "{{ __('Browser membutuhkan izin Anda untuk mengaktifkan kamera. Ketuk tombol di bawah untuk meminta ulang izin akses kamera.') }}",
            cameraDeniedHint: "{{ __('Jika tetap tidak bisa, pastikan Anda telah memberikan Izin Kamera di pengaturan Google Chrome atau Safari Anda.') }}",
            cameraDeniedRetry: "{{ __('Izinkan Kamera & Coba Lagi') }}"
        };
        self.ModelViewerElement = self.ModelViewerElement || {};
        self.ModelViewerElement.meshoptDecoderLocation = 'https://cdn.jsdelivr.net/npm/meshoptimizer/meshopt_decoder.js';
    </script>
    <script type="module" src="{{ asset('js/model-viewer.min.js') }}"></script>

    <!-- Refactored AR Scanner Logic -->
    <script src="{{ asset('js/ar-scanner.js') }}?v={{ time() }}"></script>
@endpush
