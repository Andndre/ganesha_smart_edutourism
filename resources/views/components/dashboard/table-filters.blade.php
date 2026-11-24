@props(['action', 'searchName' => 'search', 'placeholder' => 'Cari data...', 'label' => 'Cari data', 'resetUrl' => null, 'filtered' => false])

<form method="GET" action="{{ $action }}" {{ $attributes->class(['flex flex-col gap-3 sm:flex-row sm:items-end']) }}>
    <div class="min-w-0 flex-1">
        <label for="{{ $attributes->get('id', $searchName) }}-input" class="mb-1.5 block text-xs font-semibold text-gray-700">{{ $label }}</label>
        <input type="search" id="{{ $attributes->get('id', $searchName) }}-input" name="{{ $searchName }}"
            value="{{ request($searchName) }}" placeholder="{{ $placeholder }}" class="dashboard-table-input w-full">
    </div>
    {{ $slot }}
    <div class="flex shrink-0 items-center gap-2">
        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-primary px-4 text-sm font-semibold text-white transition-colors hover:bg-primary-hover focus-visible:outline-primary active:bg-primary-dark">Terapkan</button>
        @if ($filtered)
            <a href="{{ $resetUrl ?? $action }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50 focus-visible:outline-primary">Reset</a>
        @endif
    </div>
</form>
