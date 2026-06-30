{{-- Overall Crowd Level --}}
<div id="tour-stats" class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Wisatawan Saat Ini (Desa Penglipuran)</p>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-charcoal text-5xl font-bold">{{ $totalCurrentCount }}</span>
                <span class="text-lg text-gray-400">/ {{ $totalMaxCapacity }} kapasitas total</span>
            </div>
            @php
                $overallPct = $totalMaxCapacity > 0 ? round(($totalCurrentCount / $totalMaxCapacity) * 100, 1) : 0;
                if ($overallPct >= 80) {
                    $overallStatus = 'Kapasitas Penuh';
                    $overallColor = 'text-warning';
                    $overallBarColor = 'bg-warning';
                } elseif ($overallPct >= 60) {
                    $overallStatus = 'Kapasitas Sedang';
                    $overallColor = 'text-secondary';
                    $overallBarColor = 'bg-secondary';
                } else {
                    $overallStatus = 'Kapasitas Aman';
                    $overallColor = 'text-primary';
                    $overallBarColor = 'bg-primary';
                }
                
                // Get Desa Penglipuran Zone if exists
                $desaZone = collect($zones)->firstWhere('zone_identifier', 'desa_penglipuran');
            @endphp
            <div class="mt-3 h-3 overflow-hidden rounded-full bg-gray-100">
                <div class="{{ $overallBarColor }} h-full transition-all" style="width: {{ min(100, $overallPct) }}%">
                </div>
            </div>
            <div class="mt-1.5 flex justify-between items-center text-sm text-gray-500">
                <p>{{ $overallPct }}% — <span class="{{ $overallColor }} font-semibold">{{ $overallStatus }}</span></p>
                @if($desaZone)
                    <button type="button" 
                            onclick="openThresholdModal({{ json_encode([
                                'id' => $desaZone['id'],
                                'name' => $desaZone['name'],
                                'warning_threshold' => $desaZone['warning_threshold'],
                                'critical_threshold' => $desaZone['critical_threshold'],
                                'max_capacity' => $desaZone['max_capacity'],
                                'polygon_coordinates' => $desaZone['polygon_coordinates'],
                            ]) }})"
                            class="text-xs font-semibold text-primary hover:text-primary-600">
                        Edit Kapasitas Desa
                    </button>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3 sm:text-right">
            @php
                $levels = [
                    ['label' => 'Aman', 'range' => '< 60%', 'color' => 'bg-primary/10 text-primary'],
                    ['label' => 'Sedang', 'range' => '60-80%', 'color' => 'bg-secondary/15 text-secondary'],
                    ['label' => 'Penuh', 'range' => '> 80%', 'color' => 'bg-warning/10 text-warning'],
                ];
            @endphp
            @foreach ($levels as $l)
                <div class="{{ $l['color'] }} rounded-xl p-3 text-center">
                    <p class="text-xs font-bold">{{ $l['label'] }}</p>
                    <p class="text-[11px] opacity-70">{{ $l['range'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
