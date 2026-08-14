{{--
    Game logic for the quiz mission, kept out of the game view on purpose.

    The game views render inside <template x-if> in active.blade.php, and script tags inside a
    template are inert until Alpine clones them — so a factory defined there only exists once its
    own mission has been mounted. active.blade.php includes this partial at the top level, one per
    distinct mission type on the point, so the factory is always defined before any board mounts.
    Do not fold it back into the view.

    There is no stylesheet here: every class the quiz uses (chips, progress segments, option
    markers, the explanation drop, the verdict animations) is shared chrome from
    partials/game-fx.blade.php, which this partial pulls in.
--}}
@once
    @include('user.edutourism.games.partials.game-fx')

    <script>
        function eduGameQuiz(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                idx: 0, selected: null, chosen: null, checked: false, done: false,

                /** Verdict per question, index-aligned with cfg.questions. Drives the segments. */
                outcomes: [],

                get question() { return this.cfg.questions[this.idx]; },

                get total() { return this.cfg.questions.length; },

                get answer() { return (this.question.correct_option || '').toUpperCase(); },

                /**
                 * Blank choices are dropped. The admin form always writes all four `option_*`
                 * keys, so a three-choice question arrives with an empty `option_d` that would
                 * otherwise render as an empty — and tappable — card.
                 */
                get options() {
                    return ['A', 'B', 'C', 'D']
                        .map(letter => ({ letter, text: (this.question['option_' + letter.toLowerCase()] ?? '').trim() }))
                        .filter(opt => opt.text !== '');
                },

                get correctCount() { return this.outcomes.filter(Boolean).length; },

                /** Points banked so far — the chip has to move for the segments to mean anything. */
                get earnedSoFar() { return this.total ? Math.round(this.maxPoints * this.correctCount / this.total) : 0; },

                get lastCorrect() { return this.checked && this.chosen === this.answer; },

                choose(letter) {
                    if (this.checked || this.done) return;
                    navigator.vibrate?.(40);
                    window.eduSfx?.play('tap');
                    this.selected = letter;
                },

                check() {
                    if (this.checked || this.done || this.selected === null) return;
                    this.chosen = this.selected;
                    this.checked = true;
                    this.outcomes[this.idx] = this.chosen === this.answer;

                    if (this.outcomes[this.idx]) {
                        navigator.vibrate?.([50, 30, 50]);
                        window.eduSfx?.play('match');
                        this.burst();
                    } else {
                        navigator.vibrate?.([60, 40, 60]);
                        window.eduSfx?.play('wrong');
                    }
                },

                /** Small and quick: this fires up to five times in one mission. */
                burst() {
                    if (window.eduReducedMotion?.() || typeof window.confetti !== 'function') return;
                    window.confetti({
                        particleCount: 48, spread: 62, startVelocity: 34, scalar: .9, ticks: 180,
                        origin: { y: 0.65 },
                        colors: ['#D4AF37', '#f0d97d', '#1E5128', '#4e9c6b', '#ffffff'],
                    });
                },

                next() {
                    // The final dispatch is delayed, so a second tap inside that window would score twice.
                    if (this.done) return;
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

                /** Progress segment: past questions keep their verdict, the current one is a stub. */
                segClass(i) {
                    if (i < this.idx || (i === this.idx && this.checked)) {
                        return this.outcomes[i] ? 'edu-seg-done' : 'edu-seg-done edu-seg-bad';
                    }
                    return i === this.idx ? 'edu-seg-live' : '';
                },

                optionClass(letter) {
                    if (!this.checked) {
                        return this.selected === letter
                            ? 'border-primary bg-primary/5 ring-4 ring-primary/15 text-charcoal'
                            : 'border-gray-200 bg-white text-gray-700 hover:border-primary/50 hover:bg-primary/5';
                    }
                    // The right answer is always surfaced, even when it was not the one picked.
                    if (letter === this.answer) return 'border-emerald-500 bg-emerald-50 text-emerald-900 edu-shine';
                    if (this.chosen === letter) return 'border-red-400 bg-red-50 text-red-900 edu-shake edu-flash';
                    return 'border-gray-100 bg-gray-50/70 text-gray-400';
                },

                markClass(letter) {
                    if (!this.checked) {
                        return this.selected === letter
                            ? 'bg-primary text-white edu-mark-active'
                            : 'bg-gray-100 text-gray-500';
                    }
                    if (letter === this.answer) return 'bg-emerald-500 text-white edu-mark-active';
                    if (this.chosen === letter) return 'bg-red-500 text-white edu-mark-active';
                    return 'bg-gray-100 text-gray-300';
                },

                /** 'check' | 'cross' | 'letter' — which glyph the marker disc shows. */
                markKind(letter) {
                    if (!this.checked) return 'letter';
                    if (letter === this.answer) return 'check';
                    return this.chosen === letter ? 'cross' : 'letter';
                },
            };
        }
    </script>
@endonce
