@extends('layouts.dashboard')

@section('title', 'Tarif Tiket')

@section('content')

    <div class="mb-6">
        <h1 class="font-display text-charcoal text-2xl font-bold">Tarif Tiket Masuk</h1>
        <p class="mt-0.5 text-sm text-gray-500">
            Harga yang dipakai petugas gerbang saat mencatat kunjungan. Golongan yang dinonaktifkan tidak muncul di form scan.
        </p>
    </div>

    {{-- Angka nol dibiarkan terlihat, bukan disembunyikan: petugas gerbang tidak
         boleh mencatat kunjungan dengan tarif yang belum diisi. --}}
    @if ($rates->flatten()->contains(fn ($rate) => $rate->is_active && $rate->price === 0))
        <div class="mb-6 max-w-4xl rounded-xl border border-warning/20 bg-warning/5 p-4 text-sm text-warning-800">
            Ada golongan aktif yang harganya masih Rp 0. Isi harganya atau nonaktifkan golongan itu.
        </div>
    @endif

    <div class="max-w-4xl space-y-6">
        @foreach (\App\Models\TicketRate::ORIGIN_LABELS as $origin => $originLabel)
            <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <header class="border-b border-gray-100 bg-gray-50/50 px-5 py-3.5">
                    <h2 class="text-charcoal text-sm font-bold">
                        {{ $originLabel }}
                        <span class="font-normal text-gray-500">
                            — {{ $origin === 'domestic' ? 'Warga Negara Indonesia' : 'Warga Negara Asing' }}
                        </span>
                    </h2>
                </header>

                <div class="divide-y divide-gray-50">
                    @forelse ($rates->get($origin, collect()) as $rate)
                        <form method="POST" action="{{ route('admin.ticket-rates.update', $rate) }}"
                            class="flex flex-wrap items-end gap-3 px-5 py-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="origin" value="{{ $rate->origin }}">

                            <div class="min-w-[9rem] flex-1">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500"
                                    for="name-{{ $rate->id }}">Golongan</label>
                                <input id="name-{{ $rate->id }}" type="text" name="name" value="{{ $rate->name }}" required
                                    class="min-h-[44px] w-full rounded-xl border border-gray-200 px-3 text-sm">
                            </div>

                            <div class="w-32">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500"
                                    for="price-{{ $rate->id }}">Retribusi</label>
                                <input id="price-{{ $rate->id }}" type="number" name="price" value="{{ $rate->price }}"
                                    min="0" step="500" inputmode="numeric" required
                                    class="min-h-[44px] w-full rounded-xl border border-gray-200 px-3 text-sm tabular-nums">
                            </div>

                            <div class="w-32">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500"
                                    for="fee-{{ $rate->id }}">Biaya layanan</label>
                                <input id="fee-{{ $rate->id }}" type="number" name="service_fee"
                                    value="{{ $rate->service_fee }}" min="0" step="500" inputmode="numeric" required
                                    class="min-h-[44px] w-full rounded-xl border border-gray-200 px-3 text-sm tabular-nums">
                            </div>

                            <div class="w-20">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500"
                                    for="sort-{{ $rate->id }}">Urutan</label>
                                <input id="sort-{{ $rate->id }}" type="number" name="sort_order"
                                    value="{{ $rate->sort_order }}" min="0" max="999"
                                    class="min-h-[44px] w-full rounded-xl border border-gray-200 px-3 text-sm tabular-nums">
                            </div>

                            <label class="flex min-h-[44px] cursor-pointer items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_active" value="1" @checked($rate->is_active)
                                    class="text-primary focus:ring-primary h-5 w-5 rounded border-gray-300">
                                Aktif
                            </label>

                            <button type="submit"
                                class="bg-primary shadow-primary/20 hover:bg-primary-600 min-h-[44px] rounded-xl px-4 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
                                Simpan
                            </button>
                        </form>
                    @empty
                        <p class="px-5 py-6 text-sm text-gray-500">Belum ada golongan untuk {{ $originLabel }}.</p>
                    @endforelse
                </div>

                {{-- Hapus dipisah dari form edit: satu tombol submit per form supaya
                     tidak ada aksi merusak yang ikut tertekan saat menyimpan harga. --}}
                @if ($rates->get($origin, collect())->isNotEmpty())
                    <footer class="flex flex-wrap items-center gap-2 border-t border-gray-100 bg-gray-50/50 px-5 py-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Hapus golongan:</span>
                        @foreach ($rates->get($origin) as $rate)
                            <form method="POST" action="{{ route('admin.ticket-rates.destroy', $rate) }}"
                                class="delete-form inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 transition-colors hover:border-warning/40 hover:text-warning-800">
                                    {{ $rate->name }} &times;
                                </button>
                            </form>
                        @endforeach
                    </footer>
                @endif
            </section>
        @endforeach

        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <header class="border-b border-gray-100 bg-gray-50/50 px-5 py-3.5">
                <h2 class="text-charcoal text-sm font-bold">Tambah Golongan</h2>
            </header>

            <form method="POST" action="{{ route('admin.ticket-rates.store') }}"
                class="flex flex-wrap items-end gap-3 px-5 py-4">
                @csrf

                <div class="w-32">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500"
                        for="new-origin">Asal</label>
                    <select id="new-origin" name="origin"
                        class="min-h-[44px] w-full rounded-xl border border-gray-200 px-3 text-sm">
                        @foreach (\App\Models\TicketRate::ORIGIN_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected(old('origin') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[9rem] flex-1">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500"
                        for="new-name">Golongan</label>
                    <input id="new-name" type="text" name="name" value="{{ old('name') }}" placeholder="mis. Pelajar"
                        required class="min-h-[44px] w-full rounded-xl border border-gray-200 px-3 text-sm">
                </div>

                <div class="w-32">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500"
                        for="new-price">Retribusi</label>
                    <input id="new-price" type="number" name="price" value="{{ old('price', 0) }}" min="0" step="500"
                        inputmode="numeric" required
                        class="min-h-[44px] w-full rounded-xl border border-gray-200 px-3 text-sm tabular-nums">
                </div>

                <div class="w-32">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500"
                        for="new-fee">Biaya layanan</label>
                    <input id="new-fee" type="number" name="service_fee" value="{{ old('service_fee', 1500) }}" min="0"
                        step="500" inputmode="numeric" required
                        class="min-h-[44px] w-full rounded-xl border border-gray-200 px-3 text-sm tabular-nums">
                </div>

                <div class="w-20">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500"
                        for="new-sort">Urutan</label>
                    <input id="new-sort" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                        max="999" class="min-h-[44px] w-full rounded-xl border border-gray-200 px-3 text-sm tabular-nums">
                </div>

                <button type="submit"
                    class="bg-primary shadow-primary/20 hover:bg-primary-600 min-h-[44px] rounded-xl px-4 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
                    Tambah
                </button>
            </form>
        </section>
    </div>

    @if ($errors->any())
        <div class="mt-6 max-w-4xl rounded-xl border border-warning/20 bg-warning/5 p-4">
            <ul class="list-inside list-disc space-y-1 text-sm text-warning-800">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

@endsection
