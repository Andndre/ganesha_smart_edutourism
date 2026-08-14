{{--
    Shared presentation layer for the edutourism mini-games: the `edu-*` motion vocabulary and the
    synthesised sound cues. Included (not duplicated) by every game that needs them — the @once
    below means a point carrying several missions still emits this exactly one time.

    Design notes carried over from the matching game, which is where this originally lived:
    - Every keyframe animates transform/opacity only, so entry-level phones stay on the compositor
      and hold 60fps. Nothing here animates width/height/top/left.
    - All motion is disabled under prefers-reduced-motion at the bottom of the sheet. When you add
      a keyframe, add its class to that block in the same edit.
    - Sound is synthesised with the Web Audio API rather than shipped as files: the cues cost
      nothing to download and the context is only built on the player's first real tap, which is
      what browser autoplay policies require anyway.
--}}
@once
    <style>
        /* ---- keyframes: transform/opacity only (compositor-friendly) ---- */
        @keyframes edu-shake {

            10%,
            90% {
                transform: translate3d(-2px, 0, 0);
            }

            20%,
            80% {
                transform: translate3d(4px, 0, 0);
            }

            30%,
            50%,
            70% {
                transform: translate3d(-7px, 0, 0);
            }

            40%,
            60% {
                transform: translate3d(7px, 0, 0);
            }
        }

        @keyframes edu-pop {
            0% {
                transform: scale(1);
            }

            45% {
                transform: scale(1.06);
            }

            70% {
                transform: scale(0.98);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes edu-badge-pop {
            0% {
                opacity: 0;
                transform: scale(0) rotate(-25deg);
            }

            60% {
                opacity: 1;
                transform: scale(1.25) rotate(4deg);
            }

            100% {
                opacity: 1;
                transform: scale(1) rotate(0);
            }
        }

        @keyframes edu-shine {
            0% {
                transform: translate3d(-120%, 0, 0) skewX(-18deg);
                opacity: 0;
            }

            15% {
                opacity: 1;
            }

            100% {
                transform: translate3d(220%, 0, 0) skewX(-18deg);
                opacity: 0;
            }
        }

        @keyframes edu-pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(30, 81, 40, 0.45);
            }

            50% {
                box-shadow: 0 0 0 7px rgba(30, 81, 40, 0);
            }
        }

        @keyframes edu-flash-red {
            0% {
                opacity: 0.75;
            }

            100% {
                opacity: 0;
            }
        }

        @keyframes edu-slide-up {
            0% {
                opacity: 0;
                transform: translate3d(0, 22px, 0) scale(0.97);
            }

            100% {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes edu-rise {
            0% {
                opacity: 0;
                transform: translate3d(0, 10px, 0);
            }

            100% {
                opacity: 1;
                transform: none;
            }
        }

        .edu-shake {
            animation: edu-shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
        }

        .edu-pop {
            animation: edu-pop 0.5s cubic-bezier(.34, 1.56, .64, 1) both;
        }

        .edu-badge-pop {
            animation: edu-badge-pop 0.42s cubic-bezier(.34, 1.56, .64, 1) both;
        }

        .edu-pulse-glow {
            animation: edu-pulse-glow 1.7s ease-out infinite;
        }

        .edu-slide-up {
            animation: edu-slide-up 0.5s cubic-bezier(.34, 1.4, .64, 1) both;
        }

        .edu-rise {
            animation: edu-rise 0.36s cubic-bezier(.34, 1.4, .64, 1) both;
        }

        /**
         * Sweep of light across a freshly-confirmed card.
         * The delay is exposed as a custom property, and kept out of the `animation` shorthand so
         * it is not reset by it. Callers that stagger verdicts across a list need this: an inline
         * animation-delay on the card cannot reach an animation running on its pseudo-element, so
         * without the variable every card's sweep would fire at the same instant while the cards
         * themselves popped in sequence.
         */
        .edu-shine::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            background: linear-gradient(100deg, transparent 35%, rgba(255, 255, 255, .85) 50%, transparent 65%);
            animation: edu-shine 0.9s ease-out both;
            animation-delay: var(--edu-shine-delay, 0.1s);
        }

        /* Red wash that fades out on a wrong answer, layered over the card's own background.
           Same staggering story as .edu-shine above. */
        .edu-flash::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            background: rgb(248 113 113);
            animation: edu-flash-red 0.75s ease-out both;
            animation-delay: var(--edu-flash-delay, 0s);
        }

        /* ---- shared chrome: chips, labels, progress segments, verdict marks ----------------
         *
         * The quiz, decision and riddle missions all render the same header furniture and the
         * same "consequence card", so it lives here instead of being copied into three sheets.
         * (sequence.blade.php keeps its own `sq-*` variants — it is mid-rework on this branch and
         * folding it in here would collide with that.)
         *
         * Same Tailwind rule as the rest of this file: these rules own shape and motion only.
         * Verdict colour arrives as utilities from each game's optionClass(), so nothing here has
         * to out-specify anything.
         */
        .edu-chip {
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

        .edu-chip-gold {
            background: rgba(212, 175, 55, .18);
            color: #8a6a10;
        }

        .edu-chip-plain {
            background: rgba(25, 26, 25, .05);
            color: #6b7280;
        }

        .edu-label {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        /* One segment per question/scenario rather than a single sliding bar: the shape of the
           run stays readable at a glance — which answers helped, which cost points. */
        .edu-seg {
            flex: 1;
            overflow: hidden;
            height: 6px;
            border-radius: 9999px;
            background: rgba(25, 26, 25, .10);
        }

        /* Grows by scaleX, not width, so the fill stays on the compositor. */
        .edu-seg-fill {
            display: block;
            width: 100%;
            height: 100%;
            border-radius: inherit;
            transform: scaleX(0);
            transform-origin: left center;
            background: linear-gradient(90deg, #1E5128, #2f8f52);
            transition: transform .55s cubic-bezier(.34, 1.4, .64, 1), background .3s ease;
        }

        /* The step being played: a stub of fill, so "you are here" is visible before the
           verdict lands. */
        .edu-seg-live {
            transform: scaleX(.34);
            background: linear-gradient(90deg, rgba(30, 81, 40, .45), rgba(47, 143, 82, .35));
        }

        .edu-seg-done {
            transform: scaleX(1);
        }

        .edu-seg-bad {
            background: linear-gradient(90deg, #ef4444, #f87171);
        }

        /* The letter/verdict disc on an option card. */
        .edu-mark {
            transition: background-color .25s ease, color .25s ease, transform .25s cubic-bezier(.34, 1.4, .64, 1);
        }

        .edu-mark-active {
            transform: scale(1.08);
        }

        @keyframes edu-drop {
            0% {
                opacity: 0;
                transform: translate3d(0, -10px, 0) scale(.985);
            }

            100% {
                opacity: 1;
                transform: none;
            }
        }

        /* Drops out of the options above it rather than rising from below: the explanation reads
           as coming from the card that was just pressed. */
        .edu-drop {
            animation: edu-drop .42s cubic-bezier(.34, 1.4, .64, 1) both;
        }

        /* Keeps a small glyph visually small while giving it the 44x44 tap target the design
           system asks for. Pseudo-element rather than padding so it cannot push the text line
           it sits in taller. */
        .edu-tap {
            position: relative;
        }

        .edu-tap::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 44px;
            height: 44px;
            transform: translate(-50%, -50%);
        }

        .edu-card {
            transition: transform .18s cubic-bezier(.34, 1.4, .64, 1), box-shadow .18s ease, border-color .18s ease, background-color .18s ease;
        }

        /* Hover lift is pointer-only: on touch it would stick after the tap. */
        @media (hover: hover) {
            .edu-card:not(.edu-locked):hover {
                transform: translate3d(0, -2px, 0);
            }
        }

        .edu-link {
            fill: none;
            stroke-linecap: round;
            transition: stroke .25s ease;
        }

        /**
         * The primary action docks to the bottom of the mission overlay so it stays reachable with
         * one thumb, without the player having to scroll past the board or the result panel first.
         *
         * Fixed, not sticky: every CTA is the last child of its block, so a sticky element has no
         * room left to travel inside its containing block and simply sits inline at the end of the
         * content — the exact scroll-to-reach-it problem this is meant to solve. Fixed pins it to
         * the viewport instead. The overlay itself is `fixed inset-0` with no transformed ancestor,
         * so the viewport and the overlay are the same box here.
         *
         * The backdrop matches the mission overlay (#FAF9F6) and the fade above it stops cards from
         * looking sliced off as they pass under. `.edu-mission-scroll` below reserves the matching
         * space so the last card is never trapped behind the bar.
         */
        .edu-sticky-cta {
            position: fixed;
            inset-inline: 0;
            bottom: 0;
            z-index: 30;
            padding: 0.75rem 1.25rem;
            /* Clears the iOS home indicator without adding a gap on Android. */
            padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 0.75rem);
            background: #FAF9F6;
            /* Centring lives on the bar, not on the button. See below. */
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* The bar spans the viewport, but the button tracks the mission column so it doesn't
           stretch into a full-width slab on a desktop screen.
           Deliberately no `display` here: this sheet is unlayered, so it outranks every Tailwind
           utility, and a `display: block` would silently kill the `flex` on any CTA that lays an
           icon out beside its label — the icon drops to the start of a block box and the whole
           button falls apart. Auto margins cannot centre an inline-block <button> either, which is
           why the bar itself is the flex container instead. */
        .edu-sticky-cta>* {
            width: 100%;
            max-width: 28rem;
        }

        .edu-sticky-cta::before {
            content: '';
            position: absolute;
            inset-inline: 0;
            bottom: 100%;
            height: 1.25rem;
            pointer-events: none;
            background: linear-gradient(to top, #FAF9F6, rgba(250, 249, 246, 0));
        }

        /* Room for the docked bar: button (2.75rem) + its padding + the safe-area inset. */
        .edu-mission-scroll {
            padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 6rem);
        }

        @media (prefers-reduced-motion: reduce) {

            .edu-shake,
            .edu-pop,
            .edu-badge-pop,
            .edu-pulse-glow,
            .edu-slide-up,
            .edu-rise,
            .edu-drop,
            .edu-shine::after,
            .edu-flash::before {
                animation: none !important;
            }

            .edu-card,
            .edu-seg-fill,
            .edu-mark {
                transition: none !important;
            }
        }
    </style>

    <script>
        /**
         * Shared reduced-motion probe. Read per call rather than cached: the OS setting can be
         * toggled while the page is open, and a mission can outlive that change.
         */
        window.eduReducedMotion = function () {
            return window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;
        };

        /**
         * Fisher-Yates, in place. `sort(() => Math.random() - 0.5)` is not a shuffle: it feeds an
         * inconsistent comparator to a merge sort, and on short arrays it leaves most elements
         * near where they started — which for these games means handing the player a board that
         * is already half solved.
         */
        window.eduShuffle = function (arr) {
            for (let i = arr.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [arr[i], arr[j]] = [arr[j], arr[i]];
            }
            return arr;
        };

        /**
         * Synthesised cues — no audio files to download, and nothing is built until the player's
         * first tap, which keeps us on the right side of autoplay policies.
         * There is deliberately no mute switch: the device's own volume and silent mode already
         * cover it, and a per-component toggle read as a control for the content audio button.
         */
        window.eduSfx = window.eduSfx || {
            ctx: null,

            /** @param {'tap'|'match'|'wrong'|'win'|'snap'|'flip'|'chime'|'nature'} kind */
            play(kind) {
                try {
                    this.ctx = this.ctx || new(window.AudioContext || window.webkitAudioContext)();
                    if (this.ctx.state === 'suspended') this.ctx.resume();
                } catch {
                    return; // No Web Audio (or blocked) — the game is fully playable in silence.
                }

                const notes = {
                    tap: [{ f: 520, t: 0, d: 0.06, g: 0.05, w: 'triangle' }],
                    match: [
                        { f: 660, t: 0, d: 0.13, g: 0.09, w: 'sine' },
                        { f: 990, t: 0.09, d: 0.20, g: 0.08, w: 'sine' },
                    ],
                    wrong: [{ f: 150, t: 0, d: 0.20, g: 0.07, w: 'sawtooth' }],
                    win: [
                        { f: 523, t: 0, d: 0.16, g: 0.09, w: 'sine' },
                        { f: 659, t: 0.12, d: 0.16, g: 0.09, w: 'sine' },
                        { f: 784, t: 0.24, d: 0.16, g: 0.09, w: 'sine' },
                        { f: 1046, t: 0.36, d: 0.34, g: 0.10, w: 'sine' },
                    ],
                    /* Dropping a dragged card: a short woodblock-ish click, quieter than `tap`
                       because it fires on every single reorder. */
                    snap: [
                        { f: 320, t: 0, d: 0.05, g: 0.06, w: 'triangle' },
                        { f: 640, t: 0.03, d: 0.07, g: 0.04, w: 'triangle' },
                    ],
                    /* Turning a face-down card over. */
                    flip: [{ f: 780, t: 0, d: 0.09, g: 0.05, w: 'sine' }],
                    /* Solving a riddle: a struck bell rather than the `win` fanfare. Partials
                       rise on a G major triad and are stacked, not sequenced — they overlap into
                       one shimmering tone, each quieter and longer than the one below it. */
                    chime: [
                        { f: 784, t: 0, d: 0.55, g: 0.070, w: 'sine' },
                        { f: 1174, t: 0.06, d: 0.60, g: 0.050, w: 'sine' },
                        { f: 1568, t: 0.14, d: 0.75, g: 0.040, w: 'sine' },
                        { f: 2349, t: 0.22, d: 0.55, g: 0.022, w: 'triangle' },
                    ],
                    /* An eco-friendly decision: leaves settling, then a wooden chime over them.
                       The rustle is the `noise` voice below — a pitched oscillator cannot make a
                       broadband texture, and this cue is the one place the games need one. */
                    nature: [
                        { w: 'noise', f: 1400, f2: 3200, q: 0.9, t: 0, d: 0.45, g: 0.045 },
                        { w: 'noise', f: 900, f2: 2200, q: 1.1, t: 0.18, d: 0.40, g: 0.030 },
                        { f: 880, t: 0.16, d: 0.30, g: 0.060, w: 'sine' },
                        { f: 1318, t: 0.28, d: 0.45, g: 0.045, w: 'sine' },
                    ],
                } [kind];
                if (!notes) return;

                const now = this.ctx.currentTime;
                for (const n of notes) {
                    const gain = this.ctx.createGain();
                    // Ramp to a floor rather than 0: exponentialRamp cannot reach zero.
                    gain.gain.setValueAtTime(n.g, now + n.t);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + n.t + n.d);

                    /**
                     * A `noise` voice: white noise through a band-pass whose centre sweeps from
                     * `f` to `f2`. That sweep is what turns a flat hiss into something that reads
                     * as leaves or water. The buffer is built per call and is only ever a few
                     * hundred milliseconds long, so it is cheaper than shipping an audio file.
                     */
                    if (n.w === 'noise') {
                        const frames = Math.max(1, Math.ceil(this.ctx.sampleRate * n.d));
                        const buffer = this.ctx.createBuffer(1, frames, this.ctx.sampleRate);
                        const data = buffer.getChannelData(0);
                        for (let i = 0; i < frames; i++) data[i] = Math.random() * 2 - 1;

                        const src = this.ctx.createBufferSource();
                        src.buffer = buffer;

                        const band = this.ctx.createBiquadFilter();
                        band.type = 'bandpass';
                        band.Q.value = n.q ?? 1;
                        band.frequency.setValueAtTime(n.f, now + n.t);
                        if (n.f2) band.frequency.exponentialRampToValueAtTime(n.f2, now + n.t + n.d);

                        src.connect(band).connect(gain).connect(this.ctx.destination);
                        src.start(now + n.t);
                        src.stop(now + n.t + n.d);
                        continue;
                    }

                    const osc = this.ctx.createOscillator();
                    osc.type = n.w;
                    osc.frequency.setValueAtTime(n.f, now + n.t);
                    osc.connect(gain).connect(this.ctx.destination);
                    osc.start(now + n.t);
                    osc.stop(now + n.t + n.d);
                }
            },
        };
    </script>
@endonce
