@props(['title', 'columns', 'paginator' => null, 'count' => null])

<section {{ $attributes->class(['min-w-0 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm']) }}>
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 px-5 py-4">
        <h2 class="font-display text-lg font-bold text-charcoal">{{ $title }}</h2>
        @if ($paginator || $count !== null)
            <span class="text-sm tabular-nums text-gray-600">{{ number_format($paginator ? $paginator->total() : $count, 0, ',', '.') }} data</span>
        @endif
    </div>

    @isset($toolbar)
        <div class="border-b border-gray-200 bg-surface/50 p-5">{{ $toolbar }}</div>
    @endisset

    {{ $beforeTable ?? '' }}

    <div tabindex="0" role="region" aria-label="Tabel {{ $title }}. Geser untuk melihat kolom lainnya."
        class="max-h-[70vh] overflow-auto overscroll-x-contain focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary">
        <table class="dashboard-table w-full min-w-3xl border-separate border-spacing-0 text-left text-sm text-gray-700">
            <caption class="sr-only">{{ $title }}</caption>
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th scope="col" @class(['text-right' => is_array($column) && ($column['align'] ?? null) === 'right'])>
                            {{ is_array($column) ? $column['label'] : $column }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>{{ $slot }}</tbody>
        </table>
    </div>

    @if ($paginator)
        <div class="border-t border-gray-200 px-5 py-4">
            {{ $paginator->onEachSide(1)->links('components.dashboard.table-pagination') }}
        </div>
    @endif
</section>
