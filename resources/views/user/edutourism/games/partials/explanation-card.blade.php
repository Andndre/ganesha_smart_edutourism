{{--
    "Tahukah Kamu" card shown after a mission resolves. Both sequence outcomes render it, so it
    lives here rather than being pasted twice.

    @param string $text  the localized explanation
    @param string $delay Alpine expression for the delay in ms (the two outcomes stagger differently)
--}}
<div class="edu-rise rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50/50 p-4 shadow-sm"
    :style="'animation-delay:' + ({{ $delay }}) + 'ms'">
    <div class="flex items-center gap-2">
        <span class="bg-secondary/15 text-secondary flex h-6 w-6 items-center justify-center rounded-lg">
            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 2a7 7 0 00-4 12.7V17a1 1 0 001 1h6a1 1 0 001-1v-2.3A7 7 0 0012 2zM9 20h6v.5a1.5 1.5 0 01-1.5 1.5h-3A1.5 1.5 0 019 20.5z" />
            </svg>
        </span>
        <h4 class="sq-label text-amber-700/80">{{ __('Tahukah Kamu') }}</h4>
    </div>
    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $text }}</p>
</div>
