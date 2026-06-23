@props([
    'name',
    'id' => null,
    'value' => '',
    'placeholder' => '',
    'hasImage' => false,
    'heightClass' => 'min-h-20 max-h-50',
    'required' => false,
])

@php
    $id = $id ?? 'tiptap-' . Str::random(10);
    $editorId = $id . '-editor';
    $toolbarId = $id . '-toolbar';
    $textareaId = $id . '-textarea';
    $hasImage = filter_var($hasImage, FILTER_VALIDATE_BOOLEAN);
@endphp

<div class="tiptap-editor-container" data-editor-id="{{ $editorId }}" data-toolbar-id="{{ $toolbarId }}" data-textarea-id="{{ $textareaId }}" data-placeholder="{{ $placeholder }}" data-has-image="{{ $hasImage ? 'true' : 'false' }}">
    <!-- Toolbar -->
    <div id="{{ $toolbarId }}" class="flex flex-wrap gap-1 rounded-t-xl border border-b-0 border-gray-200 bg-gray-50 p-1.5 text-gray-600">
        <button type="button" data-action="bold" class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Bold">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12h8a4 4 0 100-8H6v8zm0 0h10a4 4 0 110 8H6v-8z"></path></svg>
        </button>
        <button type="button" data-action="italic" class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Italic">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4h4M12 4v16M10 20h4M15 4L9 20"></path></svg>
        </button>
        <span class="mx-1 my-auto h-5 w-px bg-gray-200"></span>
        <button type="button" data-action="bulletList" class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Bullet List">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16M8 6h.01M8 12h.01M8 18h.01"></path></svg>
        </button>
        <button type="button" data-action="orderedList" class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Ordered List">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5h11M9 12h11M9 19h11M4 4h2v4H4V4zm0 6h2a1 1 0 011 1v1a1 1 0 01-1 1H4v-1h2v-1H4v-1zm0 6h2v3H4v-3z"></path></svg>
        </button>
        <span class="mx-1 my-auto h-5 w-px bg-gray-200"></span>
        @if($hasImage)
            <button type="button" data-action="image" class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Upload Image">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </button>
            <span class="mx-1 my-auto h-5 w-px bg-gray-200"></span>
        @endif
        <button type="button" data-action="undo" class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Undo">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
        </button>
        <button type="button" data-action="redo" class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900" title="Redo">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"></path></svg>
        </button>
    </div>

    <!-- Editor Element -->
    <div id="{{ $editorId }}" class="focus-within:border-primary focus-within:ring-primary/20 {{ $heightClass }} w-full overflow-y-auto rounded-b-xl border border-gray-200 bg-white p-4 text-sm focus-within:ring-1"></div>
    
    <!-- Hidden Textarea -->
    <textarea id="{{ $textareaId }}" name="{{ $name }}" class="hidden" {{ $required ? 'required' : '' }}>{{ $value }}</textarea>
</div>
