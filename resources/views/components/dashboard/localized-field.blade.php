@props(['id', 'label', 'fieldName' => null, 'help' => null, 'required' => false, 'anchorId' => null])

<div @if($anchorId) id="{{ $anchorId }}" @endif x-data="{ locale: '{{ $fieldName && $errors->has($fieldName.'.en') ? 'en' : 'id' }}', validationMessage: '' }"
    x-init="
        $el.querySelectorAll('[role=tabpanel]').forEach(panel => {
            const language = panel.id.endsWith('-en') ? 'Inggris' : 'Indonesia';
            panel.querySelectorAll('input:not([type=hidden]):not([type=file]), textarea:not(.hidden)').forEach(input => {
                input.setAttribute('aria-label', $el.dataset.fieldLabel + ' bahasa ' + language);
            });
        });
        const fields = [...$el.querySelectorAll('input[required], textarea[required]')]
            .filter(input => /\[(id|en)\]$/.test(input.name));
        fields.forEach(input => input.required = false);
        $el.closest('form')?.addEventListener('submit', event => {
            if (event.defaultPrevented) return;
            const missing = fields.find(input => !String(input.value).trim());
            if (!missing) return;
            event.preventDefault();
            locale = missing.name.endsWith('[en]') ? 'en' : 'id';
            validationMessage = 'Isi ' + $el.dataset.fieldLabel + ' dalam bahasa ' + (locale === 'en' ? 'Inggris.' : 'Indonesia.');
            $nextTick(() => missing.focus());
        });
    "
    @input.capture="validationMessage = ''"
    data-localized-field data-field-label="{{ $label }}"
    {{ $attributes->merge(['class' => 'space-y-3']) }}>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-charcoal">{{ $label }} @if($required)<span class="text-red-700" aria-hidden="true">*</span>@endif</h3>
            @if($help)<p class="mt-1 text-xs leading-relaxed text-gray-500">{{ $help }}</p>@endif
        </div>
        <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-surface p-1" role="tablist" aria-label="Bahasa {{ $label }}">
            <button type="button" role="tab" id="{{ $id }}-tab-id" aria-controls="{{ $id }}-panel-id"
                :aria-selected="locale === 'id'" @click="locale = 'id'"
                :class="locale === 'id' ? 'bg-primary text-white shadow-sm' : 'text-gray-600 hover:text-charcoal'"
                class="min-h-11 rounded-lg px-4 text-xs font-bold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Indonesia</button>
            <button type="button" role="tab" id="{{ $id }}-tab-en" aria-controls="{{ $id }}-panel-en"
                :aria-selected="locale === 'en'" @click="locale = 'en'"
                :class="locale === 'en' ? 'bg-primary text-white shadow-sm' : 'text-gray-600 hover:text-charcoal'"
                class="min-h-11 rounded-lg px-4 text-xs font-bold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Inggris</button>
        </div>
    </div>
    <div id="{{ $id }}-panel-id" role="tabpanel" aria-labelledby="{{ $id }}-tab-id" x-show="locale === 'id'" class="[&_label]:sr-only">
        {{ $indonesian }}
    </div>
    <div id="{{ $id }}-panel-en" role="tabpanel" aria-labelledby="{{ $id }}-tab-en" x-show="locale === 'en'" x-cloak class="[&_label]:sr-only">
        {{ $english }}
    </div>
    <p x-show="validationMessage" x-text="validationMessage" class="text-xs font-semibold text-red-700" role="alert" x-cloak></p>
</div>
