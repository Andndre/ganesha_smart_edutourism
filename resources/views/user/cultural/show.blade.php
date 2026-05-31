@extends('layouts.app')
@section('title', $object->name . ' - Penglipuran')
@section('header_title', 'Storytelling')

@section('content')
    <article class="bg-surface pb-10">
        <!-- Hero Image Area -->
        <div class="w-full h-[40dvh] bg-gray-200 relative overflow-hidden">
            @if($object->historical_images && count($object->historical_images) > 0)
                <img src="{{ asset($object->historical_images[0]) }}" alt="{{ $object->name }}" class="w-full h-full object-cover">
            @else
                <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                    <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
            <div class="absolute inset-0 bg-linear-to-t from-charcoal/90 via-charcoal/20 to-transparent z-10"></div>

            <div class="absolute bottom-6 left-6 right-6 text-white z-20">
                <h1 class="text-3xl font-bold font-playfair mb-2 leading-tight">{{ $object->name }}</h1>
                <p class="text-sm text-gray-200 font-medium tracking-wide">Jantung Spiritual Desa Penglipuran</p>
            </div>
        </div>

        <!-- Audio Player (Mockup) -->
        @if($object->audio_narration_path)
            <div class="px-6 -mt-6 relative z-10 mb-8">
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center gap-4">
                    <button
                        class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center shrink-0 active:scale-95 transition-all shadow-[0_4px_10px_rgba(30,81,40,0.3)]">
                        <svg class="w-5 h-5 ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div class="flex-1">
                        <div class="text-sm font-bold text-charcoal">Dengarkan Kisah Ini</div>
                        <div class="text-xs text-gray-500 mt-1">Narasi Jero Mangku (03:45)</div>
                    </div>
                    <!-- Animated bars (pseudo) -->
                    <div class="flex items-end gap-1 h-4">
                        <div class="w-1 bg-gray-300 h-2 rounded-full"></div>
                        <div class="w-1 bg-gray-300 h-4 rounded-full"></div>
                        <div class="w-1 bg-gray-300 h-3 rounded-full"></div>
                        <div class="w-1 bg-gray-300 h-1 rounded-full"></div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Article Content -->
        <div
            class="px-6 prose prose-p:text-gray-600 prose-p:leading-relaxed prose-h2:font-playfair prose-h2:text-charcoal prose-h2:text-2xl prose-h2:font-bold prose-h2:mt-8 prose-h2:mb-4 max-w-none">

            <p class="text-lg font-medium text-charcoal leading-relaxed mb-6">
                {{ $object->description }}
            </p>

            @foreach($object->stories as $story)
                <h2>{{ $story->title }}</h2>
                <div class="text-gray-600 leading-relaxed text-sm">
                    {!! nl2br(e($story->content)) !!}
                </div>
            @endforeach
        </div>

        <!-- AR Button -->
        @if($object->ar_marker_id || $object->model_3d_path)
            <div class="px-6 mt-10">
                <a href="{{ route('ar-scan') }}"
                    class="w-full flex items-center justify-center gap-2 bg-primary text-white py-4 rounded-xl font-bold active:scale-[0.98] transition-all shadow-[0_4px_14px_rgba(30,81,40,0.3)]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 011.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Jelajahi dalam Mode AR
                </a>
            </div>
        @endif

    </article>
@endsection