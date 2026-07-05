{{--
    Sequence / timeline puzzle.
    config: { prompt?, reveal_first?: bool, items: [{text}] } — items listed in CORRECT order, shuffled on load.
    reveal_first: items start face-down ("find the hidden facts"); tap each to reveal, then order them.
    time_limit_seconds: optional countdown for the order phase (e.g. Route 2 "Escape the Timeline");
    starts when the order phase begins, auto-submits once it hits 0.
    Scoring: points - 20*(extra attempts), min 20% of points.
    Emits: mission-complete {id, earned}
--}}
@once
    <script>
        function eduGameSequence(cfg, missionId, maxPoints) {
            return {
                cfg, missionId, maxPoints,
                phase: cfg.reveal_first ? 'reveal' : 'order',
                revealed: [],
                items: [],
                attempts: 0,
                wrongIdx: [],
                done: false,
                timedOut: false,
                timeLeft: null,
                timerInterval: null,

                init() {
                    this.items = this.cfg.items.map((it, i) => ({ ...it, i }));
                    do {
                        this.items.sort(() => Math.random() - 0.5);
                    } while (this.items.length > 1 && this.items.every((it, pos) => it.i === pos));

                    if (this.phase === 'order') this.startTimer();
                },
                startTimer() {
                    if (!this.cfg.time_limit_seconds || this.timerInterval) return;
                    this.timeLeft = this.cfg.time_limit_seconds;
                    this.timerInterval = setInterval(() => {
                        this.timeLeft--;
                        if (this.timeLeft <= 0) {
                            clearInterval(this.timerInterval);
                            if (!this.done) this.timeUp();
                        }
                    }, 1000);
                },
                timeUp() {
                    if (this.done) return;
                    this.done = true;
                    this.timedOut = true;
                    navigator.vibrate?.([60, 40, 60]);
                    const earned = Math.round(this.maxPoints * 0.2);
                    setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned }), 900);
                },
                get timeLabel() {
                    const m = Math.floor(this.timeLeft / 60);
                    const s = this.timeLeft % 60;
                    return `${m}:${String(s).padStart(2, '0')}`;
                },
                reveal(i) {
                    if (!this.revealed.includes(i)) {
                        navigator.vibrate?.(50);
                        this.revealed.push(i);
                    }
                },
                move(pos, dir) {
                    const target = pos + dir;
                    if (target < 0 || target >= this.items.length || this.done) return;
                    navigator.vibrate?.(50);
                    const tmp = this.items[pos];
                    this.items[pos] = this.items[target];
                    this.items[target] = tmp;
                    this.wrongIdx = [];
                },
                check() {
                    if (this.done) return;
                    this.attempts++;
                    this.wrongIdx = this.items.map((it, pos) => it.i !== pos ? pos : null).filter(v => v !== null);
                    if (this.wrongIdx.length === 0) {
                        this.done = true;
                        if (this.timerInterval) clearInterval(this.timerInterval);
                        confetti?.({ particleCount: 70, spread: 65, origin: { y: 0.7 } });
                        const earned = Math.max(Math.round(this.maxPoints * 0.2), this.maxPoints - 20 * (this.attempts - 1));
                        setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned }), 900);
                    } else {
                        navigator.vibrate?.([60, 40, 60]);
                    }
                },
            };
        }
    </script>
@endonce

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameSequence(@js($cfg), @js($mission->id), @js($mission->points))" class="space-y-4">
    @if (!empty($cfg['prompt']))
        <p class="text-sm leading-relaxed text-gray-600">{{ $cfg['prompt'] }}</p>
    @endif

    {{-- Phase 1 (optional): reveal hidden facts --}}
    <template x-if="phase === 'reveal'">
        <div class="space-y-3">
            <div class="grid grid-cols-1 gap-2">
                <template x-for="(item, idx) in cfg.items" :key="idx">
                    <button type="button" @click="reveal(idx)"
                        class="min-h-[56px] rounded-xl border-2 p-3 text-left text-sm transition"
                        :class="revealed.includes(idx) ? 'border-emerald-200 bg-emerald-50 text-gray-700' :
                            'border-dashed border-gray-300 bg-gray-50 text-gray-400'">
                        <span x-show="!revealed.includes(idx)" class="font-semibold">🔍
                            {{ __('Ketuk untuk mengungkap fakta') }} #<span x-text="idx + 1"></span></span>
                        <span x-show="revealed.includes(idx)" x-text="item.text" class="font-medium"></span>
                    </button>
                </template>
            </div>
            <button type="button" @click="phase = 'order'; startTimer()" :disabled="revealed.length < cfg.items.length"
                class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95 disabled:cursor-not-allowed disabled:opacity-50">
                {{ __('Semua Ditemukan — Susun Kronologi') }} (<span x-text="revealed.length"></span>/<span
                    x-text="cfg.items.length"></span>)
            </button>
        </div>
    </template>

    {{-- Phase 2: order the items --}}
    <template x-if="phase === 'order'">
        <div class="space-y-3">
            <template x-if="timeLeft !== null">
                <p class="text-center text-sm font-bold" :class="timeLeft <= 30 ? 'text-red-500' : 'text-gray-500'">
                    ⏱ <span x-text="timeLabel"></span>
                </p>
            </template>
            <div class="space-y-2">
                <template x-for="(item, pos) in items" :key="item.i">
                    <div class="flex items-center gap-2 rounded-xl border-2 bg-white p-2"
                        :class="done ? 'border-emerald-300 bg-emerald-50' : (wrongIdx.includes(pos) ?
                            'quiz-shake border-red-300' : 'border-gray-200')">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-black text-gray-500"
                            x-text="pos + 1"></span>
                        <p class="flex-1 text-sm font-medium text-gray-700" x-text="item.text"></p>
                        <div class="flex flex-col gap-1">
                            <button type="button" @click="move(pos, -1)" :disabled="pos === 0 || done"
                                class="flex h-8 w-11 items-center justify-center rounded-lg bg-gray-100 text-gray-600 disabled:opacity-30">▲</button>
                            <button type="button" @click="move(pos, 1)" :disabled="pos === items.length - 1 || done"
                                class="flex h-8 w-11 items-center justify-center rounded-lg bg-gray-100 text-gray-600 disabled:opacity-30">▼</button>
                        </div>
                    </div>
                </template>
            </div>
            <button type="button" @click="check()" :disabled="done"
                class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95 disabled:opacity-50">
                <span x-show="!done">{{ __('Periksa Urutan') }}</span>
                <span x-show="done && !timedOut">✓ {{ __('Urutan Benar!') }}</span>
                <span x-show="done && timedOut">⏱ {{ __('Waktu Habis!') }}</span>
            </button>
        </div>
    </template>
</div>
