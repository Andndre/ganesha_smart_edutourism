@extends('layouts.dashboard')

@section('title', 'Tambah Fasilitas')

@section('content')

    <div class="mb-6">
        <a href="{{ route('admin.facilities') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-charcoal">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            <span>Kembali</span>
        </a>
        <h1 class="font-display text-charcoal mt-1 text-2xl font-bold">Tambah Fasilitas</h1>
    </div>

    @include('admin.facilities.partials.form')

@endsection
