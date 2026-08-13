{{--
    Game logic for the puzzle mission, kept out of the game view on purpose.

    The game views render inside <template x-if> in active.blade.php, and script tags inside a
    template are inert until Alpine clones them — so a factory defined there only exists once its
    own mission has been mounted. active.blade.php includes this partial at the top level, one per
    distinct mission type on the point, so the factory is always defined before any board mounts.
    Do not fold it back into the view.
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
