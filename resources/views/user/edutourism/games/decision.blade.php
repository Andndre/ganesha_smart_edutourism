{{--
    Decision / scenario branching game.
    config: { scenarios: [{ text, image?, image_after?, options: [{text, correct, explanation?}] }] }
    image/image_after support the Route 3 "visual before/after" variant (Day 4) — optional, and
    nothing is seeded with them yet, so the layout has to read correctly with no scene at all.
    Scoring: points split evenly per scenario; correct first pick = full share, wrong = 0 for that scenario.
    Emits: mission-complete {id, earned}

    Presentation notes:
    - The `edu-*` chrome/motion and the sound cues live in partials/game-fx.blade.php; the
      before/after frame (`dc-scene`) lives in partials/decision-script.blade.php. Nothing here
      defines its own.
    - Verdict colours come back from optionClass()/markClass() as Tailwind utilities so the sheet
      never has to fight them; see the note in the script partial.
--}}

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameDecision(@js($cfg), @js($mission->id), @js($mission->points))" class="space-y-4">

    {{-- Run header: where you are, what you have banked, and the shape of the run so far. --}}
    <div class="space-y-2.5">
        <div class="flex items-center justify-between gap-2">
            <span class="edu-chip">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v6m0 0l7 4-7 4-7-4 7-4m0 10v2" />
                </svg>
                {{ __('Skenario') }} <span x-text="idx + 1"></span>/<span x-text="total"></span>
            </span>
            {{-- Pops on a good call, so banking a point is felt and not just displayed. --}}
            <span class="edu-chip edu-chip-gold" :class="lastCorrect && 'edu-pop'">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 2l2.9 6.3 6.6.8-4.9 4.5 1.3 6.6L12 17l-5.9 3.2 1.3-6.6L2.5 9.1l6.6-.8z" />
                </svg>
                <span x-text="earnedSoFar"></span> {{ __('poin') }}
            </span>
        </div>

        {{-- One bar per scenario. Decorative: the chip above already announces the position. --}}
        <div class="flex items-center gap-1.5" aria-hidden="true">
            <template x-for="n in total" :key="'seg-' + n">
                <span class="edu-seg">
                    <span class="edu-seg-fill" :class="segClass(n - 1)"></span>
                </span>
            </template>
        </div>
    </div>

    {{-- Everything below re-mounts when `idx` changes — an x-for over a single-item array keyed by
    the scenario index. That is what re-arms the entrance animations on every scenario; an x-if
    would keep the same nodes and the new scenario would simply pop in with no transition. --}}
    <template x-for="s in [scenario]" :key="'sc-' + idx">
        <div class="space-y-4">

            {{-- The scene, when the mission carries one. Both frames are stacked; the "after" is
            only uncovered by a decision that earns it. --}}
            <template x-if="s.image">
                <div class="dc-scene edu-slide-up rounded-2xl shadow-sm">
                    <img :src="s.image" class="dc-scene-img" :class="showAfter && 'is-hidden'" alt=""
                        loading="lazy" />
                    <template x-if="s.image_after">
                        <img :src="s.image_after" class="dc-scene-img" :class="!showAfter && 'is-hidden'" alt=""
                            loading="lazy" />
                    </template>
                    <template x-if="s.image_after">
                        <span class="dc-scene-tag"
                            x-text="showAfter ? @js(__('Sesudah')) : @js(__('Sebelum'))"></span>
                    </template>
                </div>
            </template>

            <p class="font-display text-charcoal edu-rise text-base font-bold leading-snug" x-text="s.text"></p>

            <div class="space-y-3">
                <template x-for="(opt, oIdx) in s.options" :key="idx + '-' + oIdx">
                    {{-- The wrapper owns the staggered entrance so it can never cancel the verdict
                    animation (edu-shake / edu-shine) that lands on the button itself. --}}
                    <div class="edu-rise" :style="{ animationDelay: `${oIdx * 70}ms` }">
                        <button type="button" @click="choose(oIdx)" :disabled="checked"
                            class="relative flex min-h-14 w-full items-center gap-3.5 overflow-hidden rounded-xl border-2 p-4 text-left text-sm font-medium transition active:scale-[0.98] disabled:cursor-default"
                            :class="optionClass(oIdx)" :aria-pressed="selected === oIdx ? 'true' : 'false'">

                            {{-- Letter before the check, verdict glyph after it. `relative` on both
                            children so they paint above edu-flash's wash and edu-shine's sweep,
                            which are absolutely-positioned pseudo-elements on this button. --}}
                            <span
                                class="edu-mark relative flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-black"
                                :class="markClass(oIdx)">
                                <template x-if="markKind(oIdx) === 'letter'">
                                    <span x-text="letter(oIdx)"></span>
                                </template>
                                {{-- A leaf, not a tick: the verdict here is ecological, not a score. --}}
                                <template x-if="markKind(oIdx) === 'leaf'">
                                    <svg class="edu-badge-pop h-4.5 w-4.5" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M5 19c8 1 14-4 14-13-7-1-13 2-14 7-.5 2.5 0 4.5 0 6zm0 0c1.5-4 4-6.5 8-8.5" />
                                    </svg>
                                </template>
                                <template x-if="markKind(oIdx) === 'warn'">
                                    <svg class="edu-badge-pop h-4.5 w-4.5" fill="none" stroke="currentColor"
                                        stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v4m0 3h.01M10.3 3.9L2.4 17.5a2 2 0 001.7 3h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z" />
                                    </svg>
                                </template>
                            </span>

                            <span class="relative flex-1" x-text="opt.text"></span>
                        </button>
                    </div>
                </template>
            </div>

            {{-- The consequence. Drops out of the options above rather than rising from below. --}}
            <template x-if="checked && s.options[chosen].explanation">
                <div class="edu-drop rounded-2xl border p-4 shadow-sm"
                    :class="lastCorrect
                        ? 'border-emerald-200/70 bg-emerald-50/90'
                        : 'border-red-200/70 bg-red-50/90'"
                    role="status" aria-live="polite">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg"
                            :class="lastCorrect ? 'bg-emerald-500/15 text-emerald-600' : 'bg-red-500/15 text-red-500'">
                            <template x-if="lastCorrect">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5 19c8 1 14-4 14-13-7-1-13 2-14 7-.5 2.5 0 4.5 0 6zm0 0c1.5-4 4-6.5 8-8.5" />
                                </svg>
                            </template>
                            <template x-if="!lastCorrect">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v4m0 3h.01M10.3 3.9L2.4 17.5a2 2 0 001.7 3h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z" />
                                </svg>
                            </template>
                        </span>
                        <h4 class="edu-label" :class="lastCorrect ? 'text-emerald-700/80' : 'text-red-700/80'"
                            x-text="lastCorrect ? @js(__('Dampak Baik')) : @js(__('Dampak Buruk'))"></h4>
                    </div>
                    <p class="mt-2 text-sm leading-relaxed"
                        :class="lastCorrect ? 'text-emerald-800' : 'text-red-800'"
                        x-text="s.options[chosen].explanation"></p>
                </div>
            </template>
        </div>
    </template>

    {{-- x-show sits on the sticky wrapper, not the button: leaving the wrapper mounted would park
    an empty backdrop bar across the bottom of the scenario. The bar slides in from below the
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
            <span x-text="idx + 1 < total ? @js(__('Skenario Berikutnya')) : @js(__('Selesai'))"></span>
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" />
            </svg>
        </button>
    </div>
</div>
