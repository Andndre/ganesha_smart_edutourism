{{--
    Victory panel shared by both matching modes. Included inside the eduGameMatching scope, so it
    reads that component directly: revealDelay (wait out the staggered verdicts), displayPoints
    (count-up target), and the per-mode tallies.
--}}
<div class="edu-slide-up border-secondary/40 relative overflow-hidden rounded-2xl border bg-gradient-to-br from-white via-emerald-50/70 to-amber-50 p-5 text-center shadow-md"
    :style="`animation-delay:${revealDelay}ms`">

    {{-- Soft glow behind the score; purely decorative, kept out of the accessibility tree. --}}
    <div class="bg-secondary/20 pointer-events-none absolute -top-10 left-1/2 h-24 w-24 -translate-x-1/2 rounded-full blur-2xl"
        aria-hidden="true"></div>

    <div class="relative">
        <span class="text-secondary text-[10px] font-black uppercase tracking-[0.18em]">{{ __('Misi Selesai') }}</span>

        <p class="font-display text-charcoal mt-1 text-xl font-black">{{ __('Kerja Bagus!') }}</p>

        <p class="text-primary mt-2 text-4xl font-black tabular-nums">
            +<span x-text="displayPoints"></span>
            <span class="text-base font-bold uppercase tracking-wide">{{ __('poin') }}</span>
        </p>

        <template x-if="mode === 'match'">
            <p class="mt-1.5 text-xs font-semibold text-gray-500">
                <span x-text="cfg.pairs.length - matchMistakes"></span> {{ __('dari') }}
                <span x-text="cfg.pairs.length"></span> {{ __('pasangan benar') }}
            </p>
        </template>

        <template x-if="mode !== 'match'">
            <p class="mt-1.5 text-xs font-semibold text-gray-500">
                <span x-text="picked.length - wrongPicks"></span> {{ __('pilihan benar') }}
                <span x-show="wrongPicks > 0">· <span x-text="wrongPicks"></span> {{ __('salah') }}</span>
            </p>
        </template>
    </div>
</div>
