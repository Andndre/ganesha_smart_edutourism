<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Ticketing Point of Sale') }}
            </h2>
            <a href="{{ route('staff.ticketing.scan') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                Buka Scanner QR
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">{{ session('success') }}</h3>
                        </div>
                    </div>
                </div>

                @if(session('guestUrl'))
                <div class="mb-6 rounded-xl border border-indigo-200 bg-indigo-50 p-6 text-center shadow-sm">
                    <h3 class="text-lg font-bold text-indigo-900">Akses Guest Web App</h3>
                    <p class="mt-2 text-sm text-indigo-700">Silakan arahkan pengunjung untuk memindai QR Code di bawah ini untuk mengakses Smart Edutourism tanpa perlu mendaftar akun.</p>
                    <div class="mt-4 flex justify-center">
                        <div class="rounded-lg bg-white p-4 shadow-sm" id="qrcode"></div>
                    </div>
                    <div class="mt-4">
                        <p class="text-xs text-indigo-500">Atau akses URL berikut: <br><a href="{{ session('guestUrl') }}" class="font-medium underline" target="_blank">{{ session('guestUrl') }}</a></p>
                    </div>

                    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                    <script>
                        new QRCode(document.getElementById("qrcode"), {
                            text: "{{ session('guestUrl') }}",
                            width: 200,
                            height: 200,
                            colorDark : "#4F46E5",
                            colorLight : "#ffffff",
                            correctLevel : QRCode.CorrectLevel.H
                        });
                    </script>
                </div>
                @endif
            @endif

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <!-- Form Pembelian -->
                <div class="col-span-1 rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-lg font-bold text-gray-900">Pembelian Tiket Baru (Walk-in)</h3>
                    <form action="{{ route('staff.ticketing.walk-in') }}" method="POST">
                        @csrf
                        
                        <div class="space-y-4">
                            <div>
                                <label for="guest_name" class="block text-sm font-medium text-gray-700">Nama Pengunjung</label>
                                <input type="text" name="guest_name" id="guest_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="guest_email" class="block text-sm font-medium text-gray-700">Email (Opsional)</label>
                                <input type="email" name="guest_email" id="guest_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="tour_package_id" class="block text-sm font-medium text-gray-700">Pilih Paket Wisata</label>
                                <select id="tour_package_id" name="tour_package_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Pilih paket...</option>
                                    @foreach($packages as $package)
                                        <option value="{{ $package->id }}">{{ $package->name }} - Rp {{ number_format($package->price, 0, ',', '.') }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="party_size" class="block text-sm font-medium text-gray-700">Jumlah Orang</label>
                                    <input type="number" name="party_size" id="party_size" min="1" value="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Metode Bayar</label>
                                    <select id="payment_method" name="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="cash">Tunai (Cash)</option>
                                        <option value="qris">QRIS</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="pt-4">
                                <button type="submit" class="flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Proses Pembayaran & Cetak Tiket
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabel Transaksi Hari Ini -->
                <div class="col-span-1 rounded-xl bg-white p-6 shadow-sm md:col-span-2">
                    <h3 class="mb-4 text-lg font-bold text-gray-900">Tiket Terjual Hari Ini</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Pembeli</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Paket</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jumlah</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($reservations as $res)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $res->user ? $res->user->name : $res->guest_name }}
                                        {!! $res->user ? '' : '<span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">Walk-in</span>' !!}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ $res->tourPackage->name ?? 'N/A' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ $res->party_size }} Org <br>
                                        <span class="text-xs">Rp {{ number_format($res->total_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        @if($res->status === 'completed')
                                            <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">Selesai/Masuk</span>
                                        @elseif($res->status === 'confirmed')
                                            <span class="inline-flex rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800">Menunggu</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800">{{ ucfirst($res->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ $res->created_at->format('H:i') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada transaksi hari ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-admin-layout>
