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
                checked: false,
                done: false,
                timedOut: false,
                timeLeft: null,
                timerInterval: null,
                earned: 0,
                // drag state
                dragging: null,
                dragY: 0,
                dragHeight: 0,
                dragStartY: 0,

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
                stopTimer() {
                    if (this.timerInterval) {
                        clearInterval(this.timerInterval);
                        this.timerInterval = null;
                    }
                },
                timeUp() {
                    if (this.done) return;
                    this.done = true;
                    this.timedOut = true;
                    navigator.vibrate?.([60, 40, 60]);
                    this.earned = Math.round(this.maxPoints * 0.2);
                    setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned: this.earned }), 900);
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

                // ---- pointer-based drag-and-drop ----
                dragStart(pos, e) {
                    if (this.done || this.checked || this.dragging !== null) return;
                    const el = e.currentTarget.closest('.sq-item');
                    if (!el) return;
                    this.dragging = pos;
                    this.dragY = 0;
                    this.dragStartY = e.clientY;
                    this.dragHeight = el.offsetHeight + 8; // 8px ≈ gap-2
                    el.setPointerCapture(e.pointerId);
                    navigator.vibrate?.(30);
                },
                dragMove(pos, e) {
                    if (this.dragging !== pos) return;
                    e.preventDefault();
                    this.dragY = e.clientY - this.dragStartY;
                    const offset = Math.round(this.dragY / this.dragHeight);
                    const target = Math.max(0, Math.min(this.items.length - 1, this.dragging + offset));
                    if (target !== this.dragging) {
                        const moved = (target - this.dragging) * this.dragHeight;
                        // swap in place
                        const tmp = this.items[this.dragging];
                        this.items[this.dragging] = this.items[target];
                        this.items[target] = tmp;
                        this.dragging = target;
                        this.dragStartY += moved;
                        this.dragY -= moved;
                    }
                },
                dragEnd(pos, e) {
                    if (this.dragging !== pos) return;
                    this.dragging = null;
                    this.dragY = 0;
                    const el = e.currentTarget.closest('.sq-item');
                    if (el && e.pointerId) {
                        try { el.releasePointerCapture(e.pointerId); } catch (_) {}
                    }
                },

                check() {
                    if (this.done || this.checked) return;
                    Swal.fire({
                        title: @js(__('Apakah anda yakin?')),
                        text: @js(__('Urutan yang sudah dipilih akan diperiksa.')),
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#1E5128',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: @js(__('Periksa')),
                        cancelButtonText: @js(__('Batal')),
                    }).then(r => {
                        if (!r.isConfirmed) return;
                        this.attempts++;
                        this.wrongIdx = this.items.map((it, pos) => it.i !== pos ? pos : null).filter(v => v !== null);
                        this.checked = true;
                        this.done = true;
                        this.stopTimer();
                        if (this.wrongIdx.length === 0) {
                            confetti?.({ particleCount: 70, spread: 65, origin: { y: 0.7 } });
                        } else {
                            navigator.vibrate?.([60, 40, 60]);
                        }
                        this.earned = Math.max(Math.round(this.maxPoints * 0.2), this.maxPoints - 20 * (this.attempts - 1));
                    });
                },
                finish() {
                    setTimeout(() => this.$dispatch('mission-complete', { id: this.missionId, earned: this.earned }), 400);
                },
            };
        }
    </script>
@endonce

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameSequence(@js($cfg), @js($mission->id), @js($mission->points))"
    @close-mission-runner.window="stopTimer()" class="space-y-4">
    @if (!empty($cfg['prompt']))
        <p class="text-sm leading-relaxed text-gray-600">{{ $cfg['prompt'] }}</p>
    @endif

    {{-- Phase 1 (optional): reveal hidden facts --}}
    <template x-if="phase === 'reveal'">
        <div class="space-y-3">
            <div class="grid grid-cols-1 gap-2">
                <template x-for="(item, idx) in cfg.items" :key="idx">
                    <button type="button" @click="reveal(idx)"
                        class="min-h-14 rounded-xl border-2 p-3 text-left text-sm transition"
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
                    <div class="sq-item flex items-center gap-2 rounded-xl border-2 bg-white p-2 select-none"
                        :class="done ? (wrongIdx.includes(pos) ? 'border-red-300 bg-red-50' : 'border-emerald-300 bg-emerald-50') : 'border-gray-200'
                            + (dragging === pos ? ' z-10 shadow-lg' : '')"
                        :style="dragging === pos ? 'transform: translateY(' + dragY + 'px); touch-action: none;' : 'touch-action: none;'"
                        @pointerdown="dragStart(pos, $event)"
                        @pointermove="dragMove(pos, $event)"
                        @pointerup="dragEnd(pos, $event)"
                        @pointercancel="dragEnd(pos, $event)">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-black text-gray-500"
                            x-text="pos + 1"></span>
                        <p class="flex-1 text-sm font-medium text-gray-700" x-text="item.text"></p>
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
                        </svg>
                    </div>
                </template>
            </div>

            <button type="button" x-show="!checked && !done" @click="check()"
                class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
                {{ __('Periksa Urutan') }}
            </button>

            <template x-if="done && !timedOut">
                <div class="space-y-3">
                    <div class="rounded-xl p-3 text-sm"
                        :class="wrongIdx.length === 0 ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-800'">
                        <p x-text="wrongIdx.length === 0 ? @js(__('Urutan Benar!')) : @js(__('Urutan belum tepat, coba perhatikan kembali.'))"></p>
                        @if (!empty($cfg['explanation']))
                            <p class="mt-1">{{ $cfg['explanation'] }}</p>
                        @endif
                    </div>
                    <button type="button" @click="finish()"
                        class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
                        {{ __('Lanjut') }}
                    </button>
                </div>
            </template>
        </div>
    </template>
</div>
