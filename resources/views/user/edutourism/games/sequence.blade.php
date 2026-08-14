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

    {{-- The brief, read as a page from a lontar: gold hairline down the left edge, the same accent
    that closes the timeline rail and frames the result panel. --}}
    @if (!empty($cfg['prompt']))
        <div class="relative overflow-hidden rounded-2xl border border-amber-200/70 bg-gradient-to-br from-white to-amber-50/60 p-4 pl-5 shadow-sm">
            <span class="from-secondary absolute inset-y-0 left-0 w-1 bg-gradient-to-b to-emerald-600"
                aria-hidden="true"></span>
            <p class="text-sm leading-relaxed text-gray-600">{{ $cfg['prompt'] }}</p>
        </div>
    @endif

    {{-- Phase 1 (optional): flip the face-down cards to find the hidden facts. --}}
    <template x-if="phase === 'reveal'">
        <div class="space-y-3">
            {{-- Progress reads as a filling meter, not just a fraction — the bar is the reason to
            keep tapping the next face-down card. --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <span class="sq-chip">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                        </svg>
                        {{ __('Fakta Ditemukan') }}
                    </span>
                    <span class="text-primary text-xs font-black tabular-nums">
                        <span x-text="revealed.length"></span>/<span x-text="items.length"></span>
                    </span>
                </div>
                {{-- scaleX, not width: the whole sheet keeps its animations on the compositor. --}}
                <div class="sq-meter mt-2.5">
                    <span class="sq-meter-fill"
                        :style="{ transform: `scaleX(${items.length ? revealed.length / items.length : 0})` }"></span>
                </div>
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
                                    class="sq-face sq-face-front sq-sheen sq-weave from-primary flex min-h-[92px] items-center gap-4 border-2 border-dashed border-amber-300/70 bg-gradient-to-br to-emerald-800 p-4 shadow-md"
                                    :style="{ '--sq-sheen-delay': `${idx * 700}ms` }">
                                    <span
                                        class="sq-qpulse relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-amber-300/40 bg-white/15 text-lg font-black text-amber-300">?</span>
                                    <span class="relative flex-1">
                                        <span class="sq-label block text-amber-300/90">
                                            {{ __('Fakta') }} #<span x-text="idx + 1"></span>
                                        </span>
                                        <span class="mt-0.5 block text-sm font-bold text-white/95">
                                            {{ __('Ketuk untuk mengungkap fakta') }}
                                        </span>
                                    </span>
                                    {{-- Tap affordance on the trailing edge, so the card reads as
                                    interactive even before the sheen sweeps across it. --}}
                                    <svg class="relative h-5 w-5 shrink-0 text-amber-300/70" fill="none"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.5 12a3.5 3.5 0 10-7 0m10.6 5.6a9 9 0 10-14.2 0M12 12v9" />
                                    </svg>
                                </span>

                                {{-- Face up: sits in the flow, so the card is as tall as its text. --}}
                                <span
                                    class="sq-face sq-face-back flex min-h-[92px] items-center gap-4 border-2 border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm">
                                    <span
                                        class="sq-node sq-node-ok relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white">
                                        <svg class="relative h-5 w-5" fill="none" stroke="currentColor" stroke-width="3"
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
                    class="from-primary shadow-primary/25 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r to-emerald-700 py-3.5 text-sm font-bold text-white shadow-lg transition-transform active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none"
                    :class="allRevealed && 'edu-pulse-glow'">
                    {{ __('Susun Kronologi') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 7l5 5-5 5" />
                    </svg>
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
                <div class="sticky top-0 z-30 -mx-1 flex justify-center bg-[#FAF9F6] py-1.5">
                    <div class="flex items-center gap-2.5 rounded-full border bg-gradient-to-r from-white to-gray-50 py-1.5 pl-1.5 pr-4 shadow-md transition-colors"
                        :class="timeCritical ? 'border-red-200 sq-throb' : 'border-gray-200'">
                        <span class="relative flex h-9 w-9 items-center justify-center">
                            <svg class="h-9 w-9 -rotate-90" viewBox="0 0 40 40" aria-hidden="true">
                                <circle cx="20" cy="20" r="16" fill="none" stroke="currentColor" stroke-width="4"
                                    class="text-gray-100" />
                                <circle cx="20" cy="20" r="16" fill="none" stroke="currentColor" stroke-width="4"
                                    stroke-linecap="round" stroke-dasharray="100.5"
                                    class="sq-timer-ring"
                                    :class="timeCritical ? 'text-red-500' : 'text-primary'"
                                    :style="{ strokeDashoffset: 100.5 * (1 - timeRatio) }" />
                            </svg>
                            {{-- Hourglass sits inside the ring, so the badge reads as a clock at a
                            glance instead of an unlabelled arc. --}}
                            <svg class="absolute h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24" aria-hidden="true"
                                :class="timeCritical ? 'text-red-400' : 'text-gray-300'">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 2h12M6 22h12M8 2v4.5L12 11l4-4.5V2M8 22v-4.5L12 13l4 4.5V22" />
                            </svg>
                        </span>
                        <span class="leading-tight">
                            <span class="sq-label block"
                                :class="timeCritical ? 'text-red-400' : 'text-gray-400'">{{ __('Sisa Waktu') }}</span>
                            <span class="block text-base font-black tabular-nums"
                                :class="timeCritical ? 'animate-pulse text-red-500' : 'text-charcoal'"
                                x-text="timeLabel"></span>
                        </span>
                    </div>
                </div>
            </template>

            {{-- The check is one-shot, so say so before the player commits rather than only in
            the confirm dialog. --}}
            <div x-show="!done" class="flex items-center justify-between gap-2">
                <span class="sq-chip sq-chip-plain">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 11V7a4 4 0 118 0v4M6 11h12v9H6z" />
                    </svg>
                    {{ __('Sekali Periksa') }}
                </span>
                <span class="sq-chip sq-chip-gold">
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2l2.9 6.3 6.6.8-4.9 4.5 1.3 6.6L12 17l-5.9 3.2 1.3-6.6L2.5 9.1l6.6-.8z" />
                    </svg>
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
                                {{-- Every state is a gradient rather than a flat fill: mixing the
                                two would leave `bg-white` showing through the verdict tint, since
                                a gradient is a background-image and paints over the colour. --}}
                                <div class="sq-card flex items-center gap-3 rounded-2xl border-2 p-3 pr-0 shadow-sm"
                                    @pointerdown="$event.pointerType === 'mouse' && dragStart(pos, $event)"
                                    :class="{
                                        'border-emerald-500 bg-gradient-to-br from-emerald-50 to-emerald-100/60 edu-pop edu-shine': verdictFor(pos) === 'correct',
                                        'border-red-400 bg-gradient-to-br from-red-50 to-red-100/50 edu-shake edu-flash': verdictFor(pos) === 'wrong',
                                        'border-gray-200 bg-gradient-to-br from-white to-gray-50/70': verdictFor(pos) === null,
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
                                    <span class="sq-node relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-black transition-colors"
                                        :class="{
                                            'from-primary bg-gradient-to-br to-emerald-600 text-white': verdictFor(pos) === null,
                                            'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white sq-node-ok': verdictFor(pos) === 'correct',
                                            'bg-gradient-to-br from-red-400 to-red-600 text-white sq-node-bad': verdictFor(pos) === 'wrong',
                                        }">
                                        {{-- `relative` so the digit paints above the node's gloss
                                        highlight, which is an absolutely-positioned ::after. --}}
                                        <template x-if="verdictFor(pos) === null">
                                            <span class="relative" x-text="pos + 1"></span>
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
                                        class="sq-grip focus-visible:ring-primary/50 hover:from-primary/10 hover:to-primary/5 hover:text-primary -my-3 flex shrink-0 items-center justify-center self-stretch border-l border-gray-200/70 bg-gradient-to-b from-gray-50 to-gray-100/70 px-5 text-gray-400 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-inset disabled:opacity-30"
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
                    class="from-primary shadow-primary/25 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r to-emerald-700 py-3.5 text-sm font-bold text-white shadow-lg transition-transform active:scale-95">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M4 6h16M4 18h16" />
                    </svg>
                    {{ __('Periksa Urutan') }}
                </button>
            </div>

            {{-- Checked: the result panel adapts to whether it was a clean sweep or not. --}}
            <template x-if="done && !timedOut">
                <div class="space-y-3">
                    @include('user.edutourism.games.partials.sequence-victory')

                    @if (!empty($cfg['explanation']))
                        @include('user.edutourism.games.partials.explanation-card', ['text' => $cfg['explanation'], 'delay' => 'revealDelay + 140'])
                    @endif

                    <div class="edu-sticky-cta">
                        <button type="button" @click="finish()" :disabled="finished"
                            class="from-primary shadow-primary/25 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r to-emerald-700 py-3.5 text-sm font-bold text-white shadow-lg transition-transform active:scale-95 disabled:opacity-60">
                            {{ __('Lanjut') }}
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>

            {{-- Ran out of time. --}}
            <template x-if="timedOut">
                <div class="space-y-3">
                    <div class="edu-slide-up relative overflow-hidden rounded-2xl border border-red-100 bg-gradient-to-br from-white to-red-50 p-5 text-center shadow-md">
                        <div class="pointer-events-none absolute -top-10 left-1/2 h-24 w-24 -translate-x-1/2 rounded-full bg-red-300/25 blur-2xl"
                            aria-hidden="true"></div>

                        {{-- Same reason as the victory panel: the count-up would otherwise be
                        announced once per frame. --}}
                        <p class="sr-only" role="status" aria-live="polite"
                            x-text="`{{ __('Waktu Habis!') }} +${earned} {{ __('poin') }}`"></p>

                        <div class="relative">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-red-400 to-red-600 text-white shadow-lg shadow-red-500/25">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6 2h12M6 22h12M8 2v4.5L12 11l4-4.5V2M8 22v-4.5L12 13l4 4.5V22" />
                                </svg>
                            </span>
                            <p class="font-display text-charcoal mt-2.5 text-xl font-black">{{ __('Waktu Habis!') }}</p>
                            <p class="text-primary mt-1.5 text-4xl font-black tabular-nums">
                                +<span x-text="displayPoints"></span>
                                <span class="text-base font-bold uppercase tracking-wide">{{ __('poin') }}</span>
                            </p>
                            <p class="mt-1.5 text-xs font-semibold text-gray-500">
                                {{ __('Kamu tetap mendapat poin partisipasi.') }}</p>
                        </div>
                    </div>

                    @if (!empty($cfg['explanation']))
                        @include('user.edutourism.games.partials.explanation-card', ['text' => $cfg['explanation'], 'delay' => '160'])
                    @endif

                    <div class="edu-sticky-cta">
                        <button type="button" @click="finish()" :disabled="finished"
                            class="from-primary shadow-primary/25 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r to-emerald-700 py-3.5 text-sm font-bold text-white shadow-lg transition-transform active:scale-95 disabled:opacity-60">
                            {{ __('Lanjut') }}
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
