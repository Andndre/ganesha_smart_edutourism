@extends('layouts.dashboard')

@section('title', 'Tambah Fasilitas')

@section('content')

    <div class="mb-6">
        <a href="{{ route('admin.facilities') }}" class="text-sm text-gray-500 hover:text-charcoal">&larr; Kembali</a>
        <h1 class="font-display text-charcoal mt-1 text-2xl font-bold">Tambah Fasilitas</h1>
    </div>

    @include('admin.facilities.partials.form')

@endsection
