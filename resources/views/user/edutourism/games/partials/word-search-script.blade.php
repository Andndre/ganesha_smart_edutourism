{{--
    Game logic for the word-search mission, kept out of the game view on purpose.

    The game views render inside <template x-if> in active.blade.php, and script tags inside a
    template are inert until Alpine clones them — so a factory defined there only exists once its
    own mission has been mounted. active.blade.php includes this partial at the top level, one per
    distinct mission type on the point, so the factory is always defined before any board mounts.
    Do not fold it back into the view.
--}}
@once
    <script>
        function eduGameWordSearch(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                size: 0, grid: [], anchor: null, foundCells: [], foundWords: [], done: false, finished: false,

                init() {
                    const clean = w => String(w).toUpperCase().replace(/[^A-Z]/g, '');
                    const source = (Array.isArray(this.cfg.words) ? this.cfg.words : []).filter(w => clean(w) !== '');
                    // Never smaller than the longest word, or every long word would fail placement.
                    this.size = Math.max(Number(this.cfg.grid_size) || 8, ...source.map(w => clean(w).length));
                    const n = this.size;
                    const grid = Array.from({ length: n }, () => Array(n).fill(''));
                    const dirs = [[0, 1], [1, 0], [1, 1], [1, -1]];
                    const kept = [];
                    for (const original of source) {
                        const word = clean(original);
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
                        if (placed) kept.push(original);
                    }
                    // The chip list, the "all found" check and the finish CTA all read cfg.words,
                    // so dropped words have to leave it too or the mission can never be completed.
                    this.cfg.words = kept;
                    // Nothing playable (empty config, or every word failed placement): unlock the
                    // CTA right away instead of parking the player on a puzzle with no exit.
                    this.done = kept.length === 0;
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
                            window.confetti?.({ particleCount: 70, spread: 65, origin: { y: 0.7 } });
                        }
                    }
                },
                finish() {
                    // The dispatch is delayed, so a second tap inside that window would score twice.
                    if (this.finished) return;
                    this.finished = true;
                    setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned: this.maxPoints }), 400);
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
