@props(['name', 'label', 'value' => '', 'type' => 'text', 'id' => null, 'help' => null, 'required' => false])

@php($fieldId = $id ?? str_replace(['[', ']', '_'], ['-', '', '-'], $name))

<div>
    <label for="{{ $fieldId }}" class="mb-1.5 block text-sm font-semibold text-charcoal">
        {{ $label }} @if($required)<span class="text-red-700" aria-hidden="true">*</span>@endif
    </label>
    <input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'min-h-11 w-full rounded-xl border border-gray-200 bg-white px-3.5 text-sm text-charcoal outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15']) }}>
    @if($help)<p class="mt-1.5 text-xs leading-relaxed text-gray-500">{{ $help }}</p>@endif
</div>
