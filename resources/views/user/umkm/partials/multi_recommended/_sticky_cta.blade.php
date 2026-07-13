{{-- ponytail: partial dipecah untuk keterbacaan --}}
<!-- Sticky Bottom CTA -->
<div
    class="bottom-(--route-banner-h,0px) fixed inset-x-0 z-30 border-t border-gray-100 bg-white/90 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-8px_20px_rgba(0,0,0,0.06)] backdrop-blur-md">
    <div class="mb-3 flex items-center justify-between">
        <span class="text-sm font-semibold text-gray-500">{{ __('Total Estimasi Belanja') }}</span>
        <span class="text-primary font-display text-base font-extrabold">Rp
            {{ number_format($totalPrice, 0, ',', '.') }}</span>
    </div>
    <button
        class="bg-primary flex h-12 w-full items-center justify-center gap-2 rounded-xl font-bold text-white transition-all active:scale-[0.98]"
        onclick="startNavigation()">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
        </svg>
        {{ __('Mulai Perjalanan') }}
    </button>
</div>
