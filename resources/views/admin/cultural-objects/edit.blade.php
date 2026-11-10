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
        <a href="{{ route('admin.cultural-objects') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-charcoal">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            <span>Kembali</span>
        </a>
        <h1 class="font-display text-charcoal mt-1 text-2xl font-bold">Edit Objek Budaya</h1>
        <p class="mt-0.5 text-sm text-gray-500">Titik lokasi di peta diatur lewat <a href="{{ route('admin.map-manager') }}" class="text-primary underline">Peta Lokasi & Titik</a> (mengubah konten di sini tidak memindahkan titik yang sudah ada).</p>
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

            @if (!empty($object->audio_narration_paths['id']))
                window.setMiniAudio('current-audio-id', @json($object->audio_narration_paths['id']));
            @endif
            @if (!empty($object->audio_narration_paths['en']))
                window.setMiniAudio('current-audio-en', @json($object->audio_narration_paths['en']));
            @endif
        });
    </script>
@endpush
