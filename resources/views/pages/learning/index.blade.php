@extends('layouts.app')
@section('title', 'Learning Modules')

@push('styles')
<style>
    /* Hide default layout header and bottom navigation for immersive experience */
    header { display: none !important; }
    nav.fixed.bottom-0 { display: none !important; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">Learning Modules</h1>
    <div class="grid gap-6 md:grid-cols-2">
        @foreach($modules as $module)
            <a href="{{ route('learning.show', $module->slug) }}" class="block p-6 bg-white rounded-xl shadow hover:shadow-lg transition">
                <h2 class="text-2xl font-semibold mb-2">{{ $module->title }}</h2>
                <p class="text-gray-600">{{ Str::limit($module->description, 120) }}</p>
            </a>
        @endforeach
    </div>
</div>
@endsection
