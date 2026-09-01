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
        <span class="text-secondary text-[11px] font-black uppercase tracking-[0.18em]">{{ __('Misi Selesai') }}</span>

        <p class="font-display text-charcoal mt-1 text-xl font-black">{{ __('Kerja Bagus!') }}</p>

        <p class="text-primary mt-2 text-4xl font-black tabular-nums">
            +<span x-text="displayPoints"></span>
            <span class="text-base font-bold uppercase tracking-wide">{{ __('poin') }}</span>
        </p>

        {{-- Whole sentences, not concatenated fragments: the tallies sit in different places in
        different languages, so a translator needs the full line to move them around. --}}
        <template x-if="mode === 'match'">
            <p class="mt-1.5 text-xs font-semibold text-gray-500"
                x-text="@js(__(':correct dari :total pasangan benar'))
                    .replace(':correct', cfg.pairs.length - matchMistakes)
                    .replace(':total', cfg.pairs.length)"></p>
        </template>

        <template x-if="mode !== 'match'">
            <p class="mt-1.5 text-xs font-semibold text-gray-500"
                x-text="(wrongPicks > 0 ? @js(__(':correct pilihan benar · :wrong salah')) : @js(__(':correct pilihan benar')))
                    .replace(':correct', picked.length - wrongPicks)
                    .replace(':wrong', wrongPicks)"></p>
        </template>
    </div>
</div>
