@props([
    'name',
    'label',
    'id',
    'value' => [],
    'placeholderId' => 'Tulis deskripsi dalam bahasa Indonesia.',
    'placeholderEn' => 'Tulis deskripsi dalam bahasa Inggris.',
])

<x-dashboard.localized-field :id="$id" :label="$label" :field-name="$name" help="Isi setiap bahasa untuk halaman pengunjung.">
    <x-slot:indonesian>
        <label for="{{ $id }}-id" class="sr-only">{{ $label }} bahasa Indonesia</label>
        <textarea id="{{ $id }}-id" name="{{ $name }}[id]" rows="5"
            placeholder="{{ $placeholderId }}"
            class="min-h-40 w-full rounded-xl border border-gray-200 bg-white p-4 text-sm leading-relaxed text-charcoal outline-none transition placeholder:text-gray-400 focus:border-primary focus:ring-2 focus:ring-primary/15">{{ old($name.'.id', $value['id'] ?? '') }}</textarea>
    </x-slot:indonesian>
    <x-slot:english>
        <label for="{{ $id }}-en" class="sr-only">{{ $label }} bahasa Inggris</label>
        <textarea id="{{ $id }}-en" name="{{ $name }}[en]" rows="5"
            placeholder="{{ $placeholderEn }}"
            class="min-h-40 w-full rounded-xl border border-gray-200 bg-white p-4 text-sm leading-relaxed text-charcoal outline-none transition placeholder:text-gray-400 focus:border-primary focus:ring-2 focus:ring-primary/15">{{ old($name.'.en', $value['en'] ?? '') }}</textarea>
    </x-slot:english>
</x-dashboard.localized-field>
