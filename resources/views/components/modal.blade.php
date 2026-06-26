@props([
    'name',
    'maxWidth' => 'max-w-md',
    'defaultOpen' => false,
    'hasBackdrop' => true,
    'desktopLayout' => 'center',
    'drawerFromSide' => 'right',
    'zIndex' => 'z-100',
    'closeOnOutsideClick' => true,
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => $maxWidth,
    };

    $desktopAlignmentClass = match ($desktopLayout) {
        'drawer' => $drawerFromSide === 'right'
            ? 'md:items-stretch md:justify-end md:px-0'
            : 'md:items-stretch md:justify-start md:px-0',
        default => 'md:items-center md:px-4',
    };

    $desktopContainerClass = match ($desktopLayout) {
        'drawer' => $drawerFromSide === 'right'
            ? 'max-h-sheet overflow-y-auto md:rounded-none md:rounded-t-none md:border-l md:border-gray-200 md:h-screen'
            : 'max-h-sheet overflow-y-auto md:rounded-none md:rounded-t-none md:border-r md:border-gray-200 md:h-screen',
        default => 'max-h-sheet overflow-y-auto md:rounded-3xl md:overflow-hidden md:max-h-[85vh]',
    };

    $desktopTransitionStart = match ($desktopLayout) {
        'drawer' => $drawerFromSide === 'right'
            ? 'md:translate-x-full md:translate-y-0 md:scale-100'
            : 'md:-translate-x-full md:translate-y-0 md:scale-100',
        default => 'md:translate-y-4 md:scale-95',
    };

    $desktopTransitionEnd = match ($desktopLayout) {
        'drawer' => 'md:translate-x-0 md:translate-y-0 md:scale-100',
        default => 'md:translate-y-0 md:scale-100',
    };
@endphp

<!-- Responsive Drawer / Modal -->
<div x-data="{ isOpen: {{ $defaultOpen ? 'true' : 'false' }} }" x-show="isOpen" @open-{{ $name }}.window="isOpen = true"
    @close-{{ $name }}.window="isOpen = false"
    class="{{ $hasBackdrop ? 'bg-charcoal/60 backdrop-blur-sm' : 'bg-transparent pointer-events-none' }} {{ $zIndex }} {{ $desktopAlignmentClass }} fixed inset-0 flex items-end justify-center px-0"
    style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>

    <div class="{{ $desktopContainerClass }} {{ $maxWidthClass }} pointer-events-auto relative flex w-full flex-col rounded-t-[2.5rem] bg-white p-6 pb-10 shadow-2xl md:pb-6"
        style="padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));"
        @click.outside="! {{ $closeOnOutsideClick ? 'true' : 'false' }} || (!document.querySelector('.swal2-container') && (isOpen = false))" x-show="isOpen"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-y-full {{ $desktopTransitionStart }}"
        x-transition:enter-end="translate-y-0 {{ $desktopTransitionEnd }}"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0 {{ $desktopTransitionEnd }}"
        x-transition:leave-end="translate-y-full {{ $desktopTransitionStart }}">

        <!-- Pull Bar on Mobile -->
        <div class="mx-auto -mt-2 mb-5 h-1.5 w-12 rounded-full bg-gray-200 md:hidden"></div>

        <!-- Close Button on Desktop -->
        <button type="button" @click="isOpen = false"
            class="absolute right-4 top-4 z-50 hidden h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-400 transition-colors hover:text-gray-600 md:flex"
            title="Tutup">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Modal Body -->
        <div class="-mr-1 flex min-h-0 w-full flex-1 flex-col overflow-y-auto pr-1">
            {{ $slot }}
        </div>
    </div>
</div>
