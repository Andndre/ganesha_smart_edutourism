{{--
    Game logic for the sequence mission, kept out of the game view on purpose.

    The game views render inside <template x-if> in active.blade.php, and script tags inside a
    template are inert until Alpine clones them — so a factory defined there only exists once its
    own mission has been mounted. active.blade.php includes this partial at the top level, one per
    distinct mission type on the point, so the factory is always defined before any board mounts.
    Do not fold it back into the view.
--}}
@once
    @include('user.edutourism.games.partials.game-fx')

    <style>
        /* ---- small type & chips ---------------------------------------------------------
         *
         * Every rule in this sheet has to respect one Tailwind v4 rule: utilities are emitted
         * inside `@layer utilities`, and unlayered CSS outranks any layer regardless of
         * specificity. So a class here must never declare a property that a utility on the same
         * element also owns — colour and spacing stay in the markup, structure stays here.
         */
        .sq-label {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .sq-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            border-radius: 9999px;
            padding: 0.3rem 0.65rem;
            font-size: 10px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            background: rgba(30, 81, 40, .08);
            color: rgba(30, 81, 40, .9);
        }

        .sq-chip-plain {
            background: rgba(25, 26, 25, .05);
            color: #6b7280;
        }

        .sq-chip-gold {
            background: rgba(212, 175, 55, .18);
            color: #8a6a10;
        }

        /* ---- reveal progress meter ------------------------------------------------------ */

        .sq-meter {
            overflow: hidden;
            height: 6px;
            border-radius: 9999px;
            background: rgba(30, 81, 40, .1);
        }

        /* Grows by scaleX rather than width, so the fill stays on the compositor like every
           other animation in these games. Alpine writes the transform inline. */
        .sq-meter-fill {
            display: block;
            width: 100%;
            height: 100%;
            border-radius: inherit;
            transform-origin: left center;
            background: linear-gradient(90deg, #1E5128, #2f8f52 55%, #D4AF37);
            transition: transform .45s cubic-bezier(.34, 1.4, .64, 1);
        }

        /* ---- timeline rail ------------------------------------------------------------- */

        /**
         * One line behind the whole stack rather than a segment per card: the cards are opaque, so
         * it only shows through the gaps, which reads as connectors between the badges — and it
         * stays perfectly still when a card is lifted out or tilted.
         * 29px = card padding (12) + half the badge (18) - half the rail (1).
         */
        .sq-rail {
            position: absolute;
            left: 29px;
            top: 1.25rem;
            bottom: 1.25rem;
            width: 2px;
            border-radius: 2px;
            background: linear-gradient(to bottom, rgba(30, 81, 40, .35), rgba(30, 81, 40, .18) 70%, rgba(212, 175, 55, .35));
            transition: background .4s ease;
        }

        /* Solved: the rail turns emerald and light travels down it, so the eye is walked from the
           first step to the last one it just got right. */
        @keyframes sq-rail-flow {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 0 -200%;
            }
        }

        .sq-rail-done {
            background: linear-gradient(to bottom, rgb(16 185 129), rgba(16, 185, 129, .35) 50%, rgb(16 185 129));
            background-size: 100% 200%;
            animation: sq-rail-flow 2.6s linear infinite;
        }

        .sq-rail-done::before,
        .sq-rail-done::after {
            color: rgb(16 185 129);
        }

        .sq-rail::before,
        .sq-rail::after {
            content: '';
            position: absolute;
            left: 50%;
            width: 8px;
            height: 8px;
            margin-left: -4px;
            border-radius: 9999px;
            background: currentColor;
            color: rgba(30, 81, 40, .35);
        }

        .sq-rail::before {
            top: -4px;
        }

        .sq-rail::after {
            bottom: -4px;
            color: rgba(212, 175, 55, .55);
        }

        /* ---- rows & dragging ----------------------------------------------------------- */

        .sq-row {
            position: relative;
            z-index: 1;
        }

        .sq-row:not(:last-child) {
            margin-bottom: 0.625rem;
        }

        /**
         * The drag offset arrives as a custom property so the transform itself can live here in
         * CSS instead of being rebuilt as an inline string on every pointermove. `will-change` is
         * scoped to this class — it buys a compositor layer for the duration of the grab and gives
         * it straight back, which matters on the low-end phones this runs on.
         */
        .sq-row.is-dragging {
            z-index: 30;
            transform: translate3d(0, var(--sq-dy, 0px), 0) scale(1.03) rotate(1deg);
            will-change: transform;
            cursor: grabbing;
        }

        .sq-row.is-dragging .sq-card {
            border-color: #1E5128;
            box-shadow: 0 18px 35px -10px rgba(25, 26, 25, .38), 0 0 0 4px rgba(30, 81, 40, .16);
        }

        /* Settle after the finger lifts: the transform drops to none and this eases it home. */
        .sq-settle {
            transition: transform .22s cubic-bezier(.34, 1.56, .64, 1);
        }

        .sq-card {
            position: relative;
            overflow: hidden;
            /* A mouse drag would otherwise start selecting the step text instead. */
            user-select: none;
            -webkit-user-select: none;
            transition: border-color .18s ease, background-color .18s ease, box-shadow .18s ease,
                transform .18s cubic-bezier(.34, 1.4, .64, 1);
        }

        /* Grabbing the whole card is a pointer-only affordance — see the card's pointerdown. */
        @media (hover: hover) {
            .sq-card {
                cursor: grab;
            }

            .sq-row.is-dragging .sq-card {
                cursor: grabbing;
            }

            /**
             * Hover lift, pointer devices only. Checked cards stay put on their own: their verdict
             * animation runs with `both`, and an animation's retained value beats a transition.
             */
            .sq-row:not(.is-dragging) .sq-card:hover {
                transform: translate3d(0, -2px, 0);
                box-shadow: 0 12px 22px -14px rgba(25, 26, 25, .5);
            }
        }

        /* ---- step node ------------------------------------------------------------------
         *
         * The badge is a bead on the timeline rail, so it gets a coloured glow to sit proud of the
         * card, plus a top highlight that reads as a lit sphere. Box-shadow only — the size, fill
         * and text colour all stay with the utilities in the markup.
         */
        .sq-node {
            box-shadow: 0 3px 10px -2px rgba(30, 81, 40, .5);
        }

        .sq-node-ok {
            box-shadow: 0 3px 12px -2px rgba(16, 185, 129, .65);
        }

        .sq-node-bad {
            box-shadow: 0 3px 12px -2px rgba(239, 68, 68, .55);
        }

        .sq-node::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            background: linear-gradient(to bottom, rgba(255, 255, 255, .32), rgba(255, 255, 255, 0) 55%);
        }

        .sq-grip {
            touch-action: none;
            cursor: grab;
            -webkit-tap-highlight-color: transparent;
        }

        .sq-grip:active {
            cursor: grabbing;
        }

        .sq-grip:disabled {
            cursor: default;
        }

        /* ---- reveal phase: 3D flip ------------------------------------------------------ */

        .sq-flip {
            display: block;
            width: 100%;
            perspective: 900px;
            -webkit-tap-highlight-color: transparent;
        }

        .sq-flip-inner {
            position: relative;
            display: block;
            transform-style: preserve-3d;
            transition: transform .55s cubic-bezier(.34, 1.2, .5, 1);
        }

        .sq-flip.is-flipped .sq-flip-inner {
            transform: rotateY(180deg);
        }

        /**
         * Deliberately no `display` here, and the same caution applies to every rule in this
         * sheet: Tailwind v4 emits its utilities inside `@layer utilities`, and unlayered CSS
         * outranks any layer no matter its specificity or source order. So a bare
         * `display: block` on .sq-face silently beats the `flex` utility on the same element and
         * collapses the badge and the text into a vertical stack. Anything a utility already
         * owns should be left to the utility.
         */
        .sq-face {
            border-radius: 1rem;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        /**
         * The mystery face is the absolute one and the fact face sits in the flow, so each card
         * takes the height of its own text and nothing gets clipped. The trade-off is that card
         * height weakly hints at how long the hidden fact is — better than truncating it.
         */
        .sq-face-front {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }

        .sq-face-back {
            transform: rotateY(180deg);
        }

        @keyframes sq-sheen {

            0%,
            72% {
                transform: translate3d(-130%, 0, 0) skewX(-18deg);
            }

            100% {
                transform: translate3d(240%, 0, 0) skewX(-18deg);
            }
        }

        /* The delay is a custom property because the animation lives on the pseudo-element: an
           inline animation-delay on the card itself would never reach it. */
        .sq-sheen::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(100deg, transparent 38%, rgba(255, 255, 255, .38) 50%, transparent 62%);
            animation: sq-sheen 3.6s ease-in-out infinite;
            animation-delay: var(--sq-sheen-delay, 0ms);
        }

        /**
         * Woven texture on the face-down card — a nod to the bedeg matting on a Penglipuran
         * compound wall, drawn as two crossed stripe gradients so it costs no image request.
         * Lives on ::before because .sq-sheen already owns ::after on this same element.
         */
        .sq-weave::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            opacity: .55;
            background-image:
                repeating-linear-gradient(45deg, rgba(255, 255, 255, .07) 0 6px, transparent 6px 12px),
                repeating-linear-gradient(-45deg, rgba(255, 255, 255, .05) 0 6px, transparent 6px 12px);
        }

        /* Gold halo breathing out of the "?" badge: the one thing on a face-down card that says
           it is waiting to be tapped. */
        @keyframes sq-qpulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(212, 175, 55, .5);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(212, 175, 55, 0);
            }
        }

        .sq-qpulse {
            animation: sq-qpulse 2.2s ease-out infinite;
        }

        /* Once flipped, the halo would keep pulsing behind the fact face. */
        .sq-flip.is-flipped .sq-qpulse {
            animation: none;
        }

        /* ---- countdown ------------------------------------------------------------------ */

        .sq-timer-ring {
            transition: stroke-dashoffset 1s linear, stroke .3s ease;
        }

        @keyframes sq-throb {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .sq-throb {
            animation: sq-throb 1s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {

            .sq-flip-inner,
            .sq-settle,
            .sq-card,
            .sq-rail,
            .sq-meter-fill,
            .sq-timer-ring {
                transition: none !important;
            }

            .sq-sheen::after,
            .sq-qpulse,
            .sq-rail-done,
            .sq-throb {
                animation: none !important;
            }

            /* The hover lift is motion too, and on a hybrid device it still fires. */
            .sq-row:not(.is-dragging) .sq-card:hover {
                transform: none;
            }

            /* The lift still needs to read as "this one is in your hand" — minus the theatrics. */
            .sq-row.is-dragging {
                transform: translate3d(0, var(--sq-dy, 0px), 0);
            }
        }
    </style>

    <script>
        function eduGameSequence(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                phase: cfg.reveal_first ? 'reveal' : 'order',
                revealed: [],
                items: [],
                wrongIdx: [],
                verdictShown: false,
                done: false,
                finished: false,
                timedOut: false,
                timeLeft: null,
                timerInterval: null,
                warned: false,
                earned: 0,
                displayPoints: 0,
                sfx: window.eduSfx,

                // Drag state. `slots` is the measured geometry of every row, refreshed on grab and
                // after each reorder.
                dragIdx: null,
                dragY: 0,
                dragStartY: 0,
                dragPointer: null,
                slots: [],
                syncing: false,
                onWindowMove: null,
                onWindowUp: null,
                dragMoved: false,

                init() {
                    // `i` is the item's correct position; `enter` is its shuffled position, kept
                    // separately so the staggered entrance delay stays constant while `i`-keyed
                    // rows move around.
                    this.items = this.cfg.items.map((it, i) => ({ ...it, i }));
                    do {
                        window.eduShuffle(this.items);
                    } while (this.items.length > 1 && this.items.every((it, pos) => it.i === pos));
                    this.items.forEach((it, pos) => { it.enter = pos; });

                    if (this.phase === 'order') this.startTimer();
                },

                destroy() {
                    this.stopTimer();
                    // The drag listeners live on window, so closing the runner mid-drag would
                    // otherwise leave them behind pointing at a dead component.
                    this.releasePointer();
                },

                // ---- countdown ----------------------------------------------------------
                startTimer() {
                    if (!this.cfg.time_limit_seconds || this.timerInterval) return;
                    this.timeLeft = this.cfg.time_limit_seconds;
                    this.timerInterval = setInterval(() => {
                        this.timeLeft--;

                        // One nudge as the clock turns red, not one per second — a tick every
                        // second for the last half-minute is nagging, not urgency.
                        if (this.timeLeft === 30 && !this.warned) {
                            this.warned = true;
                            navigator.vibrate?.(80);
                        }
                        if (this.timeLeft <= 0) {
                            clearInterval(this.timerInterval);
                            this.timerInterval = null;
                            if (!this.done) this.timeUp();
                        }
                    }, 1000);
                },
                stopTimer() {
                    if (this.timerInterval) {
                        clearInterval(this.timerInterval);
                        this.timerInterval = null;
                    }
                },
                timeUp() {
                    if (this.done) return;
                    this.done = true;
                    this.timedOut = true;
                    this.dragIdx = null;
                    // Clearing dragIdx makes dragMove a no-op, but dragEnd then bails before it can
                    // unbind — so the window listeners have to be dropped here or they outlive the game.
                    this.releasePointer();
                    navigator.vibrate?.([60, 40, 60]);
                    this.sfx.play('wrong');
                    this.earned = Math.round(this.maxPoints * 0.2);
                    this.displayPoints = this.earned;
                },
                get timeLabel() {
                    const left = Math.max(0, this.timeLeft ?? 0);

                    return `${Math.floor(left / 60)}:${String(left % 60).padStart(2, '0')}`;
                },
                /** Fraction of the countdown still left, for the progress ring. */
                get timeRatio() {
                    if (!this.cfg.time_limit_seconds) return 0;

                    return Math.max(0, Math.min(1, (this.timeLeft ?? 0) / this.cfg.time_limit_seconds));
                },
                get timeCritical() {
                    return this.timeLeft !== null && this.timeLeft <= 30 && !this.done;
                },

                // ---- reveal phase -------------------------------------------------------
                reveal(idx) {
                    if (this.revealed.includes(idx)) return;
                    this.revealed.push(idx);
                    navigator.vibrate?.(50);
                    this.sfx.play('flip');
                    if (this.revealed.length === this.items.length) {
                        setTimeout(() => this.sfx.play('match'), 380);
                    }
                },
                get allRevealed() {
                    return this.revealed.length >= this.items.length;
                },
                startOrder() {
                    this.phase = 'order';
                    navigator.vibrate?.(30);
                    this.startTimer();
                },

                // ---- drag and drop ------------------------------------------------------
                /** The board is frozen the moment the answer is submitted. */
                get locked() {
                    return this.done;
                },

                rowEls() {
                    return this.$refs.list ? [...this.$refs.list.querySelectorAll('[data-row]')] : [];
                },
                rowEl(key) {
                    return this.$refs.list?.querySelector(`[data-row="${key}"]`) ?? null;
                },
                /**
                 * offsetTop/offsetHeight rather than getBoundingClientRect: they are unaffected by
                 * the transform the dragged card is carrying, so the geometry stays honest without
                 * having to subtract the drag offset back out.
                 */
                measureSlots() {
                    return this.rowEls().map(el => ({ el, top: el.offsetTop, height: el.offsetHeight }));
                },
                captureTops() {
                    const map = new Map();
                    for (const el of this.rowEls()) map.set(el.dataset.row, el.offsetTop);

                    return map;
                },

                /**
                 * FLIP: the rows have already been re-laid-out by Alpine, so invert each one back
                 * to where it was, then release it on the next frame and let the transition carry
                 * it forward. `exceptKey` skips the card under the finger — the pointer owns that
                 * transform and must not fight this one.
                 */
                flip(before, exceptKey = null) {
                    if (window.eduReducedMotion()) return;

                    for (const el of this.rowEls()) {
                        if (exceptKey !== null && el.dataset.row === String(exceptKey)) continue;
                        const prev = before.get(el.dataset.row);
                        if (prev === undefined) continue;
                        const delta = prev - el.offsetTop;
                        if (!delta) continue;

                        el.style.transition = 'none';
                        el.style.transform = `translate3d(0, ${delta}px, 0)`;
                        requestAnimationFrame(() => {
                            el.style.transition = 'transform 200ms cubic-bezier(.2,.7,.3,1)';
                            el.style.transform = '';
                        });
                    }
                },
                /**
                 * Inline styles left by a FLIP have to go before the element becomes the dragged
                 * one: a leftover `transition: transform` would put a 200ms lag between the finger
                 * and the card.
                 */
                clearInlineTransform(el) {
                    if (!el) return;
                    el.style.transition = '';
                    el.style.transform = '';
                },

                dragStart(pos, e) {
                    if (this.locked || this.dragIdx !== null) return;
                    if (e.pointerType === 'mouse' && e.button !== 0) return;

                    const row = e.currentTarget.closest('[data-row]');
                    if (!row) return;

                    this.clearInlineTransform(row);
                    row.classList.remove('sq-settle');

                    this.dragIdx = pos;
                    this.dragY = 0;
                    this.dragStartY = e.clientY;
                    this.dragPointer = e.pointerId;
                    this.dragMoved = false;
                    this.slots = this.measureSlots();

                    /**
                     * The rest of the gesture is tracked on window, NOT on the handle, and this is
                     * load-bearing rather than stylistic.
                     *
                     * Alpine's keyed x-for reorders by detaching and re-inserting the row
                     * (`el.replaceWith(...)` then `prev.after(el)` in x-for.js), and the Pointer
                     * Events spec implicitly releases pointer capture the moment the capturing
                     * element leaves the document. So a capture taken on the handle survives
                     * exactly until the first reorder — after which every later pointermove goes
                     * to whatever happens to be under the finger, the drag dies, and the card can
                     * never travel more than one position. Listening on window means the gesture
                     * outlives the reflow it causes.
                     */
                    this.onWindowMove = ev => this.dragMove(ev);
                    this.onWindowUp = ev => this.dragEnd(ev);
                    window.addEventListener('pointermove', this.onWindowMove, { passive: false });
                    window.addEventListener('pointerup', this.onWindowUp);
                    window.addEventListener('pointercancel', this.onWindowUp);

                    navigator.vibrate?.(30);
                    this.sfx.play('tap');
                },

                releasePointer() {
                    if (this.onWindowMove) window.removeEventListener('pointermove', this.onWindowMove);
                    if (this.onWindowUp) {
                        window.removeEventListener('pointerup', this.onWindowUp);
                        window.removeEventListener('pointercancel', this.onWindowUp);
                    }
                    this.onWindowMove = null;
                    this.onWindowUp = null;
                    this.dragPointer = null;
                },

                dragMove(e) {
                    if (this.dragIdx === null || this.syncing) return;
                    if (this.dragPointer !== null && e.pointerId !== this.dragPointer) return;
                    e.preventDefault();

                    const pos = this.dragIdx;
                    this.dragY = e.clientY - this.dragStartY;

                    const slot = this.slots[pos];
                    if (!slot) return;
                    const center = slot.top + this.dragY + slot.height / 2;

                    // Walk outward from the current index and stop at the first neighbour whose
                    // midpoint has not been crossed — that is the insertion point.
                    let target = pos;
                    if (this.dragY < 0) {
                        for (let j = pos - 1; j >= 0; j--) {
                            if (center >= this.slots[j].top + this.slots[j].height / 2) break;
                            target = j;
                        }
                    } else if (this.dragY > 0) {
                        for (let j = pos + 1; j < this.slots.length; j++) {
                            if (center <= this.slots[j].top + this.slots[j].height / 2) break;
                            target = j;
                        }
                    }
                    if (target !== pos) this.moveDragged(pos, target);
                },

                /**
                 * Shift-and-insert. After the DOM settles the dragged row sits at a different
                 * offsetTop, so the drag origin is re-based by exactly that shift — otherwise the
                 * card would jump out from under the finger by the height of whatever it passed.
                 */
                moveDragged(from, to) {
                    const before = this.captureTops();
                    const oldTop = this.slots[from].top;
                    const moved = this.items[from];

                    this.items.splice(to, 0, ...this.items.splice(from, 1));
                    this.dragIdx = to;
                    this.dragMoved = true;
                    this.syncing = true;
                    navigator.vibrate?.(50);

                    this.$nextTick(() => {
                        this.flip(before, moved.i);
                        this.slots = this.measureSlots();
                        const shift = (this.slots[to]?.top ?? oldTop) - oldTop;
                        this.dragStartY += shift;
                        this.dragY -= shift;
                        this.syncing = false;
                    });
                },

                dragEnd(e) {
                    if (this.dragIdx === null) return;
                    if (this.dragPointer !== null && e.pointerId !== this.dragPointer) return;

                    // Resolved from the item, not from the event target: by now the finger may be
                    // over a completely different card, or over nothing at all.
                    const row = this.rowEl(this.items[this.dragIdx].i);
                    const moved = this.dragMoved;
                    this.dragIdx = null;
                    this.dragY = 0;
                    this.slots = [];
                    this.releasePointer();

                    if (row) {
                        row.classList.add('sq-settle');
                        setTimeout(() => row.classList.remove('sq-settle'), 260);
                    }
                    // Only on a real reorder. Now that a plain mouse click on a card counts as a
                    // grab, firing the drop cue unconditionally would chirp at every stray click.
                    if (moved) {
                        navigator.vibrate?.(20);
                        this.sfx.play('snap');
                    }
                },

                /** Keyboard equivalent of a drag, so the game is playable without pointer input. */
                nudge(pos, delta) {
                    const to = pos + delta;
                    if (this.locked || to < 0 || to >= this.items.length) return;

                    const before = this.captureTops();
                    const moved = this.items[pos];
                    this.items.splice(to, 0, ...this.items.splice(pos, 1));
                    navigator.vibrate?.(50);
                    this.sfx.play('snap');

                    this.$nextTick(() => {
                        this.flip(before);
                        this.rowEl(moved.i)?.querySelector('[data-grip]')?.focus();
                    });
                },

                // ---- checking -----------------------------------------------------------
                /**
                 * One check, one score. Partial credit is proportional to how many steps ended up
                 * in the right place, floored at the same 20% participation share the timeout
                 * path pays out — getting it completely wrong still has to be worth finishing.
                 */
                scoreEarned() {
                    if (this.wrongIdx.length === 0) return this.maxPoints;

                    const share = this.correctCount / this.items.length;

                    return Math.max(Math.round(this.maxPoints * 0.2), Math.round(this.maxPoints * share));
                },
                get correctCount() {
                    return this.items.length - this.wrongIdx.length;
                },
                get allCorrect() {
                    return this.verdictShown && this.wrongIdx.length === 0;
                },
                verdictFor(pos) {
                    if (!this.verdictShown) return null;

                    return this.wrongIdx.includes(pos) ? 'wrong' : 'correct';
                },
                /** How long the staggered verdicts take, so the celebration lands after them. */
                get revealDelay() {
                    return 150 * Math.max(0, this.items.length - 1) + 260;
                },

                check() {
                    if (this.locked) return;

                    Swal.fire({
                        title: @js(__('Periksa urutan sekarang?')),
                        text: @js(__('Urutan dinilai sekali saja dan langsung dikunci, jadi pastikan dulu susunanmu.')),
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#1E5128',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: @js(__('Periksa')),
                        cancelButtonText: @js(__('Batal')),
                    }).then(r => {
                        if (!r.isConfirmed) return;

                        // Locked immediately: the check is final, and the run must not accept
                        // another drag while the verdicts are still animating in.
                        this.done = true;
                        this.stopTimer();

                        this.wrongIdx = this.items.map((it, pos) => it.i !== pos ? pos : null).filter(v => v !== null);
                        this.verdictShown = true;
                        this.earned = this.scoreEarned();

                        if (this.wrongIdx.length === 0) {
                            this.celebrate(this.earned);

                            return;
                        }

                        navigator.vibrate?.([60, 40, 60]);
                        setTimeout(() => this.sfx.play('wrong'), 140);
                        // The score still counts up, just without the fanfare.
                        setTimeout(() => this.countUp(this.earned), this.revealDelay);
                    });
                },

                celebrate(earned) {
                    setTimeout(() => {
                        if (typeof confetti === 'function') {
                            const shared = { spread: 70, colors: ['#1E5128', '#D4AF37', '#FAF9F6'] };
                            confetti({ ...shared, particleCount: 90, origin: { x: 0.3, y: 0.7 } });
                            setTimeout(() => confetti({ ...shared, particleCount: 70, origin: { x: 0.7, y: 0.66 } }), 180);
                        }
                        this.sfx.play('win');
                        navigator.vibrate?.([40, 60, 120]);
                        this.countUp(earned);
                    }, this.revealDelay);
                },

                countUp(target) {
                    if (window.eduReducedMotion() || target <= 0) {
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

                finish() {
                    // The dispatch is delayed, so a second tap inside that window would score twice.
                    if (this.finished) return;
                    this.finished = true;
                    setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned: this.earned }), 400);
                },
            };
        }
    </script>
@endonce
