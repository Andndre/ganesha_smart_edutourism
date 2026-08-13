{{--
    Riddle reveal.
    config: { riddle, answers: ["merajan", "sanggah"], hint?, success_text?, explanation? }
    Matching: case-insensitive, punctuation-stripped, Levenshtein distance ≤ 1 (light typo tolerance).
    Scoring: points - 20*(wrong attempts), min 20% of points.
    Emits: mission-complete {id, earned}
--}}

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

    <template x-if="!checked">
        <div class="space-y-3">
            <input type="text" x-model="guess" @keydown.enter="submit()"
                :class="wrong ? 'quiz-shake border-red-300' : 'border-gray-200'"
                class="focus:border-primary w-full rounded-xl border-2 p-3 text-sm font-medium text-gray-800 outline-none transition"
                placeholder="{{ __('Ketik jawabanmu...') }}" autocomplete="off" />
            <div class="edu-sticky-cta">
                <button type="button" @click="submit()"
                    class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
                    {{ __('Jawab Teka-Teki') }}
                </button>
            </div>
        </div>
    </template>

    <template x-if="checked">
        <div class="space-y-3">
            <div class="rounded-2xl border p-6 text-center"
                :class="solved ? 'border-emerald-100 bg-emerald-50' : 'border-red-100 bg-red-50'">
                <div
                    class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full"
                    :class="solved ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'">
                    <svg x-show="solved" class="quiz-success-check h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg x-show="!solved" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <p x-show="solved" class="text-lg font-black capitalize" :class="solved ? 'text-emerald-700' : 'text-red-700'" x-text="answerShown"></p>
                <p x-show="solved" class="mt-1 text-sm text-emerald-600">{{ $cfg['success_text'] ?? __('Tepat sekali!') }}</p>
                <p x-show="!solved" class="text-lg font-black text-red-700">{{ __('Belum tepat') }}</p>
                <p x-show="!solved" class="mt-1 text-sm text-red-600">{{ __('Jawaban yang benar adalah:') }} <span class="font-semibold">{{ $cfg['answers'][0] ?? '' }}</span></p>
            </div>

            @if (!empty($cfg['explanation']))
                <div class="rounded-xl p-3 text-sm"
                    :class="solved ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-800'">
                    <p>{{ $cfg['explanation'] }}</p>
                </div>
            @endif

            <div class="edu-sticky-cta">
                <button type="button" @click="continueMission()"
                    class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
                    {{ __('Lanjut') }}
                </button>
            </div>
        </div>
    </template>
</div>
