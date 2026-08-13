{{--
    Multiple-choice quiz (Point 1 "Unlock the Village").
    config: { questions: [{ prompt, option_a, option_b, option_c, option_d, correct_option: 'A'|'B'|'C'|'D', explanation? }] }
    Scoring: points split evenly per question; correct pick = full share, wrong = 0 for that question.
    Emits: mission-complete {id, earned}
--}}

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
                :class="optionClass(opt.letter)" :disabled="checked">
                <span class="mr-2 font-bold" x-text="opt.letter + '.'"></span>
                <span x-text="opt.text"></span>
            </button>
        </template>
    </div>

    {{-- x-show sits on the sticky wrapper, not the button: leaving the wrapper mounted would park
    an empty backdrop bar across the bottom of the question. --}}
    <div x-show="selected !== null && !checked" class="edu-sticky-cta">
        <button type="button" @click="check()"
            class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
            {{ __('Periksa') }}
        </button>
    </div>

    <template x-if="checked && question.explanation">
        <div class="rounded-xl p-3 text-sm"
            :class="chosen === question.correct_option ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-800'">
            <p x-text="question.explanation"></p>
        </div>
    </template>

    <div x-show="checked" class="edu-sticky-cta">
        <button type="button" @click="next()"
            class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
            <span x-text="idx + 1 < cfg.questions.length ? @js(__('Soal Berikutnya')) : @js(__('Selesai'))"></span>
        </button>
    </div>
</div>
