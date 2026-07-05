{{--
    Matching game — 2 modes via config.mode:
    - "match": pair left items with right targets (tap item, then tap target).
      config: { prompt?, pairs: [{left, right, icon?, audio?}], penalty? }
    - "pick": scavenger hunt with decoys — pick N correct cards out of M.
      config: { prompt?, items: [{label, icon?, image?, correct}], pick_count, penalty? }
    Scoring: match → points - penalty*mistakes (min 20% of points);
             pick  → (correct - wrong*penalty_ratio) / pick_count * points, min 0.
    Emits: mission-complete {id, earned}
--}}
@once
    <script>
        function eduGameMatching(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                mode: cfg.mode || 'match',
                // match mode state
                lefts: [], rights: [], selectedLeft: null, matched: [], mistakes: 0, wrongPair: null,
                // pick mode state
                picked: [], wrongPicks: 0, pickDone: false,
                done: false,

                init() {
                    if (this.mode === 'match') {
                        this.lefts = this.cfg.pairs.map((p, i) => ({ ...p, i }));
                        this.rights = this.cfg.pairs.map((p, i) => ({ ...p, i })).sort(() => Math.random() - 0.5);
                    }
                },

                // ---- match mode ----
                pickLeft(i) {
                    if (this.matched.includes(i) || this.done) return;
                    navigator.vibrate?.(50);
                    this.selectedLeft = i;
                },
                pickRight(i) {
                    if (this.selectedLeft === null || this.matched.includes(i) || this.done) return;
                    if (i === this.selectedLeft) {
                        navigator.vibrate?.(50);
                        this.matched.push(i);
                        this.selectedLeft = null;
                        if (this.matched.length === this.cfg.pairs.length) this.finishMatch();
                    } else {
                        navigator.vibrate?.([60, 40, 60]);
                        this.mistakes++;
                        this.wrongPair = i;
                        setTimeout(() => this.wrongPair = null, 500);
                    }
                },
                finishMatch() {
                    const penalty = this.cfg.penalty ?? 10;
                    const earned = Math.max(Math.round(this.maxPoints * 0.2), this.maxPoints - penalty * this.mistakes);
                    this.complete(earned);
                },

                // ---- pick mode ----
                togglePick(idx) {
                    if (this.pickDone || this.done) return;
                    navigator.vibrate?.(50);
                    const pos = this.picked.indexOf(idx);
                    if (pos >= 0) this.picked.splice(pos, 1);
                    else if (this.picked.length < (this.cfg.pick_count || this.cfg.items.filter(t => t.correct).length)) this.picked.push(idx);
                },
                submitPick() {
                    if (this.pickDone) return;
                    this.pickDone = true;
                    const correct = this.picked.filter(i => this.cfg.items[i].correct).length;
                    this.wrongPicks = this.picked.length - correct;
                    const target = this.cfg.pick_count || this.cfg.items.filter(t => t.correct).length;
                    const perItem = this.maxPoints / target;
                    const penaltyRatio = this.cfg.penalty_ratio ?? 0.5;
                    const earned = Math.max(0, Math.round(correct * perItem - this.wrongPicks * perItem * penaltyRatio));
                    setTimeout(() => this.complete(earned), 1600);
                },
                pickState(idx) {
                    if (!this.pickDone) return this.picked.includes(idx) ? 'selected' : 'idle';
                    if (this.picked.includes(idx)) return this.cfg.items[idx].correct ? 'correct' : 'wrong';
                    return this.cfg.items[idx].correct ? 'missed' : 'idle';
                },

                complete(earned) {
                    if (this.done) return;
                    this.done = true;
                    confetti?.({ particleCount: 70, spread: 65, origin: { y: 0.7 } });
                    setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned }), 900);
                },
            };
        }
    </script>
@endonce

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameMatching(@js($cfg), @js($mission->id), @js($mission->points))" class="space-y-4">
    @if (!empty($cfg['prompt']))
        <p class="text-sm leading-relaxed text-gray-600">{{ $cfg['prompt'] }}</p>
    @endif

    @if (($cfg['mode'] ?? 'match') === 'match')
        <div class="grid grid-cols-2 gap-3">
            <div class="space-y-2">
                <template x-for="item in lefts" :key="'l' + item.i">
                    <button type="button" @click="pickLeft(item.i)"
                        class="w-full min-h-[44px] rounded-xl border-2 p-3 text-left text-sm font-semibold transition"
                        :class="matched.includes(item.i) ? 'border-emerald-300 bg-emerald-50 text-emerald-700' :
                            (selectedLeft === item.i ? 'border-primary bg-primary text-white' : 'border-gray-200 bg-white text-gray-700')"
                        :disabled="matched.includes(item.i)">
                        <template x-if="item.image">
                            <img :src="item.image" alt="" class="mx-auto block h-12 w-12 rounded object-cover mb-1">
                        </template>
                        <span x-text="item.left" :class="item.image ? 'block text-center text-xs' : ''"></span>
                    </button>
                </template>
            </div>
            <div class="space-y-2">
                <template x-for="item in rights" :key="'r' + item.i">
                    <button type="button" @click="pickRight(item.i)"
                        class="w-full min-h-[44px] rounded-xl border-2 p-3 text-left text-sm font-medium transition"
                        :class="matched.includes(item.i) ? 'border-emerald-300 bg-emerald-50 text-emerald-700' :
                            (wrongPair === item.i ? 'quiz-shake border-red-300 bg-red-50 text-red-700' : 'border-gray-200 bg-white text-gray-700')"
                        :disabled="matched.includes(item.i)">
                        <span x-text="item.right"></span>
                    </button>
                </template>
            </div>
        </div>
        <p class="text-center text-xs text-gray-400">
            {{ __('Ketuk item di kiri, lalu ketuk pasangannya di kanan.') }}
        </p>
    @else
        <div class="grid grid-cols-2 gap-3">
            <template x-for="(item, idx) in cfg.items" :key="idx">
                <button type="button" @click="togglePick(idx)"
                    class="min-h-[64px] rounded-xl border-2 p-3 text-sm font-semibold transition"
                    :class="{
                        'border-gray-200 bg-white text-gray-700': pickState(idx) === 'idle',
                        'border-primary bg-green-50 text-primary': pickState(idx) === 'selected',
                        'border-emerald-400 bg-emerald-50 text-emerald-700': pickState(idx) === 'correct',
                        'quiz-shake border-red-400 bg-red-50 text-red-700': pickState(idx) === 'wrong',
                        'border-amber-300 bg-amber-50 text-amber-700': pickState(idx) === 'missed',
                    }">
                    <template x-if="item.image">
                        <img :src="item.image" alt="" class="mx-auto block h-12 w-12 rounded object-cover mb-1">
                    </template>
                    <span x-text="item.label" :class="item.image ? 'block text-center text-xs' : ''"></span>
                </button>
            </template>
        </div>
        <button type="button" @click="submitPick()" :disabled="picked.length === 0 || pickDone"
            class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="!pickDone">{{ __('Periksa Pilihan') }} (<span x-text="picked.length"></span>/<span
                    x-text="cfg.pick_count"></span>)</span>
            <span x-show="pickDone">{{ __('Memeriksa...') }}</span>
        </button>
    @endif
</div>
