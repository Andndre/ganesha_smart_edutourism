{{--
    Multiple-choice quiz (Point 1 "Unlock the Village").
    config: { questions: [{ prompt, option_a, option_b, option_c, option_d, correct_option: 'A'|'B'|'C'|'D', explanation? }] }
    Blank option_* keys are dropped by the factory, so a three-choice question renders three cards.
    Scoring: points split evenly per question; correct pick = full share, wrong = 0 for that question.
    Emits: mission-complete {id, earned}

    Presentation notes:
    - Every class here is shared chrome from partials/game-fx.blade.php; this game defines no CSS
      of its own. It reads as a sibling of the decision game on purpose — same header, same option
      cards, same verdict language — because a player meets both on the same route.
    - Verdict colours come back from optionClass()/markClass() as Tailwind utilities, so the
      unlayered game-fx sheet never has to out-specify them.
--}}

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameQuiz(@js($cfg), @js($mission->id), @js($mission->points))" class="space-y-4">

    {{-- Run header: where you are, what you have banked, and the shape of the run so far. --}}
    <div class="space-y-2.5">
        <div class="flex items-center justify-between gap-2">
            <span class="edu-chip">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.1 9a3 3 0 015.8 1c0 2-3 3-3 3M12 17h.01" />
                    <circle cx="12" cy="12" r="9.5" />
                </svg>
                {{ __('Soal') }} <span x-text="idx + 1"></span>/<span x-text="total"></span>
            </span>
            {{-- Pops on a correct answer, so banking a point is felt and not just displayed. --}}
            <span class="edu-chip edu-chip-gold" :class="lastCorrect && 'edu-pop'">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 2l2.9 6.3 6.6.8-4.9 4.5 1.3 6.6L12 17l-5.9 3.2 1.3-6.6L2.5 9.1l6.6-.8z" />
                </svg>
                <span x-text="earnedSoFar"></span> {{ __('poin') }}
            </span>
        </div>

        {{-- One bar per question. Decorative: the chip above already announces the position. --}}
        <div class="flex items-center gap-1.5" aria-hidden="true">
            <template x-for="n in total" :key="'seg-' + n">
                <span class="edu-seg">
                    <span class="edu-seg-fill" :class="segClass(n - 1)"></span>
                </span>
            </template>
        </div>
    </div>

    {{-- Everything below re-mounts when `idx` changes — an x-for over a single-item array keyed by
    the question index. That is what re-arms the entrance animations on every question; an x-if
    would keep the same nodes and the next question would simply pop in with no transition. --}}
    <template x-for="q in [question]" :key="'q-' + idx">
        <div class="space-y-4">

            <p class="font-display text-charcoal edu-rise text-base font-bold leading-snug" x-text="q.prompt"></p>

            <div class="space-y-3">
                <template x-for="(opt, oIdx) in options" :key="idx + '-' + opt.letter">
                    {{-- The wrapper owns the staggered entrance so it can never cancel the verdict
                    animation (edu-shake / edu-shine) that lands on the button itself. --}}
                    <div class="edu-rise" :style="{ animationDelay: `${oIdx * 70}ms` }">
                        <button type="button" @click="choose(opt.letter)" :disabled="checked"
                            class="relative flex min-h-14 w-full items-center gap-3.5 overflow-hidden rounded-xl border-2 p-4 text-left text-sm font-medium transition active:scale-[0.98] disabled:cursor-default"
                            :class="optionClass(opt.letter)"
                            :aria-pressed="selected === opt.letter ? 'true' : 'false'">

                            {{-- Letter before the check, verdict glyph after it. `relative` on both
                            children so they paint above edu-flash's wash and edu-shine's sweep,
                            which are absolutely-positioned pseudo-elements on this button. --}}
                            <span
                                class="edu-mark relative flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-black"
                                :class="markClass(opt.letter)">
                                <template x-if="markKind(opt.letter) === 'letter'">
                                    <span x-text="opt.letter"></span>
                                </template>
                                <template x-if="markKind(opt.letter) === 'check'">
                                    <svg class="edu-badge-pop h-4.5 w-4.5" fill="none" stroke="currentColor"
                                        stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </template>
                                <template x-if="markKind(opt.letter) === 'cross'">
                                    <svg class="edu-badge-pop h-4.5 w-4.5" fill="none" stroke="currentColor"
                                        stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </template>
                            </span>

                            <span class="relative flex-1" x-text="opt.text"></span>
                        </button>
                    </div>
                </template>
            </div>

            {{-- The explanation. Drops out of the options above rather than rising from below. --}}
            <template x-if="checked && q.explanation">
                <div class="edu-drop rounded-2xl border p-4 shadow-sm"
                    :class="lastCorrect
                        ? 'border-emerald-200/70 bg-emerald-50/90'
                        : 'border-red-200/70 bg-red-50/90'"
                    role="status" aria-live="polite">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg"
                            :class="lastCorrect ? 'bg-emerald-500/15 text-emerald-600' : 'bg-red-500/15 text-red-500'">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M12 2a7 7 0 00-4 12.7V17a1 1 0 001 1h6a1 1 0 001-1v-2.3A7 7 0 0012 2zM9 20h6v.5a1.5 1.5 0 01-1.5 1.5h-3A1.5 1.5 0 019 20.5z" />
                            </svg>
                        </span>
                        <h4 class="edu-label" :class="lastCorrect ? 'text-emerald-700/80' : 'text-red-700/80'"
                            x-text="lastCorrect ? @js(__('Jawaban Benar')) : @js(__('Jawaban Kurang Tepat'))"></h4>
                    </div>
                    <p class="mt-2 text-sm leading-relaxed"
                        :class="lastCorrect ? 'text-emerald-800' : 'text-red-800'" x-text="q.explanation"></p>
                </div>
            </template>
        </div>
    </template>

    {{-- x-show sits on the sticky wrapper, not the button: leaving the wrapper mounted would park
    an empty backdrop bar across the bottom of the question. The bar slides in from below the
    viewport, which is where translate-y-full puts it on a fixed element. --}}
    <div x-show="selected !== null && !checked" x-cloak class="edu-sticky-cta"
        x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-full opacity-0">
        <button type="button" @click="check()"
            class="from-primary shadow-primary/25 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r to-emerald-700 py-3.5 text-sm font-bold text-white shadow-md transition-transform hover:shadow-lg active:scale-95">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M12 3l7 4v5a9 9 0 11-14 0V7z" />
            </svg>
            {{ __('Periksa') }}
        </button>
    </div>

    <div x-show="checked" x-cloak class="edu-sticky-cta" x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0">
        <button type="button" @click="next()"
            class="from-primary shadow-primary/25 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r to-emerald-700 py-3.5 text-sm font-bold text-white shadow-lg transition-transform active:scale-95">
            <span x-text="idx + 1 < total ? @js(__('Soal Berikutnya')) : @js(__('Selesai'))"></span>
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" />
            </svg>
        </button>
    </div>
</div>
