{{--
    Sequence / timeline puzzle.
    config: { prompt?, explanation?, reveal_first?: bool, items: [{text}] } — items listed in
    CORRECT order, shuffled on load.
    reveal_first: items start face-down ("find the hidden facts"); tap each to flip it over, then
    order them.
    time_limit_seconds: optional countdown for the order phase (e.g. Route 2 "Escape the Timeline");
    starts when the order phase begins, auto-submits once it hits 0.
    Scoring: one check only. All correct pays full points; otherwise partial credit in
    proportion to how many steps landed in the right place, floored at 20% of points.
    Emits: mission-complete {id, earned}

    Presentation notes:
    - The `edu-*` classes and the `eduSfx` cues come from partials/game-fx.blade.php; everything
      prefixed `sq-` below is specific to this game (3D flip, timeline rail, timer ring, drag).
    - Reordering is shift-and-insert, not swap: the dragged card leaves the flow, the gap closes,
      and the rest slide to make room. Displaced rows animate with FLIP — measure before, invert,
      release — so the motion is real layout, not a guess.
    - Card heights vary with text length (the seeded items run one to three lines), so every drag
      measures live geometry via offsetTop/offsetHeight. Those two ignore transforms, which is what
      makes them safe to read mid-drag while the card carries a translate.
    - On touch, a drag can only start on the grip handle — that is what keeps `touch-action: none`
      off the card body, and therefore keeps the mission sheet scrollable on a phone. A mouse has
      no such conflict, so on pointer devices the whole card is grabbable.
    - Three nested elements per row, because three separate things animate: the row owns the drag
      and FLIP transforms, the wrapper owns the entrance, the card owns the verdict. Sharing an
      element would let one `animation`/`transform` silently cancel another.
--}}

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameSequence(@js($cfg), @js($mission->id), @js($mission->points))"
    @close-mission-runner.window="stopTimer()" class="space-y-4">

    @if (!empty($cfg['prompt']))
        <p class="text-sm leading-relaxed text-gray-600">{{ $cfg['prompt'] }}</p>
    @endif

    {{-- Phase 1 (optional): flip the face-down cards to find the hidden facts. --}}
    <template x-if="phase === 'reveal'">
        <div class="space-y-3">
            <div class="flex items-center justify-between px-0.5">
                <span class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">
                    {{ __('Fakta Ditemukan') }}
                </span>
                <span class="text-primary text-xs font-black tabular-nums">
                    <span x-text="revealed.length"></span>/<span x-text="items.length"></span>
                </span>
            </div>

            <div class="space-y-2.5">
                {{-- Deliberately `items` (shuffled) and not `cfg.items`: config lists the facts in
                their correct chronological order, so revealing them from it would hand the player
                the answer one screen before they are asked for it. --}}
                <template x-for="(item, idx) in items" :key="item.i">
                    <div class="edu-rise" :style="{ animationDelay: `${idx * 55}ms` }">
                        <button type="button" class="sq-flip text-left" @click="reveal(idx)"
                            :class="revealed.includes(idx) && 'is-flipped'"
                            :aria-pressed="revealed.includes(idx)"
                            :aria-label="revealed.includes(idx) ? item.text : @js(__('Kartu tertutup')) + ' ' + (idx + 1)">
                            <span class="sq-flip-inner">

                                {{-- Face down: the mystery side. --}}
                                <span
                                    class="sq-face sq-face-front sq-sheen from-primary flex min-h-[92px] items-center gap-4 border-2 border-dashed border-amber-300/70 bg-gradient-to-br to-emerald-800 p-4 shadow-sm"
                                    :style="{ '--sq-sheen-delay': `${idx * 700}ms` }">
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/15 text-lg font-black text-amber-300">?</span>
                                    <span class="flex-1">
                                        <span class="block text-[10px] font-black uppercase tracking-[0.16em] text-amber-300/90">
                                            {{ __('Fakta') }} #<span x-text="idx + 1"></span>
                                        </span>
                                        <span class="mt-0.5 block text-sm font-bold text-white/95">
                                            {{ __('Ketuk untuk mengungkap fakta') }}
                                        </span>
                                    </span>
                                </span>

                                {{-- Face up: sits in the flow, so the card is as tall as its text. --}}
                                <span
                                    class="sq-face sq-face-back flex min-h-[92px] items-center gap-4 border-2 border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    <span class="flex-1 text-sm font-medium leading-relaxed text-gray-700"
                                        x-text="item.text"></span>
                                </span>
                            </span>
                        </button>
                    </div>
                </template>
            </div>

            <div class="edu-sticky-cta">
                <button type="button" @click="startOrder()" :disabled="!allRevealed"
                    class="bg-primary w-full rounded-xl py-3.5 text-sm font-bold text-white shadow-sm transition-transform active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                    :class="allRevealed && 'edu-pulse-glow'">
                    {{ __('Susun Kronologi') }}
                </button>
            </div>
        </div>
    </template>

    {{-- Phase 2: order the items. --}}
    <template x-if="phase === 'order'">
        <div class="space-y-3">

            {{-- Countdown: sticks to the top of the mission scrollport so it stays visible while
            the player works down a long list. --}}
            <template x-if="timeLeft !== null">
                <div class="sticky top-0 z-30 -mx-1 flex justify-center bg-[#FAF9F6] py-1">
                    <div class="flex items-center gap-2 rounded-full border bg-white px-3 py-1.5 shadow-sm transition-colors"
                        :class="timeCritical ? 'border-red-200 sq-throb' : 'border-gray-200'">
                        <span class="relative h-7 w-7">
                            <svg class="h-7 w-7 -rotate-90" viewBox="0 0 40 40" aria-hidden="true">
                                <circle cx="20" cy="20" r="16" fill="none" stroke="currentColor" stroke-width="4"
                                    class="text-gray-100" />
                                <circle cx="20" cy="20" r="16" fill="none" stroke="currentColor" stroke-width="4"
                                    stroke-linecap="round" stroke-dasharray="100.5"
                                    class="sq-timer-ring"
                                    :class="timeCritical ? 'text-red-500' : 'text-primary'"
                                    :style="{ strokeDashoffset: 100.5 * (1 - timeRatio) }" />
                            </svg>
                        </span>
                        <span class="text-sm font-black tabular-nums"
                            :class="timeCritical ? 'animate-pulse text-red-500' : 'text-charcoal'"
                            x-text="timeLabel"></span>
                    </div>
                </div>
            </template>

            {{-- The check is one-shot, so say so before the player commits rather than only in
            the confirm dialog. --}}
            <div x-show="!done" class="flex items-center justify-between px-0.5">
                <span class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">
                    {{ __('Sekali Periksa') }}
                </span>
                <span class="text-primary text-[10px] font-black uppercase tracking-[0.16em]">
                    {{ __('Nilai maks') }} <span x-text="maxPoints"></span>
                </span>
            </div>

            <div class="relative">
                <div class="sq-rail" aria-hidden="true"
                    :class="done && wrongIdx.length === 0 && 'sq-rail-done'"></div>

                <ol class="relative" x-ref="list">
                    <template x-for="(item, pos) in items" :key="item.i">
                        {{-- The row owns the drag + FLIP transforms. --}}
                        {{-- The offset is always written, even at rest: letting the custom property
                        disappear and come back leaves a frame where the transform reads an unset
                        var, which the browser throws out along with the rest of the declaration. --}}
                        <li class="sq-row" :data-row="item.i" :class="dragIdx === pos && 'is-dragging'"
                            :style="{ '--sq-dy': (dragIdx === pos ? dragY : 0) + 'px' }">
                            {{-- The wrapper owns the entrance animation, on its own element so it
                            cannot cancel the verdict animation on the card below it. --}}
                            <div class="edu-rise" :style="{ animationDelay: `${item.enter * 45}ms` }">
                                {{-- Mouse users can grab the card anywhere; touch users cannot,
                                because on a phone the card body is the scroll surface. --}}
                                <div class="sq-card flex items-center gap-3 rounded-2xl border-2 bg-white p-3 pr-0 shadow-sm"
                                    @pointerdown="$event.pointerType === 'mouse' && dragStart(pos, $event)"
                                    :class="{
                                        'border-emerald-500 bg-emerald-50 edu-pop edu-shine': verdictFor(pos) === 'correct',
                                        'border-red-400 bg-red-50 edu-shake edu-flash': verdictFor(pos) === 'wrong',
                                        'border-gray-200': verdictFor(pos) === null,
                                    }"
                                    {{-- One delay for the card's own animation, two more for the
                                    glow and the wash that run on its pseudo-elements, so the whole
                                    verdict cascades down the list as one wave. --}}
                                    :style="verdictShown ? {
                                        animationDelay: `${pos * 150}ms`,
                                        '--edu-shine-delay': `${pos * 150 + 100}ms`,
                                        '--edu-flash-delay': `${pos * 150}ms`,
                                    } : {}">

                                    {{-- Step badge, which becomes the verdict icon once checked. --}}
                                    <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-black shadow-sm transition-colors"
                                        :class="{
                                            'from-primary bg-gradient-to-br to-emerald-600 text-white': verdictFor(pos) === null,
                                            'bg-emerald-500 text-white': verdictFor(pos) === 'correct',
                                            'bg-red-500 text-white': verdictFor(pos) === 'wrong',
                                        }">
                                        <template x-if="verdictFor(pos) === null">
                                            <span x-text="pos + 1"></span>
                                        </template>
                                        <template x-if="verdictFor(pos) === 'correct'">
                                            <svg class="edu-badge-pop h-5 w-5" fill="none" stroke="currentColor"
                                                stroke-width="3" viewBox="0 0 24 24"
                                                :style="{ animationDelay: `${pos * 150}ms` }">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </template>
                                        <template x-if="verdictFor(pos) === 'wrong'">
                                            <svg class="edu-badge-pop h-5 w-5" fill="none" stroke="currentColor"
                                                stroke-width="3" viewBox="0 0 24 24"
                                                :style="{ animationDelay: `${pos * 150}ms` }">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </template>
                                    </span>

                                    <p class="flex-1 text-sm font-medium leading-relaxed text-gray-700 select-none"
                                        x-text="item.text"></p>

                                    {{-- On touch this is the ONLY place a drag can start, so it is
                                    sized as a full-height strip down the right edge of the card
                                    rather than a small icon button: negative margins cancel the
                                    card's own padding so the target reaches the card's real edges,
                                    and the card's overflow-hidden clips it to the rounded corner. --}}
                                    <button type="button" data-grip
                                        class="sq-grip -my-3 flex shrink-0 items-center justify-center self-stretch border-l border-gray-100 bg-gray-50/60 px-5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary/50 disabled:opacity-30"
                                        :disabled="locked"
                                        :aria-label="@js(__('Geser untuk memindahkan langkah')) + ' ' + (pos + 1)"
                                        {{-- Only pointerdown is bound here; the rest of the gesture
                                        is tracked on window. See dragStart() for why.
                                        .stop keeps it from bubbling into the card's own mouse
                                        handler and starting the same drag twice. --}}
                                        @pointerdown.stop="dragStart(pos, $event)"
                                        @keydown.arrow-up.prevent="nudge(pos, -1)"
                                        @keydown.arrow-down.prevent="nudge(pos, 1)">
                                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <circle cx="7" cy="5" r="1.8" />
                                            <circle cx="13" cy="5" r="1.8" />
                                            <circle cx="7" cy="10" r="1.8" />
                                            <circle cx="13" cy="10" r="1.8" />
                                            <circle cx="7" cy="15" r="1.8" />
                                            <circle cx="13" cy="15" r="1.8" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </li>
                    </template>
                </ol>
            </div>

            <div x-show="!done" class="edu-sticky-cta">
                <button type="button" @click="check()"
                    class="bg-primary w-full rounded-xl py-3.5 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
                    {{ __('Periksa Urutan') }}
                </button>
            </div>

            {{-- Checked: the result panel adapts to whether it was a clean sweep or not. --}}
            <template x-if="done && !timedOut">
                <div class="space-y-3">
                    @include('user.edutourism.games.partials.sequence-victory')

                    @if (!empty($cfg['explanation']))
                        <div class="edu-rise rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
                            :style="`animation-delay:${revealDelay + 140}ms`">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">
                                {{ __('Tahukah Kamu') }}</h4>
                            <p class="mt-1.5 text-sm leading-relaxed text-gray-600">{{ $cfg['explanation'] }}</p>
                        </div>
                    @endif

                    <div class="edu-sticky-cta">
                        <button type="button" @click="finish()"
                            class="bg-primary w-full rounded-xl py-3.5 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
                            {{ __('Lanjut') }}
                        </button>
                    </div>
                </div>
            </template>

            {{-- Ran out of time. --}}
            <template x-if="timedOut">
                <div class="space-y-3">
                    <div class="edu-slide-up rounded-2xl border border-red-100 bg-red-50 p-5 text-center shadow-sm">
                        <p class="text-2xl">⏱</p>
                        <p class="font-display text-charcoal mt-1 text-lg font-black">{{ __('Waktu Habis!') }}</p>
                        <p class="text-primary mt-1 text-3xl font-black tabular-nums">
                            +<span x-text="displayPoints"></span>
                            <span class="text-sm font-bold uppercase tracking-wide">{{ __('poin') }}</span>
                        </p>
                        <p class="mt-1 text-xs font-semibold text-gray-500">
                            {{ __('Kamu tetap mendapat poin partisipasi.') }}</p>
                    </div>

                    @if (!empty($cfg['explanation']))
                        <div class="edu-rise rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
                            style="animation-delay:160ms">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">
                                {{ __('Tahukah Kamu') }}</h4>
                            <p class="mt-1.5 text-sm leading-relaxed text-gray-600">{{ $cfg['explanation'] }}</p>
                        </div>
                    @endif

                    <div class="edu-sticky-cta">
                        <button type="button" @click="finish()"
                            class="bg-primary w-full rounded-xl py-3.5 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
                            {{ __('Lanjut') }}
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
