<!-- Payment Bar: fixed bottom on mobile/tablet, sticky summary panel on desktop -->
<div
    class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-100 bg-white p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-4px_10px_rgba(0,0,0,0.05)] lg:sticky lg:inset-x-auto lg:bottom-auto lg:top-8 lg:mt-0 lg:rounded-2xl lg:border lg:border-gray-100 lg:p-5 lg:shadow-sm">
    <div class="mx-auto w-full max-w-2xl lg:max-w-none">
        <h3 class="text-charcoal mb-3 hidden font-bold lg:block">Ringkasan Pembayaran</h3>

        <div class="mb-3 hidden items-center justify-between text-sm text-gray-500 lg:flex">
            <span>Harga / orang</span>
            <span>Rp {{ number_format($package->price, 0, ',', '.') }}</span>
        </div>
        <div class="mb-3 hidden items-center justify-between text-sm text-gray-500 lg:flex">
            <span>Jumlah peserta</span>
            <span x-text="partySize + ' orang'"></span>
        </div>

        <div class="mb-3 flex items-center justify-between px-1 lg:border-t lg:border-gray-100 lg:px-0 lg:pt-3">
            <span class="text-sm font-medium text-gray-500 lg:font-bold lg:text-charcoal">Total Harga</span>
            <span class="text-primary text-lg font-bold">Rp <span x-text="formattedTotal"></span></span>
        </div>
        <button type="submit" :disabled="isLoading"
            class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center gap-2 rounded-2xl font-bold text-white shadow-lg transition-all hover:bg-[#152E1D] active:scale-[0.98] disabled:opacity-50">
            <span x-show="!isLoading">Bayar Sekarang</span>
            <span x-show="isLoading">Memproses...</span>
        </button>
    </div>
</div>
