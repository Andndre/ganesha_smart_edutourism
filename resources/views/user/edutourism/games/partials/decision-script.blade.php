{{--
    Presentation + logic for the decision mission, kept out of the game view on purpose.

    The game views render inside <template x-if> in active.blade.php, and script tags inside a
    template are inert until Alpine clones them — so a factory defined there only exists once its
    own mission has been mounted. active.blade.php includes this partial at the top level, one per
    distinct mission type on the point, so the factory is always defined before any board mounts.
    Do not fold it back into the view.

    Presentation notes:
    - The `edu-*` motion vocabulary, the shared header chrome (chips, progress segments, option
      markers, the explanation drop) and the `eduSfx` cues all come from game-fx.blade.php. The
      only thing left below is `dc-scene`, the before/after frame, which no other game has.
    - Same Tailwind v4 rule as the other games: this sheet is unlayered, so it outranks every
      utility regardless of specificity. The verdict colours therefore stay in `optionClass()` as
      utilities and this sheet only claims properties no utility on those elements sets.
    - Every keyframe/transition animates transform + opacity (plus background-color on the tiny
      progress segments, which is cheap and never reflows).
--}}
@once
    @include('user.edutourism.games.partials.game-fx')

    <style>
        /* ---- before / after scene ----------------------------------------------------------
         *
         * Both frames are stacked and both are in the DOM from the start, so the "after" image is
         * already decoded when the decision lands and the dissolve cannot stutter on its first
         * frame. A swipe was the other option in the brief; a dissolve with a slow settle reads
         * better here because the two photos share a framing — a wipe would just look like a glitch.
         */
        .dc-scene {
            position: relative;
            overflow: hidden;
            aspect-ratio: 16 / 10;
            background: #eef1ee;
        }

        .dc-scene-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 1;
            transform: scale(1);
            transition: opacity .75s ease, transform 1.1s cubic-bezier(.22, 1, .36, 1);
        }

        /* The hidden frame sits slightly zoomed in, so the swap has a gentle push behind it. */
        .dc-scene-img.is-hidden {
            opacity: 0;
            transform: scale(1.05);
        }

        .dc-scene-tag {
            position: absolute;
            top: .6rem;
            left: .6rem;
            border-radius: 9999px;
            padding: .3rem .6rem;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: #fff;
            background: rgba(25, 26, 25, .55);
            backdrop-filter: blur(4px);
        }

        @media (prefers-reduced-motion: reduce) {
            .dc-scene-img {
                transition: none !important;
            }
        }
    </style>

    <script>
        function eduGameDecision(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                idx: 0, selected: null, chosen: null, checked: false, done: false,

                /** Verdict per scenario, index-aligned with cfg.scenarios. Drives the segments. */
                outcomes: [],

                get scenario() { return this.cfg.scenarios[this.idx]; },

                get total() { return this.cfg.scenarios.length; },

                get correctCount() { return this.outcomes.filter(Boolean).length; },

                /** Points banked so far — the chip has to move for the segments to mean anything. */
                get earnedSoFar() { return Math.round(this.maxPoints * this.correctCount / this.total); },

                get lastCorrect() { return this.checked && !!this.scenario.options[this.chosen]?.correct; },

                /** The payoff frame is only earned: a bad decision leaves the scene as it was. */
                get showAfter() { return this.lastCorrect && !!this.scenario.image_after; },

                choose(oIdx) {
                    if (this.checked || this.done) return;
                    navigator.vibrate?.(40);
                    window.eduSfx?.play('tap');
                    this.selected = oIdx;
                },

                check() {
                    if (this.checked || this.done || this.selected === null) return;
                    this.chosen = this.selected;
                    this.checked = true;

                    const correct = !!this.scenario.options[this.chosen].correct;
                    this.outcomes[this.idx] = correct;

                    if (correct) {
                        navigator.vibrate?.([50, 30, 50]);
                        window.eduSfx?.play('nature');
                        this.burst();
                    } else {
                        navigator.vibrate?.([60, 40, 60]);
                        window.eduSfx?.play('wrong');
                    }
                },

                /**
                 * Leaves rather than a party popper: low gravity, high drift and a slow decay, so
                 * the particles drift down across the scene instead of shooting past it.
                 */
                burst() {
                    if (window.eduReducedMotion?.() || typeof window.confetti !== 'function') return;
                    window.confetti({
                        particleCount: 36, spread: 78, startVelocity: 26, gravity: .55, decay: .93,
                        drift: .5, scalar: 1.1, ticks: 260, origin: { y: 0.55 },
                        colors: ['#1E5128', '#4e9c6b', '#8fd19e', '#D4AF37', '#e7f3ea'],
                    });
                },

                next() {
                    if (this.idx + 1 < this.total) {
                        this.idx++;
                        this.selected = null;
                        this.chosen = null;
                        this.checked = false;
                        return;
                    }
                    this.done = true;
                    const earned = this.earnedSoFar;
                    setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned }), 400);
                },

                /** Progress segment: past scenarios keep their verdict, the current one is a stub. */
                segClass(i) {
                    if (i < this.idx || (i === this.idx && this.checked)) {
                        return this.outcomes[i] ? 'edu-seg-done' : 'edu-seg-done edu-seg-bad';
                    }
                    return i === this.idx ? 'edu-seg-live' : '';
                },

                optionClass(oIdx) {
                    if (!this.checked) {
                        return this.selected === oIdx
                            ? 'border-primary bg-primary/5 ring-4 ring-primary/15 text-charcoal'
                            : 'border-gray-200 bg-white text-gray-700 hover:border-primary/50 hover:bg-primary/5';
                    }
                    // The right answer is always surfaced, even when it was not the one picked —
                    // this game teaches the consequence, it does not just grade the guess.
                    if (this.scenario.options[oIdx].correct) return 'border-emerald-500 bg-emerald-50 text-emerald-900 edu-shine';
                    if (this.chosen === oIdx) return 'border-red-400 bg-red-50 text-red-900 edu-shake edu-flash';
                    return 'border-gray-100 bg-gray-50/70 text-gray-400';
                },

                markClass(oIdx) {
                    if (!this.checked) {
                        return this.selected === oIdx
                            ? 'bg-primary text-white edu-mark-active'
                            : 'bg-gray-100 text-gray-500';
                    }
                    if (this.scenario.options[oIdx].correct) return 'bg-emerald-500 text-white edu-mark-active';
                    if (this.chosen === oIdx) return 'bg-red-500 text-white edu-mark-active';
                    return 'bg-gray-100 text-gray-300';
                },

                /** 'leaf' | 'warn' | 'letter' — which glyph the marker shows. */
                markKind(oIdx) {
                    if (!this.checked) return 'letter';
                    if (this.scenario.options[oIdx].correct) return 'leaf';
                    return this.chosen === oIdx ? 'warn' : 'letter';
                },

                letter(oIdx) { return String.fromCharCode(65 + oIdx); },
            };
        }
    </script>
@endonce
