{{--
    Riddle reveal.
    config: { riddle, answers: ["merajan", "sanggah"], hint?, success_text?, explanation? }
    Matching: case-insensitive, punctuation-stripped, Levenshtein distance ≤ 1 (light typo tolerance).
    Scoring: points - 20*(wrong attempts), min 20% of points. A wrong guess no longer ends the
    mission — the player keeps guessing (and keeps losing 20 a go) until they solve it, give up, or
    hit the 4-attempt cap, which is where the score floor is anyway.
    Emits: mission-complete {id, earned}

    Presentation notes:
    - The `rd-*` classes, the `edu-*` motion vocabulary and the sound cues all live in
      partials/riddle-script.blade.php and partials/game-fx.blade.php. Nothing here defines its own.
    - The wrong-answer state deliberately drops the border/ring utilities in favour of
      `.rd-field-wrong`, which owns those properties outright. See the note in the script partial.
--}}

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameRiddle(@js($cfg), @js($mission->id), @js($mission->points))" class="space-y-4">

    {{-- The live point value, not the ceiling: the number has to visibly fall for the attempt
    pips under the input to mean anything. --}}
    <div class="flex items-center justify-between gap-2">
        <span class="edu-chip">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9.1 9a3 3 0 015.8 1c0 2-3 3-3 3M12 17h.01" />
                <circle cx="12" cy="12" r="9.5" />
            </svg>
            {{ __('Teka-Teki') }}
        </span>
        <span class="edu-chip edu-chip-gold">
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 2l2.9 6.3 6.6.8-4.9 4.5 1.3 6.6L12 17l-5.9 3.2 1.3-6.6L2.5 9.1l6.6-.8z" />
            </svg>
            <span x-text="pointsNow"></span> {{ __('poin') }}
        </span>
    </div>

    {{-- The riddle itself, read as a page torn from a lontar: aged paper, a gold seal down the
    reading edge, and an oversized opening quote ghosted behind the text. One light sweep on
    entry (edu-shine) and then it sits still — it is the thing being read, not an animation. --}}
    <div class="rd-parchment edu-slide-up edu-shine relative overflow-hidden rounded-2xl border border-amber-200/60 p-5 pl-6 shadow-sm"
        style="--edu-shine-delay:.5s">
        <span class="rd-seal" aria-hidden="true"></span>
        <span class="rd-quote" aria-hidden="true">&ldquo;</span>
        <p class="font-display text-charcoal relative text-base font-bold italic leading-relaxed">
            &ldquo;{{ $cfg['riddle'] ?? '' }}&rdquo;
        </p>
    </div>

    @if (!empty($cfg['hint']))
        {{-- Not a <details>: the panel has to open on its own after a miss, which needs the state
        in the component. The bulb keeps glowing while the hint is shut, so it reads as an offer. --}}
        <div>
            <button type="button" @click="toggleHint()" :aria-expanded="hintOpen ? 'true' : 'false'"
                aria-controls="rd-hint-{{ $mission->id }}"
                class="flex min-h-11 w-full items-center gap-2.5 rounded-xl border border-amber-200/70 bg-amber-50/60 px-3.5 py-2.5 text-left transition active:scale-[.98]"
                :class="hintOpen && 'rounded-b-none border-b-transparent'">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600"
                    :class="!hintOpen && 'rd-bulb'">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            d="M12 2a7 7 0 00-4 12.7V17a1 1 0 001 1h6a1 1 0 001-1v-2.3A7 7 0 0012 2zM9 20h6v.5a1.5 1.5 0 01-1.5 1.5h-3A1.5 1.5 0 019 20.5z" />
                    </svg>
                </span>
                <span class="flex-1 text-xs font-extrabold text-amber-800"
                    x-text="hintOpen ? @js(__('Sembunyikan petunjuk')) : @js(__('Butuh petunjuk?'))"></span>
                <svg class="h-4 w-4 shrink-0 text-amber-500 transition-transform duration-300" fill="none"
                    stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"
                    :class="hintOpen && 'rotate-180'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                </svg>
            </button>

            {{-- Slide-down + fade on transform/opacity only, so it stays on the compositor —
            an animated height would not. --}}
            <div id="rd-hint-{{ $mission->id }}" x-show="hintOpen" x-cloak x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="rounded-b-xl border border-t-0 border-amber-200 bg-amber-100/50 px-3.5 py-3 text-sm leading-relaxed text-amber-900">
                {{ $cfg['hint'] }}
            </div>
        </div>
    @endif

    <template x-if="!checked">
        <div class="space-y-3">
            <div>
                {{-- The wrapper takes the shake so the input keeps its own focus transform-free;
                the input takes the ring flash. --}}
                <div :class="wrong && 'edu-shake'">
                    <input type="text" x-model="guess" @input="onType()" @keydown.enter="submit()"
                        class="focus:border-primary focus:ring-primary/15 min-h-11 w-full rounded-xl border-2 bg-white p-3.5 text-sm font-semibold text-gray-800 outline-none transition placeholder:font-medium placeholder:text-gray-400 focus:ring-4"
                        :class="wrong ? 'rd-field-wrong' : 'border-gray-200'"
                        placeholder="{{ __('Ketik jawabanmu...') }}" aria-label="{{ __('Ketik jawabanmu...') }}"
                        autocomplete="off" autocapitalize="off"
                        spellcheck="false" />
                </div>

                {{-- Verdict line and attempt pips share the row: the message says what happened,
                the pips say what it cost. --}}
                <div class="mt-2 flex min-h-5 items-center justify-between gap-3">
                    <p class="text-xs font-bold text-red-500" x-show="wrong" x-cloak
                        :class="wrong && 'edu-rise'" role="alert"
                        x-text="emptyGuess ? @js(__('Tulis jawabanmu dulu')) : @js(__('Belum tepat, coba lagi!'))"></p>
                    <div class="ml-auto flex shrink-0 items-center gap-1.5" x-show="attempts > 0" x-cloak
                        :aria-label="@js(__('Percobaan terpakai')) + ': ' + attempts">
                        <template x-for="n in maxAttempts" :key="n">
                            <span class="rd-pip" :class="n <= attempts && 'rd-pip-spent'" aria-hidden="true"></span>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Only after a real struggle, and quiet enough that it never competes with the CTA. --}}
            <div class="text-center" x-show="canGiveUp" x-cloak x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <button type="button" @click="reveal()"
                    class="min-h-11 px-3 text-xs font-bold text-gray-400 underline decoration-dotted underline-offset-4 transition active:scale-95">
                    {{ __('Menyerah & lihat jawaban') }}
                </button>
            </div>

            <div class="edu-sticky-cta">
                <button type="button" @click="submit()"
                    class="from-primary shadow-primary/25 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r to-emerald-700 py-3.5 text-sm font-bold text-white shadow-md transition-transform hover:shadow-lg active:scale-95">
                    {{-- Sparkles rather than the obvious question mark: at 16px a `?` inside a
                    circle turns to mud, and the label already says what the button does. --}}
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.8 15.9L9 18.75l-.8-2.85a4.5 4.5 0 00-3.1-3.09L2.25 12l2.85-.81a4.5 4.5 0 003.1-3.09L9 5.25l.8 2.85a4.5 4.5 0 003.1 3.09L15.75 12l-2.85.81a4.5 4.5 0 00-3.1 3.09zM18.26 8.72L18 9.75l-.26-1.03a3.38 3.38 0 00-2.45-2.46L14.25 6l1.04-.26a3.38 3.38 0 002.45-2.45L18 2.25l.26 1.04a3.38 3.38 0 002.46 2.45L21.75 6l-1.03.26a3.38 3.38 0 00-2.46 2.46z" />
                    </svg>
                    {{ __('Jawab Teka-Teki') }}
                </button>
            </div>
        </div>
    </template>

    <template x-if="checked">
        <div class="space-y-3">
            {{-- Solved and revealed share one panel: same shape, different accent, so the reveal
            never feels like a punishment screen. --}}
            <div class="edu-slide-up relative overflow-hidden rounded-2xl border p-6 text-center shadow-md"
                :class="solved
                    ? 'border-amber-200/70 bg-gradient-to-br from-white to-amber-50/70'
                    : 'border-gray-200 bg-gradient-to-br from-white to-gray-50'"
                role="status" aria-live="polite">

                {{-- Warm halo behind the badge; blurred, so it reads as light rather than a shape. --}}
                <div class="pointer-events-none absolute -top-12 left-1/2 h-28 w-28 -translate-x-1/2 rounded-full blur-2xl"
                    :class="solved ? 'bg-amber-300/40' : 'bg-gray-300/30'" aria-hidden="true"></div>

                {{-- The panel slides in, then its contents pop — two animations, so two elements:
                on one element the later rule in game-fx would silently cancel the earlier. --}}
                <div class="edu-pop relative" style="animation-delay:180ms">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full shadow-lg"
                        :class="solved
                            ? 'bg-gradient-to-br from-emerald-500 to-primary text-white shadow-emerald-600/25'
                            : 'bg-gradient-to-br from-gray-400 to-gray-500 text-white shadow-gray-500/20'">
                        {{-- Ring first, then the tick drawn inside it — both are stroke-dash
                        animations, so nothing here reflows. --}}
                        <template x-if="solved">
                            <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle class="rd-ring" cx="12" cy="12" r="10" />
                                <path class="rd-tick" d="M7.5 12.4l3 3 6-6.4" />
                            </svg>
                        </template>
                        <template x-if="!solved">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z" />
                                <circle cx="12" cy="12" r="2.6" />
                            </svg>
                        </template>
                    </span>

                    <p class="edu-label mt-3.5" :class="solved ? 'text-amber-600/80' : 'text-gray-400'"
                        x-text="solved ? @js(__('Terpecahkan')) : @js(__('Jawabannya'))"></p>

                    {{-- The word itself, lit by a gold sweep. No text-colour utility here: the
                    gradient is clipped to the glyphs and owns the colour. --}}
                    <p class="font-display mt-1 text-3xl font-black capitalize"
                        :class="solved ? 'rd-answer' : 'text-charcoal'" x-text="answerShown"></p>

                    <p class="mt-2 text-sm font-medium text-gray-500" x-show="solved">
                        {{ $cfg['success_text'] ?? __('Tepat sekali!') }}</p>
                    <p class="mt-2 text-sm font-medium text-gray-500" x-show="!solved">
                        {{ __('Tidak apa-apa — teka-teki ini memang berat.') }}</p>

                    <p class="text-primary mt-3 text-3xl font-black tabular-nums">
                        +<span x-text="earned"></span>
                        <span class="text-sm font-bold uppercase tracking-wide">{{ __('poin') }}</span>
                    </p>
                </div>
            </div>

            @if (!empty($cfg['explanation']))
                <div class="edu-rise rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50/50 p-4 shadow-sm"
                    style="animation-delay:220ms">
                    <div class="flex items-center gap-2">
                        <span class="bg-secondary/15 text-secondary flex h-6 w-6 items-center justify-center rounded-lg">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M12 2a7 7 0 00-4 12.7V17a1 1 0 001 1h6a1 1 0 001-1v-2.3A7 7 0 0012 2zM9 20h6v.5a1.5 1.5 0 01-1.5 1.5h-3A1.5 1.5 0 019 20.5z" />
                            </svg>
                        </span>
                        <h4 class="edu-label text-amber-700/80">{{ __('Tahukah Kamu') }}</h4>
                    </div>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $cfg['explanation'] }}</p>
                </div>
            @endif

            <div class="edu-sticky-cta">
                <button type="button" @click="continueMission()"
                    class="from-primary shadow-primary/25 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r to-emerald-700 py-3.5 text-sm font-bold text-white shadow-lg transition-transform active:scale-95">
                    {{ __('Lanjut') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>
