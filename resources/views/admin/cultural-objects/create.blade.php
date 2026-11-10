@extends('layouts.dashboard')

@section('title', 'Tambah Objek Budaya')

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
        <h1 class="font-display text-charcoal mt-1 text-2xl font-bold">Tambah Objek Budaya</h1>
        <p class="mt-0.5 text-sm text-gray-500">Setelah disimpan, objek muncul di daftar dengan tombol <strong>Taruh di peta</strong> untuk menentukan koordinatnya (boleh dilewati untuk perkakas tanpa titik).</p>
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
@endpush
