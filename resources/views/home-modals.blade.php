@push('modals')
        <!-- Premium Detail Weather Modal (Mobile Bottom-Sheet / Desktop Modal) -->
        <x-modal name="weather-modal">
            <div class="space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span
                        class="rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-extrabold uppercase tracking-wider text-emerald-600">{{ __('Desa Penglipuran') }}</span>
                    <button type="button" @click="isOpen = false"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-500 transition-all hover:text-gray-600 active:scale-95 md:hidden"
                        title="{{ __('Tutup') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <h3 class="font-display text-charcoal text-xl font-black leading-snug tracking-tight">
                    {{ __('Detail Cuaca Desa') }}</h3>

                @if (isset($weather) && $weather)
                    <!-- Main Status Grid -->
                    <div class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-gray-100 bg-white shadow-sm">
                            {!! $weather->getIconHtml() !!}
                        </div>
                        <div>
                            <p class="text-2xl font-black leading-none text-gray-800">{{ round($weather->temperature) }}°C</p>
                            <p class="mt-1.5 text-xs font-black leading-none text-gray-700">{{ $weather->getConditionText() }}
                            </p>
                        </div>
                    </div>

                    <!-- Weather Parameters Grid -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Humidity -->
                        <div class="flex items-center gap-2.5 rounded-xl border border-gray-100 bg-gray-50/70 p-3.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 9.172V5L8 4z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold uppercase leading-none tracking-wider text-gray-500">
                                    {{ __('Kelembapan') }}
                                </p>
                                <p class="mt-1 text-xs font-black text-gray-700">{{ $weather->humidity }}%</p>
                            </div>
                        </div>

                        <!-- Wind -->
                        <div class="flex items-center gap-2.5 rounded-xl border border-gray-100 bg-gray-50/70 p-3.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold uppercase leading-none tracking-wider text-gray-500">
                                    {{ __('Angin') }}</p>
                                <p class="mt-1 text-xs font-black text-gray-700">{{ $weather->wind_speed }} km/h</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendation Box based on Weather Code -->
                    <div class="border-t border-gray-50 pt-3">
                        <h4 class="mb-1.5 text-[11px] font-black uppercase tracking-wider text-gray-500">
                            {{ __('Rekomendasi Aktivitas') }}
                        </h4>
                        <p class="text-xs leading-relaxed text-gray-500">
                            @if (\in_array($weather->weather_code, [0, 1, 2]))
                                @if ($weather->isNight())
                                    {{ __('Cuaca malam cerah dan sejuk, sangat nyaman untuk menikmati suasana malam desa yang tenang.') }}
                                @else
                                    {{ __('Cuaca sangat bersahabat untuk berjalan-jalan menjelajahi desa. Jangan lupa menggunakan tabir surya dan membawa air minum!') }}
                                @endif
                            @elseif(\in_array($weather->weather_code, [3, 45, 48]))
                                {{ __('Cuaca sejuk dan berawan, sangat ideal untuk berkeliling santai tanpa sengatan matahari.') }}
                            @elseif(\in_array($weather->weather_code, [51, 53, 55, 61, 63]))
                                {{ __('Gerimis atau hujan ringan. Disarankan membawa payung atau jas hujan saat menjelajahi pekarangan desa.') }}
                            @else
                                {{ __('Hujan deras atau badai terdeteksi. Sebaiknya bersantai di cafe lokal, mengunjungi rumah adat (bale), atau menikmati kuliner lokal di area tertutup.') }}
                            @endif
                        </p>
                    </div>

                    <!-- Footer Info / Updated Time -->
                    <div class="border-t border-gray-50 pt-3 text-center">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            {{ __('Pembaruan Terakhir:') }}
                            {{ $weather->updated_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                        </p>
                    </div>
                @else
                    <!-- No Weather Info State -->
                    <div class="space-y-4 py-8 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-500">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-gray-700">{{ __('Data Cuaca Belum Tersedia') }}</h4>
                            <p class="mx-auto max-w-60 text-xs leading-relaxed text-gray-500">
                                {{ __('Silakan jalankan update cuaca dari sistem untuk memuat laporan cuaca terkini.') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </x-modal>

        <!-- Premium Detail Density Modal (Mobile Bottom-Sheet / Desktop Modal) -->
        <x-modal name="density-modal">
            <div class="space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span
                        class="rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-extrabold uppercase tracking-wider text-emerald-600">{{ __('Desa Penglipuran') }}</span>
                    <button type="button" @click="isOpen = false"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-500 transition-all hover:text-gray-600 active:scale-95 md:hidden"
                        title="{{ __('Tutup') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <h3 class="font-display text-charcoal text-xl font-black leading-snug tracking-tight">
                    {{ __('Kepadatan Pengunjung') }}</h3>

                <!-- Total Wisatawan Saat Ini -->
                <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5">
                    <p class="text-[11px] font-bold uppercase leading-none tracking-wider text-gray-500">
                        {{ __('Total Wisatawan Saat Ini') }}</p>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-3xl font-black leading-none text-gray-800">{{ $totalCurrent }}</span>
                        <span class="text-sm font-semibold text-gray-500">/ {{ $totalMax }}
                            {{ __('kapasitas total') }}</span>
                    </div>
                    @php($modalDensityPercent = $totalMax > 0 ? round(($totalCurrent / $totalMax) * 100) : 0)
                    <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-gray-100">
                        <div class="{{ $densityStatus['barColor'] }} h-full transition-all"
                            style="width: {{ min(100, $modalDensityPercent) }}%">
                        </div>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">
                        {{ $modalDensityPercent }}% —
                        <span class="{{ $densityStatus['color'] }} font-semibold">{{ __($densityStatus['label']) }}</span>
                    </p>
                </div>

                <!-- Legend -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-primary/10 rounded-xl p-3 text-center">
                        <p class="text-primary text-xs font-bold">{{ __('Aman') }}</p>
                        <p class="text-primary text-[11px]">&lt; {{ $warningThreshold }}%</p>
                    </div>
                    {{-- Teks pakai amber-800/orange-800, bukan token secondary/warning: token aslinya
                         hanya 1,87:1 dan 3,42:1 di atas latar tint-nya sendiri --}}
                    <div class="bg-secondary/15 rounded-xl p-3 text-center">
                        <p class="text-xs font-bold text-amber-800">{{ __('Sedang') }}</p>
                        <p class="text-[11px] text-amber-800">{{ $warningThreshold }}-{{ $criticalThreshold }}%</p>
                    </div>
                    <div class="bg-warning/10 rounded-xl p-3 text-center">
                        <p class="text-xs font-bold text-orange-800">{{ __('Penuh') }}</p>
                        <p class="text-[11px] text-orange-800">&gt; {{ $criticalThreshold }}%</p>
                    </div>
                </div>

                <p
                    class="border-t border-gray-50 pt-3 text-center text-[11px] font-bold uppercase tracking-wider text-gray-500">
                    {{ __('Data diperbarui setiap kali halaman dimuat ulang') }}
                </p>
            </div>
        </x-modal>
    @endpush
