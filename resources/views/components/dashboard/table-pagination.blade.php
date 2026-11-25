<nav role="navigation" aria-label="{{ $paginationLabel ?? 'Navigasi halaman tabel' }}" data-table-pagination class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm tabular-nums text-gray-600">
        Menampilkan <span class="font-semibold text-charcoal">{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}</span>
        dari <span class="font-semibold text-charcoal">{{ number_format($paginator->total(), 0, ',', '.') }}</span> {{ $itemLabel ?? 'data' }}
    </p>
    @if ($paginator->hasPages())
        <div class="flex flex-wrap items-center gap-2">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="dashboard-page-link opacity-50">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="dashboard-page-link">Sebelumnya</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="hidden px-1 text-gray-500 sm:inline">{{ $element }}</span>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span aria-current="page" aria-label="Halaman {{ $page }}" class="dashboard-page-link border-primary bg-primary text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="dashboard-page-link hidden sm:inline-flex">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="dashboard-page-link">Berikutnya</a>
            @else
                <span aria-disabled="true" class="dashboard-page-link opacity-50">Berikutnya</span>
            @endif
        </div>
    @endif
</nav>
