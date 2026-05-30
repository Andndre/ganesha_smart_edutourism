<!-- Sticky Bottom Payment Bar -->
<div class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-100 bg-white p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
    <div class="mb-3 flex items-center justify-between px-1">
        <span class="text-sm font-medium text-gray-500">Total Harga</span>
        <span class="text-primary text-lg font-bold">Rp <span x-text="formattedTotal"></span></span>
    </div>
    <button type="submit" :disabled="isLoading"
        class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center gap-2 rounded-2xl font-bold text-white shadow-lg transition-all active:scale-[0.98] disabled:opacity-50">
        <span x-show="!isLoading">Bayar Sekarang</span>
        <span x-show="isLoading">Memproses...</span>
    </button>
</div>
