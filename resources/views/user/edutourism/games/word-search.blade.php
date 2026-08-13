{{--
    Word search. Grid generated client-side (H/V/diagonal placement + random fill).
    config: { prompt?, words: ["BAMBU", ...], grid_size?: int, explanation?: {en,id} }
    Selection is tap-based (outdoor/mobile friendly): tap the first letter, then the last letter
    of a word — the straight line between them is checked (both directions).
    Scoring: full mission points when all words are found (no fail state).
    Emits: mission-complete {id, earned}
--}}

@php($cfg = $mission->localizedConfig())
<div x-data="eduGameWordSearch(@js($cfg), @js($mission->id), @js($mission->points))" class="space-y-4">
    @if (!empty($cfg['prompt']))
        <p class="text-sm leading-relaxed text-gray-600">{{ $cfg['prompt'] }}</p>
    @endif

    <div class="flex flex-wrap gap-2">
        <template x-for="w in cfg.words" :key="w">
            <span class="rounded-lg border px-2 py-1 text-xs font-bold uppercase tracking-wide"
                :class="foundWords.includes(w) ? 'border-emerald-200 bg-emerald-50 text-emerald-600 line-through' :
                    'border-gray-200 bg-gray-50 text-gray-500'"
                x-text="w"></span>
        </template>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-gray-50 p-2">
        <div class="grid gap-1" :style="`grid-template-columns: repeat(${size}, minmax(0, 1fr));`">
            <template x-for="(row, r) in grid" :key="r">
                <template x-for="(letter, c) in row" :key="r + '-' + c">
                    <button type="button" @click="tap(r, c)"
                        class="flex aspect-square min-w-8 items-center justify-center rounded-md text-sm font-bold shadow-sm transition"
                        :class="cellClass(r, c)" x-text="letter"></button>
                </template>
            </template>
        </div>
    </div>

    <template x-if="done && cfg.explanation">
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-3 text-sm text-emerald-800">
            <p>{{ $cfg['explanation'] }}</p>
        </div>
    </template>

    {{-- x-show sits on the sticky wrapper, not the button: leaving the wrapper mounted would park
    an empty backdrop bar across the bottom of the grid. --}}
    <div x-show="done" class="edu-sticky-cta">
        <button type="button" @click="finish()"
            class="bg-primary w-full rounded-xl py-3 text-sm font-bold text-white shadow-sm transition-transform active:scale-95">
            {{ __('Lanjut') }}
        </button>
    </div>

    <p x-show="!done" class="text-center text-xs text-gray-400">{{ __('Ketuk huruf pertama lalu huruf terakhir sebuah kata.') }}</p>
</div>
