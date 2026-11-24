@props(['colspan', 'filtered' => false, 'message' => 'Belum ada data.', 'hint' => 'Data yang ditambahkan akan muncul di sini.'])

<tr>
    <td colspan="{{ $colspan }}" {{ $attributes->class(['text-center']) }}>
        <div class="mx-auto flex max-w-md flex-col items-center gap-2 py-8">
            <svg class="mb-1 h-8 w-8 text-gray-400" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" />
            </svg>
            <p class="font-semibold text-charcoal">{{ $filtered ? 'Tidak ada hasil yang cocok.' : $message }}</p>
            <p class="text-sm text-gray-600">{{ $filtered ? 'Ubah kata kunci atau reset filter untuk melihat data lainnya.' : $hint }}</p>
        </div>
    </td>
</tr>
