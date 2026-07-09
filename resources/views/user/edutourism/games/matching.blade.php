{{--
    Matching game — 2 modes via config.mode:
    - "match": pair left items with right targets (tap item, then tap target).
      config: { prompt?, pairs: [{left, right, image?, audio?}], penalty? }
    - "pick": scavenger hunt with decoys — pick N correct cards out of M.
      config: { prompt?, items: [{label, icon?, image?, correct, explanation?}], pick_count, penalty? }
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
                picked: [], wrongPicks: 0, pickDone: false, earned: 0,
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
                    this.earned = Math.max(Math.round(this.maxPoints * 0.2), this.maxPoints - penalty * this.mistakes);
                    this.complete(this.earned);
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
                    if (this.pickDone || this.picked.length === 0) return;
                    Swal.fire({
                        title: @js(__('Apakah anda yakin?')),
                        text: @js(__('Pilihan yang sudah dipilih akan diperiksa.')),
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#1E5128',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: @js(__('Periksa Pilihan')),
                        cancelButtonText: @js(__('Batal')),
                    }).then(r => {
                        if (!r.isConfirmed) return;
                        this.pickDone = true;
                        const correct = this.picked.filter(i => this.cfg.items[i].correct).length;
                        this.wrongPicks = this.picked.length - correct;
                        const target = this.cfg.pick_count || this.cfg.items.filter(t => t.correct).length;
                        const perItem = this.maxPoints / target;
                        const penaltyRatio = this.cfg.penalty_ratio ?? 0.5;
                        this.earned = Math.max(0, Math.round(correct * perItem - this.wrongPicks * perItem * penaltyRatio));
                    });
                },
                pickState(idx) {
                    if (!this.pickDone) return this.picked.includes(idx) ? 'selected' : 'idle';
                    if (this.picked.includes(idx)) return this.cfg.items[idx].correct ? 'correct' : 'wrong';
                    return this.cfg.items[idx].correct ? 'missed' : 'idle';
                },
                pickResultClass(idx) {
                    const state = this.pickState(idx);
                    if (state === 'correct') return 'border-emerald-400 bg-emerald-50 text-emerald-700';
                    if (state === 'wrong') return 'border-red-400 bg-red-50 text-red-700';
                    if (state === 'missed') return 'border-amber-300 bg-amber-50 text-amber-700';
                    return 'border-gray-200 bg-white text-gray-700';
                },
                finish() {
                    setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned: this.earned }), 400);
                },

                complete(earned) {
                    if (this.done) return;
                    this.done = true;
                    this.earned = earned;
                    confetti?.({ particleCount: 70, spread: 65, origin: { y: 0.7 } });
                },

                playAudio(path) {
                    new Audio('/audio-stream/' + encodeURI(path)).play().catch(() => {});
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
                        class="w-full min-h-11 rounded-xl border-2 p-3 text-left text-sm font-semibold transition"
                        :class="matched.includes(item.i) ? 'border-emerald-300 bg-emerald-50 text-emerald-700' :
                            (selectedLeft === item.i ? 'border-primary bg-primary text-white' : 'border-gray-200 bg-white text-gray-700')"
                        :disabled="matched.includes(item.i)">
                        <template x-if="item.image">
                            <img :src="item.image" alt="" class="mx-auto block h-12 w-12 rounded object-cover mb-1" x-on:error="$event.target.style.display='none'">
                        </template>
                        <span class="inline-flex items-center gap-1.5">
                            <span x-text="item.left" :class="item.image ? 'block text-center text-xs' : ''"></span>
                            <template x-if="item.audio">
                                <button type="button" @click.stop="playAudio(item.audio)"
                                    class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </button>
                            </template>
                        </span>
                    </button>
                </template>
            </div>
            <div class="space-y-2">
                <template x-for="item in rights" :key="'r' + item.i">
                    <button type="button" @click="pickRight(item.i)"
                        class="w-full min-h-11 rounded-xl border-2 p-3 text-left text-sm font-medium transition"
                        :class="matched.includes(item.i) ? 'border-emerald-300 bg-emerald-50 text-emerald-700' :
                            (wrongPair === item.i ? 'quiz-shake border-red-300 bg-red-50 text-red-700' : 'border-gray-200 bg-white text-gray-700')"
                        :disabled="matched.includes(item.i)">
                        <span x-text="item.right"></span>
                    </button>
                </template>
            </div>
        </div>
        <p x-show="!done" class="text-center text-xs text-gray-400">
            {{ __('Ketuk item di kiri, lalu ketuk pasangannya di kanan.') }}
        </p>

        <template x-if="done">
            <div class="space-y-2">
                <template x-for="item in cfg.pairs" :key="'exp-' + item.i">
                    <div x-show="item.explanation" class="rounded-xl border border-emerald-100 bg-emerald-50 p-3 text-sm text-emerald-800">
                        <p class="font-semibold" x-text="item.left + ' ↔ ' + item.right"></p>
                        <p x-text="item.explanation"></p>
                    </div>
                </template>
                <button type="button" @click="finish()"
                    class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
                    {{ __('Lanjut') }}
                </button>
            </div>
        </template>
    @else
        <div class="grid grid-cols-2 gap-3">
            <template x-for="(item, idx) in cfg.items" :key="idx">
                <div>
                    <button type="button" @click="togglePick(idx)"
                        class="min-h-16 w-full rounded-xl border-2 p-3 text-sm font-semibold transition"
                        :class="pickDone ? pickResultClass(idx) : (pickState(idx) === 'selected' ? 'border-primary bg-green-50 text-primary' : 'border-gray-200 bg-white text-gray-700')"
                        :disabled="pickDone">
                        <template x-if="item.image">
                            <img :src="item.image" alt="" class="mx-auto block h-12 w-12 rounded object-cover mb-1" x-on:error="$event.target.style.display='none'">
                        </template>
                        <span x-text="item.label" :class="item.image ? 'block text-center text-xs' : ''"></span>
                    </button>
                    <template x-if="pickDone && item.explanation">
                        <p class="mt-1 rounded-lg px-2 py-1 text-xs leading-snug"
                            :class="pickState(idx) === 'wrong' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'"
                            x-text="item.explanation"></p>
                    </template>
                </div>
            </template>
        </div>
        <button type="button" x-show="!pickDone" @click="submitPick()" :disabled="picked.length === 0"
            class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95 disabled:cursor-not-allowed disabled:opacity-50">
            <span>{{ __('Periksa Pilihan') }} (<span x-text="picked.length"></span>/<span
                    x-text="cfg.pick_count"></span>)</span>
        </button>
        <button type="button" x-show="pickDone" @click="finish()"
            class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
            {{ __('Lanjut') }}
        </button>
    @endif
</div>
