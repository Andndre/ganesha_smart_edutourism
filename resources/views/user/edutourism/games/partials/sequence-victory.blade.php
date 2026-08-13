{{--
    Result panel for the sequence game — shown for BOTH outcomes, because the check is one-shot and
    a wrong order still ends the mission with a score. Included inside the eduGameSequence scope,
    so it reads that component directly: revealDelay (wait out the staggered per-card verdicts),
    allCorrect, displayPoints (count-up target) and correctCount.
--}}
<div class="edu-slide-up relative overflow-hidden rounded-2xl border p-5 text-center shadow-md"
    :class="allCorrect
        ? 'border-secondary/40 bg-gradient-to-br from-white via-emerald-50/70 to-amber-50'
        : 'border-amber-200 bg-gradient-to-br from-white to-amber-50/60'"
    :style="`animation-delay:${revealDelay}ms`">

    {{-- Soft glow behind the score; purely decorative, kept out of the accessibility tree. --}}
    <div class="pointer-events-none absolute -top-10 left-1/2 h-24 w-24 -translate-x-1/2 rounded-full blur-2xl"
        :class="allCorrect ? 'bg-secondary/20' : 'bg-amber-300/20'" aria-hidden="true"></div>

    <div class="relative" role="status" aria-live="polite">
        <span class="text-[10px] font-black uppercase tracking-[0.18em]"
            :class="allCorrect ? 'text-secondary' : 'text-amber-600'"
            x-text="allCorrect ? @js(__('Misi Selesai')) : @js(__('Hasil Pemeriksaan'))"></span>

        <p class="font-display text-charcoal mt-1 text-xl font-black"
            x-text="allCorrect ? @js(__('Kronologi Tersusun Sempurna!')) : @js(__('Urutan Belum Tepat'))"></p>

        <p class="text-primary mt-2 text-4xl font-black tabular-nums">
            +<span x-text="displayPoints"></span>
            <span class="text-base font-bold uppercase tracking-wide">{{ __('poin') }}</span>
        </p>

        <p class="mt-1.5 text-xs font-semibold text-gray-500">
            <span x-text="correctCount"></span> {{ __('dari') }} <span x-text="items.length"></span>
            {{ __('langkah pada posisi yang tepat') }}
        </p>
    </div>
</div>
