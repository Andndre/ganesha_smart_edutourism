{{-- ponytail: partial dipecah untuk keterbacaan --}}
        <!-- Info Card -->
        <div class="relative z-10 -mt-4 rounded-t-3xl border-b border-gray-100 bg-white px-4 py-6 shadow-sm">
            <div class="mb-4 flex items-center gap-4">
                <div
                    class="text-primary flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gray-100 shadow-inner">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-charcoal text-xl font-bold">{{ $umkm->business_name }}</h2>
                    <p class="mt-1 flex items-center gap-1 text-sm text-gray-500">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ __('Milik:') }} {{ $umkm->owner_name }}
                    </p>
                    <div class="mt-1.5 flex items-center gap-2">
                        <span
                            class="bg-primary/10 text-primary rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                            {{ __('Rekomendasi') }}
                        </span>
                    </div>
                </div>
            </div>
            @if ($umkm->description)
                <div class="prose prose-sm mt-2 max-w-none text-gray-600 prose-p:leading-relaxed">
                    {!! $umkm->description !!}
                </div>
            @endif
        </div>
