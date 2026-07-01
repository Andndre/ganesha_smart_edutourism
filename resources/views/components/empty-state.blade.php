@props(['title', 'text', 'ctaUrl' => null, 'ctaText' => null])

<div class="flex flex-col items-center justify-center text-center h-[60vh]">
    {{ $icon }}
    <h2 class="text-xl font-bold text-charcoal mb-2">{{ $title }}</h2>
    <p class="text-sm text-gray-500 mb-6 max-w-xs">{{ $text }}</p>
    @if ($ctaUrl)
        <a href="{{ $ctaUrl }}"
            class="bg-primary inline-flex items-center gap-2 rounded-2xl px-6 py-3 text-sm font-bold text-white shadow-lg transition-all active:scale-[0.98]">
            {{ $ctaText }}
        </a>
    @endif
</div>
