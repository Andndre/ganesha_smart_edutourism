{{--
    Riddle reveal.
    config: { riddle, answers: ["merajan", "sanggah"], hint?, success_text? }
    Matching: case-insensitive, punctuation-stripped, Levenshtein distance ≤ 1 (light typo tolerance).
    Scoring: points - 20*(wrong attempts), min 20% of points.
    Emits: mission-complete {id, earned}
--}}
@once
    <script>
        function eduGameRiddle(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                guess: '', attempts: 0, wrong: false, solved: false, answerShown: '',

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
                    if (this.solved || !this.guess.trim()) return;
                    const g = this.normalize(this.guess);
                    const hit = this.cfg.answers.find(ans => this.levenshtein(g, this.normalize(ans)) <= 1);
                    if (hit) {
                        this.solved = true;
                        this.answerShown = this.cfg.answers[0];
                        navigator.vibrate?.([50, 30, 50]);
                        confetti?.({ particleCount: 90, spread: 75, origin: { y: 0.6 } });
                        const earned = Math.max(Math.round(this.maxPoints * 0.2), this.maxPoints - 20 * this.attempts);
                        setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned }), 1400);
                    } else {
                        this.attempts++;
                        this.wrong = true;
                        navigator.vibrate?.([60, 40, 60]);
                        setTimeout(() => this.wrong = false, 600);
                    }
                },
            };
        }
    </script>
@endonce

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameRiddle(@js($cfg), @js($mission->id), @js($mission->points))" class="space-y-4">
    <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-4">
        <p class="font-display text-charcoal text-base font-bold italic leading-relaxed">
            “{{ $cfg['riddle'] ?? '' }}”
        </p>
    </div>

    @if (!empty($cfg['hint']))
        <details class="text-xs text-gray-400">
            <summary class="cursor-pointer font-semibold">{{ __('Butuh petunjuk?') }}</summary>
            <p class="mt-1">{{ $cfg['hint'] }}</p>
        </details>
    @endif

    <template x-if="!solved">
        <form @submit.prevent="submit()" class="space-y-3">
            <input type="text" x-model="guess" :class="wrong ? 'quiz-shake border-red-300' : 'border-gray-200'"
                class="focus:border-primary w-full rounded-xl border-2 p-3 text-sm font-medium text-gray-800 outline-none transition"
                placeholder="{{ __('Ketik jawabanmu...') }}" autocomplete="off" />
            <p x-show="attempts > 0" class="text-xs font-semibold text-red-500" x-cloak>
                {{ __('Belum tepat, coba lagi!') }}</p>
            <button type="submit"
                class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
                {{ __('Jawab Teka-Teki') }}
            </button>
        </form>
    </template>

    <template x-if="solved">
        <div class="quiz-success-icon rounded-2xl border border-emerald-100 bg-emerald-50 p-6 text-center">
            <div
                class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="quiz-success-check h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-lg font-black capitalize text-emerald-700" x-text="answerShown"></p>
            <p class="mt-1 text-sm text-emerald-600">{{ $cfg['success_text'] ?? __('Tepat sekali!') }}</p>
        </div>
    </template>
</div>
