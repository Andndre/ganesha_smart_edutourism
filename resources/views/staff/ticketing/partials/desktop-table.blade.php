<!-- Desktop Table (Hidden on Mobile) -->
<div class="hidden overflow-x-auto rounded-xl border border-gray-100 sm:block">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50/50">
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                    Pembeli</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                    Paket</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                    Jumlah & Total</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                    Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                    Waktu</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                    Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <template x-for="res in filteredAndSortedReservations()" :key="res.id">
                <tr class="hover:bg-gray-50/30">
                    <td class="text-charcoal px-4 py-3.5 font-semibold">
                        <span x-text="res.guest_name"></span>
                        <template x-if="res.is_walkin">
                            <span
                                class="bg-primary/10 text-primary ml-1 inline-flex items-center rounded px-2 py-0.5 text-[10px] font-semibold">Walk-in</span>
                        </template>
                    </td>
                    <td class="px-4 py-3.5 text-gray-600" x-text="res.package_name"></td>
                    <td class="px-4 py-3.5 text-gray-600">
                        <span x-text="res.party_size + ' Org'"></span> <br>
                        <span class="text-charcoal text-xs font-semibold"
                            x-text="formatRupiah(res.total_amount)"></span>
                    </td>
                    <td class="px-4 py-3.5">
                        <template x-if="res.status === 'completed'">
                            <span
                                class="bg-primary/10 text-primary inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold">Selesai/Masuk</span>
                        </template>
                        <template x-if="res.status === 'confirmed'">
                            <span
                                class="inline-flex rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Menunggu</span>
                        </template>
                        <template x-if="res.status !== 'completed' && res.status !== 'confirmed'">
                            <span
                                class="inline-flex rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600"
                                x-text="capitalize(res.status)"></span>
                        </template>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-400" x-text="res.time"></td>
                    <td class="px-4 py-3.5">
                        <div class="flex gap-1.5">
                            <template x-if="res.status === 'confirmed'">
                                <button @click="checkInReservation(res.id)"
                                    class="bg-primary hover:bg-primary-600 inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold text-white shadow-sm transition-all">
                                    Check In
                                </button>
                            </template>
                            <template x-if="res.status === 'pending'">
                                <div class="flex gap-1.5">
                                    <template x-if="res.payment_method === 'qris'">
                                        <div class="flex gap-1.5">
                                            <button @click="payQRIS(res.id)"
                                                class="inline-flex items-center rounded-lg bg-amber-500 px-2.5 py-1 text-xs font-bold text-white shadow-sm transition-all hover:bg-amber-600">
                                                Bayar
                                            </button>
                                            <button @click="syncReservation(res.id)"
                                                class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-1 text-xs font-bold text-gray-700 transition-all hover:bg-gray-200"
                                                title="Sync Status">
                                                Sync
                                            </button>
                                        </div>
                                    </template>
                                    <button @click="cancelReservation(res.id)"
                                        class="inline-flex items-center rounded-lg bg-red-50 px-2 py-1 text-xs font-bold text-red-700 transition-all hover:bg-red-100"
                                        title="Batalkan">
                                        Batal
                                    </button>
                                </div>
                            </template>
                        </div>
                    </td>
                </tr>
            </template>
            <template x-if="filteredAndSortedReservations().length === 0">
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada transaksi yang
                        cocok dengan filter.</td>
                </tr>
            </template>
        </tbody>
    </table>
</div>
