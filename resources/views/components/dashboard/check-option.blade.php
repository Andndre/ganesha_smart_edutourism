@props(['name', 'value', 'checked' => false, 'label', 'caption' => null])

<label {{ $attributes->merge(['class' => 'flex min-h-12 cursor-pointer items-center gap-3 rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm transition hover:border-primary/40 hover:bg-primary/5']) }}>
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked($checked)
        class="h-4 w-4 shrink-0 rounded border-gray-300 text-primary focus:ring-primary">
    <span class="min-w-0 flex-1 font-semibold text-charcoal">{{ $label }}</span>
    @if($caption)<span class="text-xs text-gray-500">{{ $caption }}</span>@endif
</label>
