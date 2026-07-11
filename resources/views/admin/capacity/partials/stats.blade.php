@php
    $overallPct = $totalMaxCapacity > 0 ? round(($totalCurrentCount / $totalMaxCapacity) * 100, 1) : 0;

    // Get Desa Penglipuran Zone if exists
    $desaZone = collect($zones)->firstWhere('zone_identifier', 'desa_penglipuran');

    $overallThresholds = \App\Models\CapacityZone::statusFor(
        $overallPct,
        $desaZone['warning_threshold'] ?? 60,
        $desaZone['critical_threshold'] ?? 80,
    );
    $overallStatus = 'Kapasitas ' . $overallThresholds['label'];
    $overallColor = $overallThresholds['color'];
    $overallBarColor = $overallThresholds['barColor'];
@endphp
{{-- Overall Crowd Level --}}
<div id="tour-stats" class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
    data-desa-zone-id="{{ $desaZone['id'] ?? '' }}">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Wisatawan Saat Ini (Desa Penglipuran)</p>
            <div class="mt-1 flex items-baseline gap-2">
                <span data-stats-count class="text-charcoal text-5xl font-bold">{{ $totalCurrentCount }}</span>
                <span class="text-lg text-gray-400">/ {{ $totalMaxCapacity }} kapasitas total</span>
            </div>
            <div class="mt-3 h-3 overflow-hidden rounded-full bg-gray-100">
                <div data-stats-bar class="{{ $overallBarColor }} h-full transition-all" style="width: {{ min(100, $overallPct) }}%">
                </div>
            </div>
            <div class="mt-1.5 flex justify-between items-center text-sm text-gray-500">
                <p data-stats-status>{{ $overallPct }}% — <span class="{{ $overallColor }} font-semibold">{{ $overallStatus }}</span></p>
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
                $warningPct = $desaZone['warning_threshold'] ?? 60;
                $criticalPct = $desaZone['critical_threshold'] ?? 80;
                $levels = [
                    ['label' => 'Aman', 'range' => "< {$warningPct}%", 'color' => 'bg-primary/10 text-primary'],
                    ['label' => 'Sedang', 'range' => "{$warningPct}-{$criticalPct}%", 'color' => 'bg-secondary/15 text-secondary'],
                    ['label' => 'Penuh', 'range' => "> {$criticalPct}%", 'color' => 'bg-warning/10 text-warning'],
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
