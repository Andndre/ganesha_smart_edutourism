@props(['title' => null])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white p-5 shadow-sm']) }}>
    @if ($title)
        <h2 class="mb-4 text-lg font-bold text-charcoal">{{ $title }}</h2>
    @endif

    {{ $slot }}
</section>
