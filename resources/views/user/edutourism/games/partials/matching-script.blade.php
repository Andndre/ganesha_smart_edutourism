{{--
    Game logic for the matching mission, kept out of the game view on purpose.

    The game views render inside <template x-if> in active.blade.php, and script tags inside a
    template are inert until Alpine clones them — so a factory defined there only exists once its
    own mission has been mounted. active.blade.php includes this partial at the top level, one per
    distinct mission type on the point, so the factory is always defined before any board mounts.
    Do not fold it back into the view.
--}}
@once
    @include('user.edutourism.games.partials.game-fx')

    <script>
        function eduGameMatching(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                mode: cfg.mode || 'match',
                // match mode state
                lefts: [], rights: [], selectedLeft: null, drafts: [], matchMistakes: 0,
                // pick mode state
                picked: [], wrongPicks: 0, pickDone: false, earned: 0,
                done: false,
                // presentation-only state
                sfx: window.eduSfx,
                displayPoints: 0,
                justResolved: [],
                linksDrawn: new Set(),
                resizeObserver: null,

                /**
                 * Badge/thread colours. Deliberately dark and saturated rather than pastel:
                 * these are read in direct sunlight on the walking route.
                 */
                palette: ['#1E5128', '#D4AF37', '#0F766E', '#7C3AED', '#C2410C', '#1D4ED8'],

                init() {
                    if (this.mode === 'match') {
                        this.lefts = this.cfg.pairs.map((p, i) => ({ ...p, i }));
                        this.rights = this.cfg.pairs.map((p, i) => ({ ...p, i })).sort(() => Math.random() - 0.5);

                        // Redraw whenever the pairing changes, and again whenever the board is
                        // resized — the curves are measured from live geometry, so a rotation or
                        // a font-size change would otherwise leave them pointing at stale coords.
                        this.$watch('drafts', () => this.$nextTick(() => this.drawLinks()));
                        this.$watch('done', () => this.$nextTick(() => this.drawLinks()));

                        // Deferred: x-ref elements are registered as Alpine walks the tree, so
                        // $refs.board is not guaranteed to exist yet inside init().
                        this.$nextTick(() => {
                            if (!window.ResizeObserver || !this.$refs.board) return;
                            this.resizeObserver = new ResizeObserver(() => this.drawLinks());
                            this.resizeObserver.observe(this.$refs.board);
                        });
                    }
                },

                destroy() {
                    this.resizeObserver?.disconnect();
                },

                /** Tap feedback: haptic + click cue, fired from every card press. */
                feedbackTap() {
                    navigator.vibrate?.(50);
                    this.sfx.play('tap');
                },

                // ---- match mode ----
                draftForLeft(i) { return this.drafts.find(d => d.leftI === i); },
                draftForRight(j) { return this.drafts.find(d => d.rightI === j); },
                pairResult(i) {
                    const d = this.draftForLeft(i);
                    if (!d || !this.done) return null;
                    return d.leftI === d.rightI ? 'correct' : 'wrong';
                },

                /** Stable per-pair colour so a badge and its thread always agree. */
                pairColor(leftI) {
                    const d = this.draftForLeft(leftI);
                    if (!d) return null;
                    if (this.done) return d.leftI === d.rightI ? '#059669' : '#DC2626';

                    return this.palette[leftI % this.palette.length];
                },

                /** 1-based label shown on both halves of a drafted pair. */
                pairNumber(leftI) {
                    const idx = this.drafts.findIndex(d => d.leftI === leftI);

                    return idx < 0 ? null : idx + 1;
                },

                pickLeft(i) {
                    if (this.done) return;
                    this.feedbackTap();
                    this.selectedLeft = this.selectedLeft === i ? null : i;
                },
                pickRight(j) {
                    if (this.done) return;
                    this.feedbackTap();
                    if (this.selectedLeft === null) {
                        const idx = this.drafts.findIndex(d => d.rightI === j);
                        if (idx >= 0) this.drafts.splice(idx, 1);
                        return;
                    }
                    this.drafts = this.drafts.filter(d => d.leftI !== this.selectedLeft && d.rightI !== j);
                    this.drafts.push({ leftI: this.selectedLeft, rightI: j });
                    this.selectedLeft = null;
                    navigator.vibrate?.(30);
                },
                allPairsDrafted() {
                    return this.drafts.length === this.cfg.pairs.length;
                },

                /**
                 * Curves drawn through the column gutter, one per drafted pair. Anchored to each
                 * card's vertical midpoint rather than to its grid row, so a left card made taller
                 * by a thumbnail still meets its partner cleanly.
                 */
                drawLinks() {
                    const svg = this.$refs.links;
                    const board = this.$refs.board;
                    if (!svg || !board) return;

                    svg.replaceChildren();
                    const box = board.getBoundingClientRect();
                    if (!box.width) return;

                    for (const d of this.drafts) {
                        const a = board.querySelector(`[data-left="${d.leftI}"]`);
                        const b = board.querySelector(`[data-right="${d.rightI}"]`);
                        if (!a || !b) continue;

                        const ra = a.getBoundingClientRect();
                        const rb = b.getBoundingClientRect();
                        const x1 = ra.right - box.left;
                        const y1 = ra.top + ra.height / 2 - box.top;
                        const x2 = rb.left - box.left;
                        const y2 = rb.top + rb.height / 2 - box.top;
                        const mid = (x1 + x2) / 2;

                        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        path.setAttribute('class', 'edu-link');
                        path.setAttribute('d', `M${x1} ${y1} C${mid} ${y1}, ${mid} ${y2}, ${x2} ${y2}`);
                        path.setAttribute('stroke', this.pairColor(d.leftI));
                        path.setAttribute('stroke-width', this.done ? '3' : '2.5');
                        svg.appendChild(path);

                        // Only a newly formed pair gets the draw-on animation; redraws caused by
                        // resize must not replay it, or the board would twitch on every reflow.
                        const key = `${d.leftI}-${d.rightI}`;
                        if (this.linksDrawn.has(key) || this.prefersReducedMotion()) continue;
                        this.linksDrawn.add(key);
                        const len = path.getTotalLength();
                        path.animate(
                            [{ strokeDasharray: len, strokeDashoffset: len }, { strokeDasharray: len, strokeDashoffset: 0 }],
                            { duration: 420, easing: 'cubic-bezier(.4,.2,.2,1)' },
                        );
                    }
                },

                prefersReducedMotion() {
                    return window.eduReducedMotion();
                },

                submitMatch() {
                    if (this.done || !this.allPairsDrafted()) return;
                    Swal.fire({
                        title: @js(__('Apakah anda yakin?')),
                        text: @js(__('Pasangan yang sudah dipilih akan diperiksa.')),
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#1E5128',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: @js(__('Periksa Jawaban')),
                        cancelButtonText: @js(__('Batal')),
                    }).then(r => {
                        if (!r.isConfirmed) return;
                        this.matchMistakes = this.drafts.filter(d => d.leftI !== d.rightI).length;
                        const penalty = this.cfg.penalty ?? 10;
                        this.earned = Math.max(Math.round(this.maxPoints * 0.2), this.maxPoints - penalty * this.matchMistakes);
                        this.revealMatch();
                        this.complete(this.earned);
                    });
                },

                /**
                 * Stagger the verdicts instead of flipping every card at once: one pair lands
                 * every 160ms, each with its own cue, so the player can actually follow which
                 * answer was which.
                 */
                revealMatch() {
                    this.cfg.pairs.forEach((_, i) => {
                        setTimeout(() => {
                            this.justResolved.push(i);
                            const ok = this.drafts.find(d => d.leftI === i)?.rightI === i;
                            this.sfx.play(ok ? 'match' : 'wrong');
                            navigator.vibrate?.(ok ? 35 : [45, 40, 45]);
                        }, 160 * i);
                    });
                },

                resolved(i) { return this.justResolved.includes(i); },

                // ---- pick mode ----
                togglePick(idx) {
                    if (this.pickDone || this.done) return;
                    this.feedbackTap();
                    const pos = this.picked.indexOf(idx);
                    if (pos >= 0) this.picked.splice(pos, 1);
                    else if (this.picked.length < (this.cfg.pick_count || this.cfg.items.filter(t => t.correct).length)) this.picked.push(idx);
                },
                submitPick() {
                    if (this.pickDone || this.picked.length === 0) return;
                    Swal.fire({
                        title: @js(__('Apakah anda yakin?')),
                        text: @js(__('Pilihan yang sudah dipilih akan diperiksa.')),
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#1E5128',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: @js(__('Periksa Pilihan')),
                        cancelButtonText: @js(__('Batal')),
                    }).then(r => {
                        if (!r.isConfirmed) return;
                        this.pickDone = true;
                        const correct = this.picked.filter(i => this.cfg.items[i].correct).length;
                        this.wrongPicks = this.picked.length - correct;
                        const target = this.cfg.pick_count || this.cfg.items.filter(t => t.correct).length;
                        const perItem = this.maxPoints / target;
                        const penaltyRatio = this.cfg.penalty_ratio ?? 0.5;
                        this.earned = Math.max(0, Math.round(correct * perItem - this.wrongPicks * perItem * penaltyRatio));

                        this.revealPick();
                        this.celebrate(this.earned);
                    });
                },

                /** Same staggered reveal as match mode, walking the grid in display order. */
                revealPick() {
                    this.cfg.items.forEach((item, i) => {
                        if (!this.picked.includes(i)) return;
                        setTimeout(() => {
                            this.justResolved.push(i);
                            this.sfx.play(item.correct ? 'match' : 'wrong');
                            navigator.vibrate?.(item.correct ? 35 : [45, 40, 45]);
                        }, 140 * this.picked.indexOf(i));
                    });
                },

                pickState(idx) {
                    if (!this.pickDone) return this.picked.includes(idx) ? 'selected' : 'idle';
                    if (this.picked.includes(idx)) return this.cfg.items[idx].correct ? 'correct' : 'wrong';
                    return this.cfg.items[idx].correct ? 'missed' : 'idle';
                },
                pickResultClass(idx) {
                    const state = this.pickState(idx);
                    if (state === 'correct') return 'border-emerald-500 bg-emerald-50 text-emerald-800';
                    if (state === 'wrong') return 'border-red-400 bg-red-50 text-red-700';
                    if (state === 'missed') return 'border-amber-300 bg-amber-50 text-amber-700';
                    return 'border-gray-200 bg-white text-gray-500 opacity-70';
                },
                finish() {
                    setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned: this.earned }), 400);
                },

                complete(earned) {
                    if (this.done) return;
                    this.done = true;
                    this.earned = earned;
                    this.celebrate(earned);
                },

                /**
                 * How long the staggered verdicts take to finish. The victory panel and the
                 * count-up both wait this long so the celebration lands after the last card has
                 * shown its answer, rather than talking over it.
                 */
                get revealDelay() {
                    const isMatch = this.mode === 'match';
                    const count = isMatch ? (this.cfg.pairs?.length ?? 0) : this.picked.length;

                    return (isMatch ? 160 : 140) * Math.max(0, count - 1) + 260;
                },

                /**
                 * Victory: confetti, chime and a score that counts up. The count-up is delayed
                 * past the staggered reveal so the number lands after the last verdict, not over it.
                 */
                celebrate(earned) {
                    const delay = this.revealDelay;

                    setTimeout(() => {
                        if (typeof confetti === 'function') {
                            confetti({
                                particleCount: 90,
                                spread: 72,
                                origin: { y: 0.7 },
                                colors: ['#1E5128', '#D4AF37', '#FAF9F6'],
                            });
                        }
                        this.sfx.play('win');
                        navigator.vibrate?.([40, 60, 120]);
                        this.countUp(earned);
                    }, delay);
                },

                countUp(target) {
                    if (this.prefersReducedMotion() || target <= 0) {
                        this.displayPoints = target;
                        return;
                    }
                    const started = performance.now();
                    const tick = now => {
                        // easeOutCubic: fast at first, settling gently onto the final number.
                        const t = Math.min(1, (now - started) / 900);
                        this.displayPoints = Math.round(target * (1 - Math.pow(1 - t, 3)));
                        if (t < 1) requestAnimationFrame(tick);
                    };
                    requestAnimationFrame(tick);
                },

                playAudio(path) {
                    new Audio('/audio-stream/' + encodeURI(path)).play().catch(() => {});
                },
            };
        }
    </script>
@endonce
