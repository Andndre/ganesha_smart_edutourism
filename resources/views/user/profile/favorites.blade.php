@php
    $title = __('Favorites');
@endphp

@extends('layouts.app')

@section('content')
    <div class="p-4">
        <h1 class="text-2xl font-bold text-charcoal-dark mb-4">{{ __('My Favorites') }}</h1>

        @if($items->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                <p class="text-gray-500 text-lg">{{ __('You haven\'t added any favorites yet.') }}</p>
                <p class="text-gray-400 mt-2">{{ __('Explore cultural objects and add them to your favorites!') }}</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($items as $item)
                    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                        <h3 class="font-semibold text-charcoal-dark">{{ $item->name ?? $item->title }}</h3>
                        @if(method_exists($item, 'getShortDescriptionAttribute') || isset($item->short_description))
                            <p class="text-gray-600 text-sm mt-1">{{ $item->short_description ?? '' }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
