<!-- Mobile Card List (Visible only on Mobile) -->
<div class="space-y-4 sm:hidden">
    <template x-for="res in filteredAndSortedReservations()" :key="'mob-' + res.id">
        <div class="space-y-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <h4 class="text-charcoal text-sm font-bold" x-text="res.guest_name"></h4>
                    <div class="mt-0.5 flex items-center gap-1.5">
                        <span class="text-[10px] text-gray-400" x-text="'Pukul ' + res.time"></span>
                        <template x-if="res.is_walkin">
                            <span
                                class="bg-primary/10 py-0.2 text-primary inline-flex items-center rounded px-1.5 text-[8px] font-semibold">Walk-in</span>
                        </template>
                    </div>
                </div>
                <div>
                    <template x-if="res.status === 'completed'">
                        <span
                            class="bg-primary/10 text-primary inline-flex rounded-lg px-2.5 py-1 text-[10px] font-semibold">Selesai</span>
                    </template>
                    <template x-if="res.status === 'confirmed'">
                        <span
                            class="inline-flex rounded-lg bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700">Menunggu</span>
                    </template>
                    <template x-if="res.status !== 'completed' && res.status !== 'confirmed'">
                        <span
                            class="inline-flex rounded-lg bg-gray-100 px-2.5 py-1 text-[10px] font-semibold text-gray-600"
                            x-text="capitalize(res.status)"></span>
                    </template>
                </div>
            </div>

            <div class="space-y-1.5 rounded-xl bg-gray-50/55 p-3 text-xs">
                <div class="flex justify-between text-gray-500">
                    <span>Paket Wisata</span>
                    <span class="text-charcoal max-w-37.5 truncate text-right font-semibold"
                        x-text="res.package_name"></span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Jumlah Peserta</span>
                    <span class="text-charcoal font-semibold" x-text="res.party_size + ' Orang'"></span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Total Pembayaran</span>
                    <span class="text-primary font-bold" x-text="formatRupiah(res.total_amount)"></span>
                </div>
            </div>

            <!-- Actions for Mobile -->
            <template x-if="res.status === 'confirmed' || res.status === 'pending'">
                <div class="flex gap-2 pt-1">
                    <template x-if="res.status === 'confirmed'">
                        <button @click="checkInReservation(res.id)"
                            class="bg-primary shadow-primary/15 w-full rounded-xl py-2.5 text-center text-xs font-bold text-white shadow-md transition-all active:scale-[0.98]">
                            Check In / Masuk
                        </button>
                    </template>
                    <template x-if="res.status === 'pending'">
                        <div class="flex w-full gap-2">
                            <button x-show="res.payment_method === 'qris'" @click="payQRIS(res.id)"
                                class="flex-1 rounded-xl bg-amber-500 py-2.5 text-center text-xs font-bold text-white shadow-md shadow-amber-500/15 transition-all active:scale-[0.98]">
                                Bayar
                            </button>
                            <button x-show="res.payment_method === 'qris'" @click="syncReservation(res.id)"
                                class="flex-1 rounded-xl bg-gray-100 py-2.5 text-center text-xs font-semibold text-gray-700 transition-all active:scale-[0.98]">
                                Sync
                            </button>
                            <button @click="cancelReservation(res.id)"
                                class="flex-1 rounded-xl bg-red-50 py-2.5 text-center text-xs font-semibold text-red-700 transition-all active:scale-[0.98]">
                                Batal
                            </button>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </template>
    <template x-if="filteredAndSortedReservations().length === 0">
        <div class="rounded-xl border border-dashed border-gray-200 py-8 text-center text-sm text-gray-400">
            Tidak ada transaksi yang cocok dengan filter.</div>
    </template>
</div>
