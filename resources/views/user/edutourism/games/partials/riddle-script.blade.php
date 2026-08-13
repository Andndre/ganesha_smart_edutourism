{{--
    Presentation + logic for the riddle mission, kept out of the game view on purpose.

    The game views render inside <template x-if> in active.blade.php, and script tags inside a
    template are inert until Alpine clones them — so a factory defined there only exists once its
    own mission has been mounted. active.blade.php includes this partial at the top level, one per
    distinct mission type on the point, so the factory is always defined before any board mounts.
    Do not fold it back into the view.

    Presentation notes:
    - The `edu-*` motion vocabulary and the `eduSfx` cues come from partials/game-fx.blade.php.
      Everything prefixed `rd-` below is specific to this game (parchment card, quote watermark,
      the wrong-answer ring flash, and the gold reveal of the solved answer).
    - Same Tailwind v4 rule as the other games: utilities are emitted inside `@layer utilities` and
      unlayered CSS outranks any layer regardless of specificity, so a class here must never own a
      property that a utility on the same element also sets. Where a rule does claim a property
      (the parchment background, the wrong-state border) the markup deliberately drops the matching
      utility instead of fighting it.
    - Every keyframe animates transform/opacity/box-shadow/stroke only. No layout properties.
--}}
@once
    @include('user.edutourism.games.partials.game-fx')

    <style>
        /* ---- the riddle card ------------------------------------------------------------
         *
         * Aged paper rather than a flat tint: two warm radial washes for the light falling
         * across the sheet, plus a low-contrast diagonal weave standing in for fibre. It is one
         * `background` shorthand so the card carries no bg-* utility of its own.
         */
        .rd-parchment {
            background-color: #fdfaf1;
            background-image:
                radial-gradient(120% 90% at 100% 0%, rgba(30, 81, 40, .08), transparent 62%),
                radial-gradient(95% 75% at 0% 100%, rgba(212, 175, 55, .20), transparent 68%),
                repeating-linear-gradient(115deg, rgba(150, 115, 45, .05) 0 2px, transparent 2px 8px);
        }

        /* Oversized opening quote sitting behind the text. Pulled up and left so only the
           shoulder of the glyph shows — a full “ centred in the card reads as a typo. */
        .rd-quote {
            position: absolute;
            top: -2.9rem;
            left: 0.15rem;
            pointer-events: none;
            user-select: none;
            font-family: var(--font-display, ui-serif, Georgia, serif);
            font-size: 11rem;
            line-height: 1;
            font-weight: 900;
            color: rgba(212, 175, 55, .16);
        }

        /* Gold hairline down the reading edge, the same accent the result panel closes on. */
        .rd-seal {
            position: absolute;
            inset-block: 0;
            left: 0;
            width: 3px;
            background: linear-gradient(to bottom, #D4AF37, rgba(212, 175, 55, .15) 55%, #1E5128);
        }

        /* ---- hint ------------------------------------------------------------------------ */

        @keyframes rd-bulb {

            0%,
            100% {
                opacity: .8;
                transform: scale(1);
            }

            50% {
                opacity: 1;
                transform: scale(1.14);
            }
        }

        .rd-bulb {
            animation: rd-bulb 2.4s ease-in-out infinite;
        }

        /* ---- answer field ----------------------------------------------------------------
         *
         * Only the wrong state is styled here, and it owns border-color + box-shadow outright:
         * while it is on, the markup drops its own border/ring utilities, so nothing collides.
         * The ring pulses twice and settles, leaving the border red until the next keystroke.
         */
        @keyframes rd-ring-flash {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(248, 113, 113, 0);
            }

            30%,
            70% {
                box-shadow: 0 0 0 5px rgba(248, 113, 113, .30);
            }
        }

        .rd-field-wrong {
            border-color: #f87171;
            animation: rd-ring-flash .85s ease-out both;
        }

        /* Attempt pips: a quiet reminder that guesses cost points. */
        .rd-pip {
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            background: rgba(25, 26, 25, .14);
            transition: background-color .25s ease, transform .25s cubic-bezier(.34, 1.4, .64, 1);
        }

        .rd-pip-spent {
            background: #f87171;
            transform: scale(1.25);
        }

        /* ---- reveal ----------------------------------------------------------------------- */

        @keyframes rd-draw {
            to {
                stroke-dashoffset: 0;
            }
        }

        /* The ring draws itself, then the tick is drawn inside it. Dash lengths are the path
           lengths for r=10 (2πr ≈ 63) and the checkmark in the same 24-box. */
        .rd-ring {
            stroke-dasharray: 63;
            stroke-dashoffset: 63;
            animation: rd-draw .6s cubic-bezier(.65, 0, .35, 1) .12s forwards;
        }

        .rd-tick {
            stroke-dasharray: 30;
            stroke-dashoffset: 30;
            animation: rd-draw .34s cubic-bezier(.65, 0, .35, 1) .58s forwards;
        }

        /* Gold sweeping across the solved word. The gradient is clipped to the glyphs, so this
           owns the element's colour outright and the markup carries no text-* colour utility. */
        @keyframes rd-gold-sweep {
            0% {
                background-position: 180% 0;
            }

            100% {
                background-position: -180% 0;
            }
        }

        .rd-answer {
            background-image: linear-gradient(100deg,
                    #1E5128 0%, #1E5128 34%, #D4AF37 45%, #f5e2a0 50%, #D4AF37 55%, #1E5128 66%, #1E5128 100%);
            background-size: 250% 100%;
            background-position: 180% 0;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            filter: drop-shadow(0 2px 12px rgba(212, 175, 55, .45));
            animation: rd-gold-sweep 2.6s ease-in-out .5s 2 both;
        }

        @media (prefers-reduced-motion: reduce) {

            .rd-bulb,
            .rd-field-wrong,
            .rd-answer {
                animation: none !important;
            }

            /* Without the draw animation the strokes would stay hidden at their dash offset. */
            .rd-ring,
            .rd-tick {
                stroke-dashoffset: 0;
                animation: none !important;
            }

            .rd-pip {
                transition: none !important;
            }
        }
    </style>

    <script>
        function eduGameRiddle(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                guess: '', attempts: 0, checked: false, wrong: false, solved: false,
                answerShown: '', earned: 0, rootEl: null, hintOpen: false, emptyGuess: false,

                /**
                 * Guesses past this point are all worth the same (the score floor is reached at
                 * maxPoints - 20*4 for a 100-point mission), so the answer is revealed rather than
                 * letting the player stall on a mission that can no longer pay more.
                 */
                maxAttempts: 4,

                init() {
                    // $el inside submit() resolves to whichever element hosts the calling
                    // directive (the answer button), which x-if unmounts once checked=true.
                    // Cache the real component root here instead, where $el is still correct.
                    this.rootEl = this.$el;
                },

                get hasHint() {
                    return !!(this.cfg.hint || '').trim();
                },

                /** Offered only once the player has actually wrestled with it. */
                get canGiveUp() {
                    return this.attempts >= 2 && !this.checked;
                },

                /** Live worth of the mission, so the cost of another wrong guess is visible. */
                get pointsNow() {
                    return Math.max(Math.round(this.maxPoints * 0.2), this.maxPoints - 20 * this.attempts);
                },

                toggleHint() {
                    this.hintOpen = !this.hintOpen;
                    navigator.vibrate?.(20);
                },

                normalize(s) {
                    return s.toLowerCase().replace(/[^a-z0-9\s]/gi, '').replace(/\s+/g, ' ').trim();
                },

                levenshtein(a, b) {
                    if (Math.abs(a.length - b.length) > 1) return 99;
                    const dp = Array.from({ length: a.length + 1 }, (_, i) => [i, ...Array(b.length).fill(0)]);
                    for (let j = 0; j <= b.length; j++) dp[0][j] = j;
                    for (let i = 1; i <= a.length; i++)
                        for (let j = 1; j <= b.length; j++)
                            dp[i][j] = Math.min(dp[i - 1][j] + 1, dp[i][j - 1] + 1, dp[i - 1][j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
                    return dp[a.length][b.length];
                },

                /**
                 * Re-arms the shake/flash. Two guesses in a row can be wrong without the input
                 * changing, and re-applying a class Alpine never removed does not restart its
                 * animation — so the class is dropped and re-added a frame later. Two rAFs, not
                 * one: the first only gets us to the end of the current frame, where Alpine has
                 * still to flush the removal.
                 */
                flashWrong() {
                    this.wrong = false;
                    requestAnimationFrame(() => requestAnimationFrame(() => { this.wrong = true; }));
                },

                /** Clears the red state as soon as the player starts rewriting the guess. */
                onType() {
                    this.wrong = false;
                    this.emptyGuess = false;
                },

                submit() {
                    if (this.checked) return;
                    navigator.vibrate?.(50);

                    const raw = this.guess.trim();
                    if (!raw) {
                        // Not an attempt — an empty box costs nothing, it just gets nudged.
                        this.emptyGuess = true;
                        this.flashWrong();
                        return;
                    }

                    const g = this.normalize(raw);
                    const hit = (this.cfg.answers || []).find(ans => this.levenshtein(g, this.normalize(ans)) <= 1);
                    if (hit) return this.solve();

                    this.attempts++;
                    this.emptyGuess = false;
                    this.flashWrong();
                    navigator.vibrate?.([60, 40, 60]);
                    window.eduSfx?.play('wrong');

                    // A miss is the moment the hint is worth reading, so open it unasked.
                    if (this.hasHint) this.hintOpen = true;

                    if (this.attempts >= this.maxAttempts) this.reveal();
                },

                solve() {
                    this.checked = true;
                    this.solved = true;
                    this.wrong = false;
                    this.answerShown = this.cfg.answers?.[0] ?? this.guess.trim();
                    this.earned = this.pointsNow;
                    navigator.vibrate?.([50, 30, 50]);
                    window.eduSfx?.play('chime');
                    this.burst();
                },

                /** Give up, or run out of attempts: the answer is shown, partial credit stands. */
                reveal() {
                    this.checked = true;
                    this.solved = false;
                    this.wrong = false;
                    this.answerShown = this.cfg.answers?.[0] ?? '';
                    this.earned = this.pointsNow;
                    navigator.vibrate?.(30);
                },

                /** Gold first, then a wider, slower second puff so the burst has a tail. */
                burst() {
                    if (window.eduReducedMotion?.() || typeof window.confetti !== 'function') return;
                    const colors = ['#D4AF37', '#f0d97d', '#1E5128', '#4e9c6b', '#ffffff'];
                    window.confetti({ particleCount: 90, spread: 75, startVelocity: 42, scalar: .95, origin: { y: 0.62 }, colors });
                    setTimeout(() => window.confetti?.({ particleCount: 45, spread: 115, decay: .92, scalar: .8, origin: { y: 0.55 }, colors }), 220);
                },

                continueMission() {
                    setTimeout(() => this.rootEl.dispatchEvent(new CustomEvent('mission-complete', { bubbles: true, detail: { id: this.missionId, earned: this.earned } })), 400);
                },
            };
        }
    </script>
@endonce
