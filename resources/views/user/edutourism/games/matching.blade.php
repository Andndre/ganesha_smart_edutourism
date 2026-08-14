{{--
    Matching game — 2 modes via config.mode:
    - "match": draft all left-right pairs, then press "Periksa Jawaban" to validate.
      config: { prompt?, pairs: [{left, right, image?, audio?, explanation?}], penalty? }
    - "pick": scavenger hunt with decoys — pick N correct cards out of M.
      config: { prompt?, items: [{label, icon?, image?, correct, explanation?}], pick_count, penalty? }
    Scoring: match → points - penalty*mistakes (min 20% of points);
             pick  → (correct - wrong*penalty_ratio) / pick_count * points, min 0.
    Emits: mission-complete {id, earned}

    Presentation notes:
    - Drafted pairs are shown twice over — a numbered colour badge AND an SVG curve through the
      column gutter. The badge is the reliable channel (it survives reflow, zoom and empty configs);
      the curve is the delightful one. Neither is load-bearing on its own.
    - The `edu-*` animation classes and the `eduSfx` sound cues used below live in the shared
      partials/game-fx.blade.php, so the sequence game inherits exactly the same feel.
--}}
@php($cfg = $mission->localizedConfig())
<div x-data="eduGameMatching(@js($cfg), @js($mission->id), @js($mission->points))" class="space-y-4">

    @if (!empty($cfg['prompt']))
        <p class="text-sm leading-relaxed text-gray-600">{{ $cfg['prompt'] }}</p>
    @endif

    @if (($cfg['mode'] ?? 'match') === 'match')
        {{-- The board is the positioning context for the SVG overlay, and the gutter is widened to
        gap-x-5 so the connecting curves read as curves rather than as nicks. --}}
        <div class="relative" x-ref="board">
            <svg class="pointer-events-none absolute inset-0 z-0 h-full w-full overflow-visible" x-ref="links"
                aria-hidden="true"></svg>

            <div class="relative z-10 grid grid-cols-[0.85fr_1.15fr] gap-x-5 gap-y-3">
                <div class="space-y-3">
                    {{-- The entrance animation lives on this wrapper, not on the card. Both set the
                    `animation` shorthand, so sharing an element would let whichever rule comes last
                    in the stylesheet silently cancel the other — the shake and the glow would never
                    play. Separate elements let each run on its own. --}}
                    <template x-for="(item, n) in lefts" :key="'l' + item.i">
                        {{-- `relative` so the badge and verdict marks can hang off this wrapper
                        rather than off the button, which clips its own overflow. --}}
                        <div class="edu-rise relative" :style="{ animationDelay: `${n * 55}ms` }">
                            <button type="button" @click="pickLeft(item.i)" :data-left="item.i"
                                class="edu-card relative w-full min-h-11 overflow-hidden rounded-2xl border-2 p-3 text-left text-sm font-semibold shadow-sm active:scale-95"
                                :class="{
                                    'edu-locked': done,
                                    'edu-shine': done && resolved(item.i) && pairResult(item.i) === 'correct',
                                    'edu-flash edu-shake': done && resolved(item.i) && pairResult(item.i) === 'wrong',
                                    'border-emerald-500 bg-emerald-50 text-emerald-800': done && resolved(item.i) && pairResult(item.i) === 'correct',
                                    'border-red-400 bg-red-50 text-red-700': done && resolved(item.i) && pairResult(item.i) === 'wrong',
                                    'border-gray-200 bg-white text-gray-700': done ? !resolved(item.i) : (selectedLeft !== item.i && !draftForLeft(item.i)),
                                    'edu-pulse-glow border-primary from-primary to-primary/85 bg-gradient-to-br text-white shadow-md': !done && selectedLeft === item.i,
                                    'bg-white text-gray-700': !done && selectedLeft !== item.i && draftForLeft(item.i),
                                }"
                                :style="{ borderColor: !done && selectedLeft !== item.i && draftForLeft(item.i) ? pairColor(item.i) : null }">

                                <template x-if="item.image">
                                    <img :src="item.image" alt="" class="mx-auto mb-1 block h-12 w-12 rounded-lg object-cover"
                                        x-on:error="$event.target.style.display='none'">
                                </template>
                                <template x-if="!item.image && item.icon">
                                    <span class="mx-auto mb-1 block text-center text-2xl" x-text="item.icon"></span>
                                </template>

                                <span class="inline-flex items-center gap-1.5">
                                    <span x-text="item.left" :class="item.image || item.icon ? 'block text-center text-xs' : ''"></span>
                                    {{-- A span, not a button: a nested <button> is invalid HTML and the
                                    parser hoists it out of its parent, which would break the card. --}}
                                    <template x-if="item.audio">
                                        <span role="button" tabindex="0" @click.stop="playAudio(item.audio)"
                                            @keydown.enter.stop.prevent="playAudio(item.audio)"
                                            @keydown.space.stop.prevent="playAudio(item.audio)"
                                            class="edu-card edu-tap inline-flex h-5 w-5 shrink-0 cursor-pointer items-center justify-center rounded-full active:scale-90"
                                            :class="selectedLeft === item.i ? 'bg-white/25 text-white' : 'bg-primary/10 text-primary'"
                                            :aria-label="@js(__('Putar audio'))">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </span>
                                    </template>
                                </span>

                            </button>

                            {{-- Pair badge: the reliable half of the pairing signal. --}}
                            <template x-if="!done && draftForLeft(item.i)">
                                <span class="edu-badge-pop absolute -right-2 -top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full text-xs font-black text-white shadow-md ring-2 ring-white"
                                    :style="{ background: pairColor(item.i) }" x-text="pairNumber(item.i)"></span>
                            </template>

                            {{-- Verdict marks, only once this pair's turn in the reveal arrives. --}}
                            <template x-if="done && resolved(item.i) && pairResult(item.i) === 'correct'">
                                <span class="edu-badge-pop absolute -right-2 -top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-white shadow-md ring-2 ring-white">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            </template>
                            <template x-if="done && resolved(item.i) && pairResult(item.i) === 'wrong'">
                                <span class="edu-badge-pop absolute -right-2 -top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-md ring-2 ring-white">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </span>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, n) in rights" :key="'r' + item.i">
                        <div class="edu-rise relative" :style="{ animationDelay: `${(n + lefts.length) * 55}ms` }">
                            <button type="button" @click="pickRight(item.i)" :data-right="item.i"
                                class="edu-card relative w-full min-h-11 overflow-hidden rounded-2xl border-2 p-3 text-left text-sm font-medium shadow-sm active:scale-95"
                                :class="{
                                    'edu-locked': done,
                                    'edu-shine': done && resolved(item.i) && draftForRight(item.i)?.leftI === item.i,
                                    'edu-flash edu-shake': done && draftForRight(item.i) && draftForRight(item.i).leftI !== item.i && resolved(draftForRight(item.i).leftI),
                                    'border-emerald-500 bg-emerald-50 text-emerald-800': done && resolved(item.i) && draftForRight(item.i)?.leftI === item.i,
                                    'border-red-400 bg-red-50 text-red-700': done && draftForRight(item.i) && draftForRight(item.i).leftI !== item.i && resolved(draftForRight(item.i).leftI),
                                    'border-gray-200 bg-white text-gray-700': !draftForRight(item.i),
                                    'bg-white text-gray-700': !done && draftForRight(item.i),
                                }"
                                :style="{ borderColor: !done && draftForRight(item.i) ? pairColor(draftForRight(item.i).leftI) : null }">

                                <template x-if="item.icon">
                                    <span class="mr-1" x-text="item.icon"></span>
                                </template>
                                <span x-text="item.right"></span>
                            </button>

                            <template x-if="!done && draftForRight(item.i)">
                                <span class="edu-badge-pop absolute -left-2 -top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full text-xs font-black text-white shadow-md ring-2 ring-white"
                                    :style="{ background: pairColor(draftForRight(item.i).leftI) }"
                                    x-text="pairNumber(draftForRight(item.i).leftI)"></span>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <p x-show="!done" class="text-center text-xs text-gray-400">
            {{ __('Ketuk item di kiri, lalu ketuk pasangannya di kanan.') }}
        </p>

        {{-- x-show sits on the sticky wrapper, not the button: leaving the wrapper mounted would
        park an empty backdrop bar across the bottom of the board. --}}
        <div x-show="!done" class="edu-sticky-cta">
            <button type="button" @click="submitMatch()" :disabled="!allPairsDrafted()"
                class="edu-card bg-primary w-full rounded-2xl py-3.5 text-sm font-bold text-white shadow-md active:scale-95 disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none">
                <span>{{ __('Periksa Jawaban') }} (<span x-text="drafts.length"></span>/<span
                        x-text="cfg.pairs.length"></span>)</span>
            </button>
        </div>

        <template x-if="done">
            <div class="space-y-3">
                @include('user.edutourism.games.partials.match-victory')

                <template x-for="(item, idx) in cfg.pairs" :key="'exp-' + idx">
                    <div x-show="item.explanation" class="edu-rise rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-3 text-sm text-emerald-800 shadow-sm"
                        :style="{ animationDelay: `${revealDelay + 140 + idx * 70}ms` }">
                        <p class="font-semibold" x-text="item.left + ' ↔ ' + item.right"></p>
                        <p class="mt-0.5 leading-relaxed opacity-90" x-text="item.explanation"></p>
                    </div>
                </template>

                <div class="edu-sticky-cta">
                    <button type="button" @click="finish()"
                        class="edu-card bg-primary w-full rounded-2xl py-3.5 text-sm font-bold text-white shadow-md active:scale-95">
                        {{ __('Lanjut') }}
                    </button>
                </div>
            </div>
        </template>
    @else
        <div class="grid grid-cols-2 gap-3">
            <template x-for="(item, idx) in cfg.items" :key="idx">
                {{-- The marks are positioned against this wrapper, not against the button: the card
                clips its own overflow (for the thumbnail corners), so a badge hung off the button
                itself would be sliced down to a sliver at the corner. --}}
                <div class="edu-rise relative" :style="{ animationDelay: `${idx * 45}ms` }">
                    <button type="button" @click="togglePick(idx)" :disabled="pickDone"
                        class="edu-card relative min-h-16 w-full overflow-hidden rounded-2xl border-2 p-3 text-sm font-semibold shadow-sm active:scale-95"
                        :class="[
                            pickDone ? pickResultClass(idx) : '',
                            {
                                'edu-locked': pickDone,
                                'edu-shine': pickDone && resolved(idx) && pickState(idx) === 'correct',
                                'edu-flash edu-shake': pickDone && resolved(idx) && pickState(idx) === 'wrong',
                                'edu-pulse-glow border-primary from-primary to-primary/85 bg-gradient-to-br text-white shadow-md': !pickDone && pickState(idx) === 'selected',
                                'border-gray-200 bg-white text-gray-700': !pickDone && pickState(idx) !== 'selected',
                            },
                        ]">

                        <template x-if="item.image">
                            <img :src="item.image" alt="" class="mx-auto mb-1 block h-12 w-12 rounded-lg object-cover"
                                x-on:error="$event.target.style.display='none'">
                        </template>
                        <template x-if="!item.image && item.icon">
                            <span class="mx-auto mb-1 block text-center text-2xl" x-text="item.icon"></span>
                        </template>
                        <span x-text="item.label" :class="item.image || item.icon ? 'block text-center text-xs' : ''"></span>
                    </button>

                    {{-- Selection tick while choosing. --}}
                    <template x-if="!pickDone && pickState(idx) === 'selected'">
                        <span class="edu-badge-pop bg-secondary text-charcoal absolute -right-2 -top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full shadow-md ring-2 ring-white">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    </template>

                    {{-- Verdict marks. "missed" flags a correct card the player never picked. --}}
                    <template x-if="pickDone && resolved(idx) && pickState(idx) === 'correct'">
                        <span class="edu-badge-pop absolute -right-2 -top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-white shadow-md ring-2 ring-white">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    </template>
                    <template x-if="pickDone && resolved(idx) && pickState(idx) === 'wrong'">
                        <span class="edu-badge-pop absolute -right-2 -top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-md ring-2 ring-white">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                    </template>
                    <template x-if="pickDone && pickState(idx) === 'missed'">
                        <span class="edu-badge-pop absolute -right-2 -top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-amber-400 text-xs font-black text-white shadow-md ring-2 ring-white">!</span>
                    </template>

                    <template x-if="pickDone && item.explanation">
                        <p class="edu-rise mt-1 rounded-xl px-2 py-1.5 text-xs leading-snug"
                            :style="{ animationDelay: `${revealDelay + 120 + idx * 45}ms` }"
                            :class="pickState(idx) === 'wrong' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'"
                            x-text="item.explanation"></p>
                    </template>
                </div>
            </template>
        </div>

        <div x-show="!pickDone" class="edu-sticky-cta">
            <button type="button" @click="submitPick()" :disabled="picked.length === 0"
                class="edu-card bg-primary w-full rounded-2xl py-3.5 text-sm font-bold text-white shadow-md active:scale-95 disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none">
                <span>{{ __('Periksa Pilihan') }} (<span x-text="picked.length"></span>/<span
                        x-text="cfg.pick_count"></span>)</span>
            </button>
        </div>

        <template x-if="pickDone">
            <div class="space-y-3">
                @include('user.edutourism.games.partials.match-victory')

                <div class="edu-sticky-cta">
                    <button type="button" @click="finish()"
                        class="edu-card bg-primary w-full rounded-2xl py-3.5 text-sm font-bold text-white shadow-md active:scale-95">
                        {{ __('Lanjut') }}
                    </button>
                </div>
            </div>
        </template>
    @endif
</div>
