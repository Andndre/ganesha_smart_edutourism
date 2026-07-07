{{--
    Decision / scenario branching game.
    config: { scenarios: [{ text, image?, image_after?, options: [{text, correct, explanation?}] }] }
    image/image_after support the Route 3 "visual before/after" variant (Day 4) — optional.
    Scoring: points split evenly per scenario; correct first pick = full share, wrong = 0 for that scenario.
    Emits: mission-complete {id, earned}
--}}
@once
    <script>
        function eduGameDecision(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                idx: 0, chosen: null, correctCount: 0, done: false,

                get scenario() { return this.cfg.scenarios[this.idx]; },
                choose(oIdx) {
                    if (this.chosen !== null || this.done) return;
                    navigator.vibrate?.(50);
                    this.chosen = oIdx;
                    if (this.scenario.options[oIdx].correct) {
                        this.correctCount++;
                        confetti?.({ particleCount: 40, spread: 55, origin: { y: 0.7 } });
                    } else {
                        navigator.vibrate?.([60, 40, 60]);
                    }
                },
                next() {
                    if (this.idx + 1 < this.cfg.scenarios.length) {
                        this.idx++;
                        this.chosen = null;
                    } else {
                        this.done = true;
                        const earned = Math.round(this.maxPoints * this.correctCount / this.cfg.scenarios.length);
                        setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned }), 400);
                    }
                },
                optionClass(oIdx) {
                    if (this.chosen === null) return 'border-gray-200 bg-white text-gray-700 hover:border-emerald-200 hover:bg-emerald-50';
                    if (this.scenario.options[oIdx].correct) return 'border-emerald-400 bg-emerald-50 text-emerald-800';
                    if (this.chosen === oIdx) return 'quiz-shake border-red-300 bg-red-50 text-red-700';
                    return 'border-gray-100 bg-gray-50 text-gray-400';
                },
            };
        }
    </script>
@endonce

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameDecision(@js($cfg), @js($mission->id), @js($mission->points))" class="space-y-4">
    <div class="flex items-center justify-between">
        <span
            class="rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-blue-600">
            {{ __('Skenario') }} <span x-text="idx + 1"></span>/<span x-text="cfg.scenarios.length"></span>
        </span>
    </div>

    <template x-if="scenario.image">
        <img :src="chosen !== null && scenario.options[chosen].correct && scenario.image_after ? scenario.image_after :
            scenario.image"
            class="max-w-full rounded-2xl" alt="" />
    </template>

    <p class="font-display text-charcoal text-base font-bold leading-snug" x-text="scenario.text"></p>

    <div class="space-y-3">
        <template x-for="(opt, oIdx) in scenario.options" :key="idx + '-' + oIdx">
            <button type="button" @click="choose(oIdx)"
                class="w-full min-h-11 rounded-xl border-2 p-4 text-left text-sm font-medium transition"
                :class="optionClass(oIdx)" :disabled="chosen !== null">
                <span x-text="opt.text"></span>
            </button>
        </template>
    </div>

    <template x-if="chosen !== null && scenario.options[chosen].explanation">
        <div class="rounded-xl p-3 text-sm"
            :class="scenario.options[chosen].correct ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800'">
            <p x-text="scenario.options[chosen].explanation"></p>
        </div>
    </template>

    <button type="button" x-show="chosen !== null" @click="next()"
        class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
        <span x-text="idx + 1 < cfg.scenarios.length ? @js(__('Skenario Berikutnya')) : @js(__('Selesai'))"></span>
    </button>
</div>
