<!-- Walk-in Purchase Modal -->
<x-modal name="walkin-modal" maxWidth="md" desktopLayout="drawer">
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="font-display text-charcoal text-lg font-bold">Pembelian Tiket Walk-in</h3>
        </div>

        <form id="walkin-form" action="{{ route('staff.ticketing.walk-in') }}" method="POST" class="mt-4">
            @csrf

            <div class="space-y-4 text-left">
                <div>
                    <label for="guest_name" class="block text-sm font-semibold text-gray-700">Nama Pengunjung
                        <span class="text-warning">*</span></label>
                    <input type="text" name="guest_name" id="guest_name" required
                        placeholder="Nama lengkap pengunjung"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                </div>

                <div>
                    <label for="guest_email" class="block text-sm font-semibold text-gray-700">Email
                        (Opsional)</label>
                    <input type="email" name="guest_email" id="guest_email" placeholder="email@contoh.com"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                </div>

                <div>
                    <label for="tour_package_id" class="block text-sm font-semibold text-gray-700">Paket Wisata
                        <span class="text-warning">*</span></label>
                    <select id="tour_package_id" name="tour_package_id" required
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:outline-none">
                        <option value="">Pilih paket...</option>
                        @foreach ($packages as $package)
                            <option value="{{ $package->id }}">{{ $package->name }} - Rp
                                {{ number_format($package->price, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="party_size" class="block text-sm font-semibold text-gray-700">Jumlah Orang
                            <span class="text-warning">*</span></label>
                        <input type="number" name="party_size" id="party_size" min="1" value="1"
                            required
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    </div>
                    <div>
                        <label for="payment_method" class="block text-sm font-semibold text-gray-700">Metode Bayar
                            <span class="text-warning">*</span></label>
                        <select id="payment_method" name="payment_method" required
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:outline-none">
                            <option value="cash">Tunai (Cash)</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="isOpen = false"
                        class="flex-1 justify-center rounded-xl bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-200 active:scale-[0.99]">
                        Batal
                    </button>
                    <button type="submit" style="flex: 2;"
                        class="bg-primary shadow-primary/20 hover:bg-primary-600 justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.99]">
                        Proses & Cetak Tiket
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-modal>
