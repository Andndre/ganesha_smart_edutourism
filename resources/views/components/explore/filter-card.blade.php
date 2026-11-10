@props(['filter', 'label'])

<button type="button" data-filter="{{ $filter }}" aria-pressed="true"
    {{ $attributes->merge(['class' => 'filter-card active flex min-h-12 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold text-charcoal transition-colors hover:bg-surface focus-visible:outline-2 focus-visible:outline-primary']) }}>
    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
        {{ $slot }}
    </span>
    <span class="flex-1">{{ $label }}</span>
    <span class="filter-check" aria-hidden="true">✓</span>
</button>
