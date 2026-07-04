{{--
    Word search. Grid generated client-side (H/V/diagonal placement + random fill).
    config: { prompt?, words: ["BAMBU", ...], grid_size?: int }
    Selection is tap-based (outdoor/mobile friendly): tap the first letter, then the last letter
    of a word — the straight line between them is checked (both directions).
    Scoring: full mission points when all words are found (no fail state).
    Emits: mission-complete {id, earned}
--}}
@once
    <script>
        function eduGameWordSearch(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                size: 0, grid: [], anchor: null, foundCells: [], foundWords: [], done: false,

                init() {
                    const words = this.cfg.words.map(w => w.toUpperCase().replace(/[^A-Z]/g, ''));
                    this.size = this.cfg.grid_size || Math.max(8, ...words.map(w => w.length));
                    const n = this.size;
                    const grid = Array.from({ length: n }, () => Array(n).fill(''));
                    const dirs = [[0, 1], [1, 0], [1, 1], [1, -1]];
                    for (const word of words) {
                        let placed = false;
                        for (let tries = 0; tries < 200 && !placed; tries++) {
                            const [dr, dc] = dirs[Math.floor(Math.random() * dirs.length)];
                            const r0 = Math.floor(Math.random() * n);
                            const c0 = Math.floor(Math.random() * n);
                            const rEnd = r0 + dr * (word.length - 1), cEnd = c0 + dc * (word.length - 1);
                            if (rEnd < 0 || rEnd >= n || cEnd < 0 || cEnd >= n) continue;
                            let ok = true;
                            for (let k = 0; k < word.length; k++) {
                                const cell = grid[r0 + dr * k][c0 + dc * k];
                                if (cell !== '' && cell !== word[k]) { ok = false; break; }
                            }
                            if (!ok) continue;
                            for (let k = 0; k < word.length; k++) grid[r0 + dr * k][c0 + dc * k] = word[k];
                            placed = true;
                        }
                        // ponytail: if random placement fails after 200 tries, drop the word from the puzzle
                        if (!placed) this.cfg.words = this.cfg.words.filter(w => w.toUpperCase().replace(/[^A-Z]/g, '') !== word);
                    }
                    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    for (let r = 0; r < n; r++)
                        for (let c = 0; c < n; c++)
                            if (grid[r][c] === '') grid[r][c] = alphabet[Math.floor(Math.random() * 26)];
                    this.grid = grid;
                },
                tap(r, c) {
                    if (this.done) return;
                    navigator.vibrate?.(50);
                    if (!this.anchor) { this.anchor = [r, c]; return; }
                    const [r0, c0] = this.anchor;
                    this.anchor = null;
                    const dr = Math.sign(r - r0), dc = Math.sign(c - c0);
                    const len = Math.max(Math.abs(r - r0), Math.abs(c - c0)) + 1;
                    const straight = (r0 === r) || (c0 === c) || (Math.abs(r - r0) === Math.abs(c - c0));
                    if (!straight) return;
                    let text = '', cells = [];
                    for (let k = 0; k < len; k++) {
                        const rr = r0 + dr * k, cc = c0 + dc * k;
                        text += this.grid[rr][cc];
                        cells.push(rr + '-' + cc);
                    }
                    const reversed = text.split('').reverse().join('');
                    const hit = this.cfg.words.find(w => {
                        const clean = w.toUpperCase().replace(/[^A-Z]/g, '');
                        return (clean === text || clean === reversed) && !this.foundWords.includes(w);
                    });
                    if (hit) {
                        this.foundWords.push(hit);
                        this.foundCells.push(...cells);
                        navigator.vibrate?.([50, 30, 50]);
                        if (this.foundWords.length === this.cfg.words.length) {
                            this.done = true;
                            confetti?.({ particleCount: 70, spread: 65, origin: { y: 0.7 } });
                            setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned: this.maxPoints }), 900);
                        }
                    }
                },
                cellClass(r, c) {
                    if (this.foundCells.includes(r + '-' + c)) return 'bg-primary text-white';
                    if (this.anchor && this.anchor[0] === r && this.anchor[1] === c) return 'bg-amber-300 text-charcoal';
                    return 'bg-white text-gray-700';
                },
            };
        }
    </script>
@endonce

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameWordSearch(@js($cfg), @js($mission->id), @js($mission->points))" class="space-y-4">
    @if (!empty($cfg['prompt']))
        <p class="text-sm leading-relaxed text-gray-600">{{ $cfg['prompt'] }}</p>
    @endif

    <div class="flex flex-wrap gap-2">
        <template x-for="w in cfg.words" :key="w">
            <span class="rounded-lg border px-2 py-1 text-xs font-bold uppercase tracking-wide"
                :class="foundWords.includes(w) ? 'border-emerald-200 bg-emerald-50 text-emerald-600 line-through' :
                    'border-gray-200 bg-gray-50 text-gray-500'"
                x-text="w"></span>
        </template>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-gray-50 p-2">
        <div class="grid gap-1" :style="`grid-template-columns: repeat(${size}, minmax(0, 1fr));`">
            <template x-for="(row, r) in grid" :key="r">
                <template x-for="(letter, c) in row" :key="r + '-' + c">
                    <button type="button" @click="tap(r, c)"
                        class="flex aspect-square min-w-[32px] items-center justify-center rounded-md text-sm font-bold shadow-sm transition"
                        :class="cellClass(r, c)" x-text="letter"></button>
                </template>
            </template>
        </div>
    </div>
    <p class="text-center text-xs text-gray-400">{{ __('Ketuk huruf pertama lalu huruf terakhir sebuah kata.') }}</p>
</div>
