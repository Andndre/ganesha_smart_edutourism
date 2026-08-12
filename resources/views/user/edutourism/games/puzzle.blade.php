{{--
    Photo puzzle — slice a cultural photo into a grid and swap the pieces back into place.
    config: { prompt?, image?, grid_size?: 3|4|5, explanation? }
      image      — falls back to the point's cultural-object photo when the admin leaves it empty.
      grid_size  — set by the admin, NOT the player: difficulty is part of the challenge, so a
                   player can't pick 3x3 and still collect full points.
    time_limit_seconds (mission column, not config) optionally caps the round.
    The board adopts the photo's own aspect ratio, so nothing is cropped away and no piece
    ends up blank — a square board would discard a third of a 3:2 shot.
    Scoring: stars (pace + efficiency) scale the points, min 20%.
    Emits: mission-complete {id, earned}
--}}
@once
    <script>
        function eduGamePuzzle(cfg, missionId, maxPoints, timeLimit, imageUrl) {
            return {
                cfg, missionId, maxPoints, imageUrl,
                // Replaced with the photo's real ratio once it loads; 1/1 only until then.
                aspect: '1 / 1',
                size: [3, 4, 5].includes(cfg.grid_size) ? cfg.grid_size : 3,
                timeLimit: timeLimit || 0,
                tiles: [], selected: null, dragFrom: null,
                moves: 0, elapsed: 0, timerId: null,
                solved: false, timedOut: false, done: false,
                stars: 0, earned: 0, rootEl: null,

                init() {
                    // $el is cached here because the swap buttons get unmounted by x-if once
                    // the board is solved, which would make $dispatch fire from a detached node.
                    this.rootEl = this.$el;
                    this.tiles = this.shuffledOrder();
                    this.timerId = setInterval(() => this.tick(), 1000);

                    // Watch the board itself rather than only checking inside swap(), so the win
                    // can never be missed by a path that mutates tiles without going through it.
                    this.$watch('tiles', () => this.checkSolved());

                    this.measureAspect();
                },

                destroy() {
                    clearInterval(this.timerId);
                },

                get total() { return this.size * this.size; },

                /**
                 * The board takes the photo's ratio so every piece carries image and nothing
                 * is cropped. Columns AND rows are both pinned to equal fractions: leaving rows
                 * implicit (grid-auto-rows: auto) lets the browser size them from content, and
                 * any row that ends up even a pixel off scales its slice of the photo
                 * differently from its neighbours — a correctly ordered board then still looks
                 * misaligned.
                 */
                get gridStyle() {
                    return {
                        aspectRatio: this.aspect,
                        gridTemplateColumns: `repeat(${this.size}, minmax(0, 1fr))`,
                        gridTemplateRows: `repeat(${this.size}, minmax(0, 1fr))`,
                    };
                },

                get timeLabel() {
                    const left = this.timeLimit ? Math.max(0, this.timeLimit - this.elapsed) : this.elapsed;
                    return `${Math.floor(left / 60)}:${String(left % 60).padStart(2, '0')}`;
                },

                /** Read the photo's natural ratio so the board can match it exactly. */
                async measureAspect() {
                    try {
                        const img = new Image();
                        img.src = this.imageUrl;
                        await img.decode();

                        if (img.naturalWidth && img.naturalHeight) {
                            this.aspect = `${img.naturalWidth} / ${img.naturalHeight}`;
                        }
                    } catch {
                        // Undecodable photo — keep the square default rather than a collapsed board.
                    }
                },

                tick() {
                    if (this.done) return;
                    this.elapsed++;
                    if (this.timeLimit && this.elapsed >= this.timeLimit) this.timeUp();
                },

                /**
                 * Fisher-Yates. Every permutation is reachable because pieces are swapped
                 * (not slid), so the only outcome to reject is an already-solved board.
                 */
                shuffledOrder() {
                    const count = this.size * this.size;
                    const order = Array.from({ length: count }, (_, i) => i);
                    do {
                        for (let i = count - 1; i > 0; i--) {
                            const j = Math.floor(Math.random() * (i + 1));
                            [order[i], order[j]] = [order[j], order[i]];
                        }
                    } while (order.every((piece, slot) => piece === slot));

                    return order;
                },

                /** Slice the photo with background-size/position instead of cutting real images. */
                tileStyle(piece) {
                    const step = 100 / (this.size - 1);

                    return {
                        backgroundImage: `url('${this.imageUrl}')`,
                        backgroundSize: `${this.size * 100}% ${this.size * 100}%`,
                        backgroundPosition: `${(piece % this.size) * step}% ${Math.floor(piece / this.size) * step}%`,
                    };
                },

                isPlaced(slot) { return this.tiles[slot] === slot; },

                // ---- tap to swap ----
                tapSlot(slot) {
                    if (this.done) return;
                    navigator.vibrate?.(20);

                    if (this.selected === null || this.selected === slot) {
                        this.selected = this.selected === slot ? null : slot;
                        return;
                    }

                    this.swap(this.selected, slot);
                    this.selected = null;
                },

                // ---- drag and drop ----
                onDragStart(slot, event) {
                    if (this.done) { event.preventDefault(); return; }
                    this.dragFrom = slot;
                    this.selected = null;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', String(slot));
                },

                onDrop(slot) {
                    if (this.done || this.dragFrom === null) return;
                    this.swap(this.dragFrom, slot);
                    this.dragFrom = null;
                },

                swap(from, to) {
                    if (from === to) return;

                    const next = [...this.tiles];
                    [next[from], next[to]] = [next[to], next[from]];
                    // Counted before the assignment so the $watch above always scores the
                    // move that completed the board.
                    this.moves++;
                    this.tiles = next;
                },

                // ---- outcomes ----
                checkSolved() {
                    if (this.done) return;
                    if (!this.tiles.every((piece, slot) => piece === slot)) return;

                    this.done = true;
                    this.solved = true;
                    this.selected = null;
                    clearInterval(this.timerId);
                    this.stars = this.computeStars();
                    this.earned = this.computeEarned();
                    navigator.vibrate?.([40, 60, 120]);
                    this.celebrate();
                },

                timeUp() {
                    if (this.done) return;
                    this.done = true;
                    this.timedOut = true;
                    clearInterval(this.timerId);
                    this.stars = 0;
                    this.earned = Math.round(this.maxPoints * 0.2);
                    navigator.vibrate?.([60, 40, 60]);
                },

                /** Stars blend pace and efficiency so neither a slow-but-tidy nor a fast-but-messy run maxes out. */
                computeStars() {
                    const parMoves = Math.round(this.total * 1.3);
                    const parSeconds = this.total * 4;
                    const ratio = (this.moves / parMoves + this.elapsed / parSeconds) / 2;

                    if (ratio <= 1.15) return 3;
                    if (ratio <= 2) return 2;

                    return 1;
                },

                computeEarned() {
                    const factor = { 3: 1, 2: 0.7, 1: 0.4 }[this.stars] ?? 0.4;

                    return Math.max(Math.round(this.maxPoints * 0.2), Math.round(this.maxPoints * factor));
                },

                celebrate() {
                    if (typeof confetti !== 'function') return;
                    confetti({ particleCount: 80, spread: 70, origin: { y: 0.7 }, colors: ['#1E5128', '#D4AF37', '#FAF9F6'] });
                },

                continueMission() {
                    setTimeout(() => this.rootEl.dispatchEvent(new CustomEvent('mission-complete', {
                        bubbles: true,
                        detail: { id: this.missionId, earned: this.earned },
                    })), 400);
                },
            };
        }
    </script>
