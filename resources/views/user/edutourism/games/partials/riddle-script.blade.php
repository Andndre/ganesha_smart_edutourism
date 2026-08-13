{{--
    Game logic for the riddle mission, kept out of the game view on purpose.

    The game views render inside <template x-if> in active.blade.php, and script tags inside a
    template are inert until Alpine clones them — so a factory defined there only exists once its
    own mission has been mounted. active.blade.php includes this partial at the top level, one per
    distinct mission type on the point, so the factory is always defined before any board mounts.
    Do not fold it back into the view.
--}}
@once
    <script>
        function eduGameRiddle(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                guess: '', attempts: 0, checked: false, wrong: false, solved: false, answerShown: '', earned: 0, rootEl: null,

                init() {
                    // $el inside submit() resolves to whichever element hosts the calling
                    // directive (the answer button), which x-if unmounts once solved=true.
                    // Cache the real component root here instead, where $el is still correct.
                    this.rootEl = this.$el;
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
                submit() {
                    if (this.checked || !this.guess.trim()) return;
                    const g = this.normalize(this.guess);
                    const hit = this.cfg.answers.find(ans => this.levenshtein(g, this.normalize(ans)) <= 1);
                    this.checked = true;
                    if (hit) {
                        this.solved = true;
                        this.answerShown = this.cfg.answers[0];
                        navigator.vibrate?.([50, 30, 50]);
                        confetti?.({ particleCount: 90, spread: 75, origin: { y: 0.6 } });
                        this.earned = Math.max(Math.round(this.maxPoints * 0.2), this.maxPoints - 20 * this.attempts);
                    } else {
                        this.attempts++;
                        this.wrong = true;
                        navigator.vibrate?.([60, 40, 60]);
                        this.earned = Math.max(Math.round(this.maxPoints * 0.2), this.maxPoints - 20 * this.attempts);
                    }
                },
                continueMission() {
                    setTimeout(() => this.rootEl.dispatchEvent(new CustomEvent('mission-complete', { bubbles: true, detail: { id: this.missionId, earned: this.earned } })), 400);
                },
            };
        }
    </script>
@endonce
