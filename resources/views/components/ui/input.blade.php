@props([
    'type' => 'text',
    'name',
    'label' => null,
    'placeholder' => '',
    'error' => null,
    'required' => false,
])

<div class="space-y-1">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if ($required)
                <span class="text-error">*</span>
            @endif
        </label>
    @endif

    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        class="dark:bg-dark-surface {{ $error ? 'border-error focus:ring-error/20' : 'border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-4 focus:ring-primary/20' }} text-body w-full rounded-lg border bg-white px-4 py-3 transition-colors duration-150 focus:outline-none disabled:bg-gray-100 disabled:opacity-50">

    @if ($error)
        <p class="text-error text-sm">{{ $error }}</p>
    @endif
</div>
