{{--
    Decision / scenario branching game.
    config: { scenarios: [{ text, image?, image_after?, options: [{text, correct, explanation?}] }] }
    image/image_after support the Route 3 "visual before/after" variant (Day 4) — optional.
    Scoring: points split evenly per scenario; correct first pick = full share, wrong = 0 for that scenario.
    Emits: mission-complete {id, earned}
--}}

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameDecision(@js($cfg), @js($mission->id), @js($mission->points))" class="space-y-4">
    <div class="flex items-center justify-between">
        <span
            class="rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-blue-600">
            {{ __('Skenario') }} <span x-text="idx + 1"></span>/<span x-text="cfg.scenarios.length"></span>
        </span>
    </div>

    <template x-if="scenario.image">
        <img :src="checked && scenario.options[chosen].correct && scenario.image_after ? scenario.image_after :
            scenario.image"
            class="max-w-full rounded-2xl" alt="" />
    </template>

    <p class="font-display text-charcoal text-base font-bold leading-snug" x-text="scenario.text"></p>

    <div class="space-y-3">
        <template x-for="(opt, oIdx) in scenario.options" :key="idx + '-' + oIdx">
            <button type="button" @click="choose(oIdx)"
                class="w-full min-h-11 rounded-xl border-2 p-4 text-left text-sm font-medium transition"
                :class="optionClass(oIdx)" :disabled="checked">
                <span x-text="opt.text"></span>
            </button>
        </template>
    </div>

    {{-- x-show sits on the sticky wrapper, not the button: leaving the wrapper mounted would park
    an empty backdrop bar across the bottom of the scenario. --}}
    <div x-show="selected !== null && !checked" class="edu-sticky-cta">
        <button type="button" @click="check()"
            class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
            {{ __('Periksa') }}
        </button>
    </div>

    <template x-if="checked && scenario.options[chosen].explanation">
        <div class="rounded-xl p-3 text-sm"
            :class="scenario.options[chosen].correct ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-800'">
            <p x-text="scenario.options[chosen].explanation"></p>
        </div>
    </template>

    <div x-show="checked" class="edu-sticky-cta">
        <button type="button" @click="next()"
            class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
            <span x-text="idx + 1 < cfg.scenarios.length ? @js(__('Skenario Berikutnya')) : @js(__('Selesai'))"></span>
        </button>
    </div>
</div>
