@extends('layouts.app')
@section('title', __('Detail Paket - Penglipuran'))
@section('header_title', __('Reservasi Paket'))

@section('content')
    <div class="relative pb-32 lg:pb-8">
        <div class="mx-auto w-full max-w-5xl lg:px-8 lg:py-8">
            <div class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-8">
                <!-- Image Header -->
                <div
                    class="relative aspect-video w-full bg-gray-200 lg:sticky lg:top-8 lg:overflow-hidden lg:rounded-3xl lg:shadow-sm">
                    @if ($package->images && count($package->images) > 0)
                        <img src="{{ Storage::url($package->images[0]) }}" alt="{{ $package->name }}"
                            class="h-full w-full object-cover">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                            <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Package Details -->
                <div
                    class="relative z-10 -mt-6 rounded-t-3xl border-b border-gray-100 bg-white px-5 py-6 shadow-sm lg:mt-0 lg:rounded-3xl lg:border lg:border-gray-100 lg:px-7 lg:py-7">
                    <div class="mb-2">
                        <span
                            class="text-primary mb-3 inline-block rounded-lg border border-green-100 bg-green-50 px-2.5 py-1 text-xs font-bold">{{ $package->isTicket() ? __('Tiket Masuk') : __('Paket Wisata') }}</span>
                        <h1 class="text-charcoal text-xl font-bold lg:text-2xl">{{ $package->name }}</h1>
                    </div>

                    <p class="mb-4 text-sm leading-relaxed text-gray-500 lg:text-base">
                        {{ $package->description }}
                    </p>

                    <!-- Info Bar -->
                    <div class="mb-3 grid grid-cols-3 gap-2">
                        <div class="flex flex-col items-center rounded-xl border border-gray-100 bg-gray-50 py-2.5">
                            <svg class="text-primary mb-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[10px] font-semibold text-gray-500">{{ __('Durasi') }}</span>
                            <span class="text-charcoal text-sm font-bold">{{ $package->duration_hours }}
                                {{ __('Jam') }}</span>
                        </div>
                        <div class="flex flex-col items-center rounded-xl border border-gray-100 bg-gray-50 py-2.5">
                            <svg class="text-primary mb-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="text-[10px] font-semibold text-gray-500">{{ __('Min. Peserta') }}</span>
                            <span class="text-charcoal text-sm font-bold">{{ $package->min_capacity }}
                                {{ __('Orang') }}</span>
                        </div>
                        <div class="flex flex-col items-center rounded-xl border border-gray-100 bg-gray-50 py-2.5">
                            <svg class="text-primary mb-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span class="text-[10px] font-semibold text-gray-500">{{ __('Maks. Peserta') }}</span>
                            <span class="text-charcoal text-sm font-bold">{{ $package->max_capacity }}
                                {{ __('Orang') }}</span>
                        </div>
                    </div>

                    <!-- Facilities -->
                    @if (count($package->inclusions ?? []) > 0)
                        <div class="mb-2 rounded-xl border border-gray-100 bg-gray-50 p-3">
                            <div class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-gray-500">
                                <svg class="text-primary h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                {{ __('Fasilitas yang Didapatkan') }}
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($package->inclusions as $item)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-600 shadow-sm ring-1 ring-gray-200">
                                        <svg class="text-primary h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ $item }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @php($guideWhatsapp = config('services.tour_guide.whatsapp'))
                    @if ($guideWhatsapp && $package->includesTourGuide())
                        <!-- Tour guide manual coordination via WhatsApp (Opsi B: no real-time guide booking) -->
                        <div class="mb-2 rounded-xl border border-green-100 bg-green-50 p-3">
                            <p class="mb-2 text-xs leading-relaxed text-gray-600">
                                {{ __('Paket ini menyertakan pemandu wisata. Silakan koordinasikan tanggal dan ketersediaan pemandu terlebih dahulu sebelum melakukan pembayaran.') }}
                            </p>
                            <a href="https://wa.me/{{ $guideWhatsapp }}?text={{ urlencode(__('Halo, saya ingin memesan pemandu untuk ":package". Apakah pemandu tersedia? Rencana tanggal kunjungan saya:', ['package' => $package->name])) }}"
                                target="_blank" rel="noopener"
                                class="tap-target inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#25D366] px-4 text-sm font-bold text-white shadow-sm transition-all hover:brightness-95 active:scale-[0.98]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.511-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.884 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                </svg>
                                {{ __('Pesan Pemandu via WhatsApp') }}
                            </a>
                        </div>
                    @endif

                    <!-- Inline CTA (desktop only) -->
                    <div class="mt-6 hidden border-t border-gray-100 pt-6 lg:block">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">{{ __('Mulai dari') }}</span>
                            <span class="text-primary text-2xl font-bold">Rp
                                {{ number_format($package->price, 0, ',', '.') }}</span>
                        </div>
                        <a href="{{ route($package->isTicket() ? 'ticket.book' : 'tour-package.book', $package->id) }}"
                            class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center gap-2 rounded-2xl font-bold text-white shadow-lg transition-all hover:bg-[#152E1D] active:scale-[0.98]">
                            {{ __('Pesan Tiket Sekarang') }}
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                        @guest
                            <p class="mt-2 text-center text-xs text-gray-400">
                                {{ __('Anda akan diminta untuk login terlebih dahulu.') }}
                            </p>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sticky Bottom CTA (mobile & tablet) -->
    <div
        class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-100 bg-white p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-4px_10px_rgba(0,0,0,0.05)] lg:hidden">
        <div class="mx-auto w-full max-w-2xl">
            <div class="mb-3 flex items-center justify-between px-1">
                <span class="text-sm font-medium text-gray-500">{{ __('Mulai dari') }}</span>
                <span class="text-primary text-lg font-bold">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
            </div>

            <a href="{{ route($package->isTicket() ? 'ticket.book' : 'tour-package.book', $package->id) }}"
                class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center gap-2 rounded-2xl font-bold text-white shadow-lg transition-all active:scale-[0.98]">
                {{ __('Pesan Tiket Sekarang') }}
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
            @guest
                <p class="mt-2 text-center text-xs text-gray-400">{{ __('Anda akan diminta untuk login terlebih dahulu.') }}
                </p>
            @endguest
        </div>
    </div>
@endsection
