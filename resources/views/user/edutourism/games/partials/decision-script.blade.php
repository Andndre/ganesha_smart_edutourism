{{--
    Game logic for the decision mission, kept out of the game view on purpose.

    The game views render inside <template x-if> in active.blade.php, and script tags inside a
    template are inert until Alpine clones them — so a factory defined there only exists once its
    own mission has been mounted. active.blade.php includes this partial at the top level, one per
    distinct mission type on the point, so the factory is always defined before any board mounts.
    Do not fold it back into the view.
--}}
@once
    <script>
        function eduGameDecision(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                idx: 0, selected: null, chosen: null, checked: false, correctCount: 0, done: false,

                get scenario() { return this.cfg.scenarios[this.idx]; },
                choose(oIdx) {
                    if (this.checked || this.done) return;
                    navigator.vibrate?.(50);
                    this.selected = oIdx;
                },
                check() {
                    if (this.checked || this.done || this.selected === null) return;
                    this.chosen = this.selected;
                    this.checked = true;
                    if (this.scenario.options[this.chosen].correct) {
                        this.correctCount++;
                        confetti?.({ particleCount: 40, spread: 55, origin: { y: 0.7 } });
                    } else {
                        navigator.vibrate?.([60, 40, 60]);
                    }
                },
                next() {
                    if (this.idx + 1 < this.cfg.scenarios.length) {
                        this.idx++;
                        this.selected = null;
                        this.chosen = null;
                        this.checked = false;
                    } else {
                        this.done = true;
                        const earned = Math.round(this.maxPoints * this.correctCount / this.cfg.scenarios.length);
                        setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned }), 400);
                    }
                },
                optionClass(oIdx) {
                    if (!this.checked) {
                        return this.selected === oIdx
                            ? 'border-primary bg-primary/10 text-primary'
                            : 'border-gray-200 bg-white text-gray-700 hover:border-emerald-200 hover:bg-emerald-50';
                    }
                    if (this.scenario.options[oIdx].correct) return 'border-emerald-400 bg-emerald-50 text-emerald-800';
                    if (this.chosen === oIdx) return 'quiz-shake border-red-300 bg-red-50 text-red-700';
                    return 'border-gray-100 bg-gray-50 text-gray-400';
                },
            };
        }
    </script>
@endonce