@endonce

@php
    $cfg = $mission->localizedConfig();
    // The admin may leave the image empty — fall back to the photo of whatever this route
    // point sits on, so a puzzle mission never renders with a blank board.
    $fallback = $mission->tourRoutePoint?->locationable;
    $puzzleImage =
        $cfg['image'] ??
        (!empty($fallback?->historical_images) ? asset('storage/' . $fallback->historical_images[0]) : null);
@endphp

@if (!$puzzleImage)
    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm text-amber-800">
        {{ __('Misi puzzle ini belum punya foto. Hubungi pengelola.') }}
    </div>
@else
    <div x-data="eduGamePuzzle(@js($cfg), @js($mission->id), @js($mission->points), @js($mission->time_limit_seconds), @js($puzzleImage))"
        class="space-y-4">

        @if (!empty($cfg['prompt']))
            <p class="max-w-prose text-sm leading-relaxed text-gray-600">{{ $cfg['prompt'] }}</p>
        @endif

        {{-- HUD — stops stretching once the column widens for the side-by-side board. --}}
        <div class="flex items-stretch gap-2">
            <div class="sm:min-w-36 flex flex-1 items-center gap-2 rounded-xl bg-white px-3 py-2 shadow-sm ring-1 ring-gray-100 sm:flex-none">
                <svg class="h-4 w-4 shrink-0" :class="timeLimit && timeLimit - elapsed <= 10 ? 'text-red-500' : 'text-primary'"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="min-w-0">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-gray-400"
                        x-text="timeLimit ? @js(__('Sisa Waktu')) : @js(__('Waktu'))"></p>
                    <p class="text-charcoal text-sm font-bold tabular-nums" x-text="timeLabel"></p>
                </div>
            </div>
            <div class="sm:min-w-36 flex flex-1 items-center gap-2 rounded-xl bg-white px-3 py-2 shadow-sm ring-1 ring-gray-100 sm:flex-none">
                <svg class="text-secondary h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <div class="min-w-0">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-gray-400">{{ __('Langkah') }}</p>
                    <p class="text-charcoal text-sm font-bold tabular-nums" x-text="moves"></p>
                </div>
            </div>
        </div>

        {{-- Board and reference side by side at equal width. They stack on phones instead:
        halving a max-w-md column would drop 5x5 pieces to roughly 30px, under the 44px tap target. --}}
        <div class="space-y-3 sm:flex sm:items-start sm:gap-3 sm:space-y-0">
            <div class="overflow-hidden rounded-2xl bg-white p-2 shadow-sm ring-1 ring-gray-100 sm:min-w-0 sm:flex-1 sm:basis-0">
                <div class="grid w-full gap-1" :style="gridStyle">
                    {{-- Keyed by slot, not by piece: the tiles stay put in the DOM and only their
                    background moves, so what the player sees is the tiles array by construction. --}}
                    <template x-for="(piece, slot) in tiles" :key="slot">
                        <button type="button" draggable="true"
                            @click="tapSlot(slot)"
                            @dragstart="onDragStart(slot, $event)"
                            @dragend="dragFrom = null"
                            @dragover.prevent=""
                            @drop.prevent="onDrop(slot)"
                            :style="tileStyle(piece)"
                            :class="{
                                'ring-secondary scale-95 ring-4': selected === slot,
                                'ring-primary/60 ring-2': dragFrom === slot,
                                'ring-primary/30 ring-1': selected !== slot && dragFrom !== slot && isPlaced(slot) && !done,
                                'ring-0': done,
                            }"
                            class="h-full w-full touch-manipulation rounded-md bg-gray-200 bg-no-repeat transition-transform duration-150 active:scale-95"
                            :aria-label="`${@js(__('Kepingan'))} ${piece + 1}`"></button>
                    </template>
                </div>
            </div>

            <figure class="overflow-hidden rounded-2xl bg-white p-2 shadow-sm ring-1 ring-gray-100 sm:min-w-0 sm:flex-1 sm:basis-0">
                {{-- No forced ratio: the img keeps its natural one, which is exactly the board's. --}}
                <img :src="imageUrl" alt="{{ __('Contoh') }}" class="block w-full rounded-xl">
                <figcaption class="pb-1 pt-2 text-center text-[10px] font-bold uppercase tracking-wider text-gray-400">
                    {{ __('Contoh') }}
                </figcaption>
            </figure>
        </div>

        <p x-show="!done" class="text-center text-xs text-gray-400">
            {{ __('Ketuk satu kepingan lalu ketuk kepingan lain untuk menukar posisinya.') }}
        </p>

        {{-- Result --}}
        <template x-if="done">
            <div class="space-y-3">
                <div class="rounded-2xl border p-5 text-center"
                    :class="solved ? 'border-emerald-100 bg-emerald-50' : 'border-red-100 bg-red-50'">
                    <template x-if="solved">
                        <div>
                            <div class="flex items-center justify-center gap-1.5">
                                <template x-for="star in 3" :key="star">
                                    <svg class="h-7 w-7" :class="star <= stars ? 'text-secondary' : 'text-emerald-200'"
                                        fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.98 10.1c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                    </svg>
                                </template>
                            </div>
                            <p class="mt-2 text-lg font-black text-emerald-700">{{ __('Puzzle Tersusun!') }}</p>
                            <p class="mt-1 text-sm text-emerald-600">
                                <span x-text="moves"></span> {{ __('langkah') }} · <span x-text="timeLabel"></span>
                            </p>
                        </div>
                    </template>
                    <template x-if="timedOut">
                        <div>
                            <p class="text-lg font-black text-red-700">{{ __('Waktu Habis!') }}</p>
                            <p class="mt-1 text-sm text-red-600">{{ __('Puzzle belum tersusun sempurna.') }}</p>
                        </div>
                    </template>
                    <p class="mt-3 text-xs font-bold uppercase tracking-wider"
                        :class="solved ? 'text-emerald-500' : 'text-red-500'">
                        + <span x-text="earned"></span> {{ __('poin') }}
                    </p>
                </div>

                @if (!empty($cfg['explanation']))
                    <div class="rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800">
                        <p>{{ $cfg['explanation'] }}</p>
                    </div>
                @endif

                <button type="button" @click="continueMission()"
                    class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
                    {{ __('Lanjut') }}
                </button>
            </div>
        </template>
    </div>
@endif
