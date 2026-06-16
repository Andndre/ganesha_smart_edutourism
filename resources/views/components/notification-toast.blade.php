{{-- Notification Toast Banner - slides in from top when a new notification arrives --}}
<div id="notification-toast-container" class="fixed inset-x-0 top-0 z-9998 flex flex-col items-center gap-2 px-4 pt-[calc(env(safe-area-inset-top)+1rem)] pointer-events-none">
</div>

<template id="notification-toast-template">
    <div class="notification-toast pointer-events-auto w-full max-w-sm transform -translate-y-full opacity-0 transition-all duration-500 ease-out"
         role="alert">
        <div class="flex items-start gap-3 rounded-2xl border px-4 py-3 shadow-xl backdrop-blur-md">
            {{-- Icon --}}
            <div class="toast-icon mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg">
            </div>

            {{-- Content --}}
            <div class="min-w-0 flex-1">
                <p class="toast-title text-sm font-bold leading-tight text-gray-900"></p>
                <p class="toast-body mt-0.5 text-xs leading-relaxed text-gray-600"></p>
            </div>

            {{-- Close --}}
            <button type="button" class="toast-close -mr-1 -mt-1 shrink-0 rounded-full p-1 text-gray-400 transition-colors hover:text-gray-600"
                    aria-label="Tutup">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</template>

<style>
    .notification-toast.toast-visible {
        transform: translateY(0);
        opacity: 1;
    }

    /* Warning level */
    .notification-toast.toast-warning .toast-icon {
        background-color: #FEF3C7;
        color: #D97706;
    }
    .notification-toast.toast-warning > div {
        border-color: rgba(217, 119, 6, 0.2);
        background-color: rgba(255, 255, 255, 0.95);
    }

    /* Critical level */
    .notification-toast.toast-critical .toast-icon {
        background-color: #FEE2E2;
        color: #DC2626;
    }
    .notification-toast.toast-critical > div {
        border-color: rgba(220, 38, 38, 0.2);
        background-color: rgba(255, 255, 255, 0.95);
    }

    /* Info level */
    .notification-toast.toast-info .toast-icon {
        background-color: #DCFCE7;
        color: #1E5128;
    }
    .notification-toast.toast-info > div {
        border-color: rgba(30, 81, 40, 0.2);
        background-color: rgba(255, 255, 255, 0.95);
    }
</style>
