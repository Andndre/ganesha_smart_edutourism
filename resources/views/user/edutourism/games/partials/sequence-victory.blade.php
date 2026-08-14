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

    {{-- The score counts up frame by frame, so a live region around it would read out every
    intermediate number. The panel is silent and this one line announces the outcome once. --}}
    <p class="sr-only" role="status" aria-live="polite"
        x-text="`${allCorrect ? @js(__('Kronologi Tersusun Sempurna!')) : @js(__('Urutan Belum Tepat'))} +${earned} {{ __('poin') }}`"></p>

    <div class="relative">
        {{-- A trophy for a clean sweep, a checklist when some steps missed — the icon carries the
        outcome before the words do. --}}
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full text-white shadow-lg"
            :class="allCorrect
                ? 'from-secondary shadow-secondary/30 bg-gradient-to-br to-amber-600'
                : 'bg-gradient-to-br from-amber-400 to-amber-600 shadow-amber-500/25'">
            <template x-if="allCorrect">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 21h8m-4-4v4M6 4h12v4a6 6 0 11-12 0zM6 6H4a2 2 0 002 4m12-4h2a2 2 0 01-2 4" />
                </svg>
            </template>
            <template x-if="!allCorrect">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01" />
                </svg>
            </template>
        </span>

        <span class="sq-label mt-2.5 block" :class="allCorrect ? 'text-secondary' : 'text-amber-600'"
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

        {{-- One tick per step, in board order: at a glance the player sees *where* the chronology
        went wrong, not just how many they missed. --}}
        <div class="mt-3 flex flex-wrap justify-center gap-1.5" aria-hidden="true">
            <template x-for="(item, pos) in items" :key="'tick' + item.i">
                <span class="h-1.5 w-5 rounded-full transition-colors"
                    :class="wrongIdx.includes(pos) ? 'bg-red-300' : 'bg-emerald-500'"></span>
            </template>
        </div>
    </div>
</div>
