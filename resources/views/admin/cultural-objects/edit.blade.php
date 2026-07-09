@extends('layouts.dashboard')

@section('title', 'Edit Objek Budaya')

@push('styles')
    <style>
        .model-viewer-wrapper { min-height: 200px; }
        model-viewer { width: 100%; height: 200px; }
    </style>
@endpush

@section('content')

    <div class="mb-6">
        <a href="{{ route('admin.cultural-objects') }}" class="text-sm text-gray-500 hover:text-charcoal">&larr; Kembali</a>
        <h1 class="font-display text-charcoal mt-1 text-2xl font-bold">Edit Objek Budaya</h1>
        <p class="mt-0.5 text-sm text-gray-500">Titik lokasi di peta diatur lewat <a href="{{ route('admin.map-manager') }}" class="text-primary underline">Peta Lokasi & Titik</a> — mengubah konten di sini tidak memindahkan titik yang sudah ada.</p>
    </div>

    @include('admin.cultural-objects.partials.form')

    @include('admin.ar-manager.partials.modal-form')

@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <script type="module" src="{{ asset('js/model-viewer.min.js') }}"></script>
    <x-tiptap-editor-script />
    @include('admin.map-manager.partials.scripts.ar')
    @include('admin.map-manager.partials.scripts.ar-model-modal')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if ($object->arModel)
                window.dispatchEvent(new CustomEvent('ar-model-select', {
                    detail: { modelId: '{{ $object->arModel->id }}' }
                }));
            @endif
        });
    </script>
@endpush
