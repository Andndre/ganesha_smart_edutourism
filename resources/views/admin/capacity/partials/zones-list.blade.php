{{-- Zone Cards --}}
<div class="flex justify-between items-center mb-4">
    <h3 class="text-charcoal font-semibold">Daftar Zona Kapasitas</h3>
    <button type="button" onclick="openCreateZoneModal()" class="bg-primary shadow-primary/20 hover:bg-primary-600 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-md">
        + Buat Zona Baru
    </button>
</div>
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    @foreach ($zones as $zone)
        @if($zone->zone_identifier === 'desa_penglipuran')
            @continue
        @endif
        @php
            $pct = $zone->max_capacity > 0 ? round(($zone->current_count / $zone->max_capacity) * 100) : 0;
            if ($pct >= ($zone->critical_threshold ?? 80)) {
                $statusLabel = 'Kritis';
                $barColor = 'bg-warning';
                $badgeClass = 'bg-warning/10 text-warning';
                $borderClass = 'border-warning/20';
            } elseif ($pct >= ($zone->warning_threshold ?? 60)) {
                $statusLabel = 'Sedang';
                $barColor = 'bg-secondary';
                $badgeClass = 'bg-secondary/15 text-secondary-700';
                $borderClass = 'border-secondary/20';
            } else {
                $statusLabel = 'Aman';
                $barColor = 'bg-primary';
                $badgeClass = 'bg-primary/10 text-primary';
                $borderClass = 'border-gray-100';
            }
        @endphp
        <div class="{{ $borderClass }} rounded-2xl border bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-start justify-between gap-2">
                <div>
                    <h3 class="text-charcoal font-semibold">{{ $zone->name }}</h3>
                    <span class="text-[10px] text-gray-400">Limit: {{ $zone->warning_threshold }}% Warning /
                        {{ $zone->critical_threshold }}% Critical</span>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        class="{{ $badgeClass }} shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold">{{ $statusLabel }}</span>
                    @if ($zone->id)
                        <button
                            onclick="openThresholdModal({{ json_encode([
                                'id' => $zone->id,
                                'name' => $zone->name,
                                'warning_threshold' => $zone->warning_threshold,
                                'critical_threshold' => $zone->critical_threshold,
                                'max_capacity' => $zone->max_capacity,
                                'polygon_coordinates' => $zone->polygon_coordinates,
                            ]) }})"
                            class="hover:text-primary text-gray-400 transition-colors focus:outline-none"
                            title="Edit Zona">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <form action="{{ route('admin.capacity.destroy', $zone->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus zona ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="hover:text-red-500 text-gray-400 transition-colors focus:outline-none" title="Hapus Zona">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-charcoal text-3xl font-bold">{{ $zone->current_count }}</span>
                <span class="font-medium text-gray-400">/ {{ $zone->max_capacity }} orang</span>
            </div>
            <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-gray-100">
                <div class="{{ $barColor }} h-full rounded-full transition-all"
                    style="width: {{ min(100, $pct) }}%"></div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-xs text-gray-500">
                <span>{{ $pct }}% terisi</span>
                <span class="font-medium">Identitas: {{ $zone->zone_identifier }}</span>
            </div>
        </div>
    @endforeach
</div>
