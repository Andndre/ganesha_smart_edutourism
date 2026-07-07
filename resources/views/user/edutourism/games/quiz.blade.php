{{--
    Multiple-choice quiz (Point 1 "Unlock the Village").
    config: { questions: [{ prompt, option_a, option_b, option_c, option_d, correct_option: 'A'|'B'|'C'|'D', explanation? }] }
    Scoring: points split evenly per question; correct pick = full share, wrong = 0 for that question.
    Emits: mission-complete {id, earned}
--}}
@once
    <script>
        function eduGameQuiz(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                idx: 0, chosen: null, correctCount: 0, done: false,

                get question() { return this.cfg.questions[this.idx]; },
                get options() {
                    return ['A', 'B', 'C', 'D'].map(letter => ({
                        letter,
                        text: this.question['option_' + letter.toLowerCase()],
                    }));
                },
                choose(letter) {
                    if (this.chosen !== null || this.done) return;
                    navigator.vibrate?.(50);
                    this.chosen = letter;
                    if (letter === this.question.correct_option) {
                        this.correctCount++;
                        confetti?.({ particleCount: 40, spread: 55, origin: { y: 0.7 } });
                    } else {
                        navigator.vibrate?.([60, 40, 60]);
                    }
                },
                next() {
                    if (this.idx + 1 < this.cfg.questions.length) {
                        this.idx++;
                        this.chosen = null;
                    } else {
                        this.done = true;
                        const earned = Math.round(this.maxPoints * this.correctCount / this.cfg.questions.length);
                        setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned }), 400);
                    }
                },
                optionClass(letter) {
                    if (this.chosen === null) return 'border-gray-200 bg-white text-gray-700 hover:border-emerald-200 hover:bg-emerald-50';
                    if (letter === this.question.correct_option) return 'border-emerald-400 bg-emerald-50 text-emerald-800';
                    if (this.chosen === letter) return 'quiz-shake border-red-300 bg-red-50 text-red-700';
                    return 'border-gray-100 bg-gray-50 text-gray-400';
                },
            };
        }
    </script>
@endonce

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameQuiz(@js($cfg), @js($mission->id), @js($mission->points))" class="space-y-4">
    <div class="flex items-center justify-between">
        <span
            class="rounded-lg border border-amber-100 bg-amber-50 px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-amber-600">
            {{ __('Soal') }} <span x-text="idx + 1"></span>/<span x-text="cfg.questions.length"></span>
        </span>
    </div>

    <p class="font-display text-charcoal text-base font-bold leading-snug" x-text="question.prompt"></p>

    <div class="space-y-3">
        <template x-for="opt in options" :key="idx + '-' + opt.letter">
            <button type="button" @click="choose(opt.letter)"
                class="w-full min-h-11 rounded-xl border-2 p-4 text-left text-sm font-medium transition"
                :class="optionClass(opt.letter)" :disabled="chosen !== null">
                <span class="mr-2 font-bold" x-text="opt.letter + '.'"></span>
                <span x-text="opt.text"></span>
            </button>
        </template>
    </div>

    <template x-if="chosen !== null && question.explanation">
        <div class="rounded-xl p-3 text-sm"
            :class="chosen === question.correct_option ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800'">
            <p x-text="question.explanation"></p>
        </div>
    </template>

    <button type="button" x-show="chosen !== null" @click="next()"
        class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
        <span x-text="idx + 1 < cfg.questions.length ? @js(__('Soal Berikutnya')) : @js(__('Selesai'))"></span>
    </button>
</div>
