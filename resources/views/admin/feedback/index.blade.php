@extends('layouts.admin')

@section('title', 'Ulasan & Feedback')

@section('content')

<div class="mb-6">
    <h1 class="font-display text-2xl font-bold text-charcoal">Ulasan & Feedback Wisatawan</h1>
    <p class="mt-0.5 text-sm text-gray-500">Pantau kepuasan pengunjung berdasarkan survei pasca kunjungan.</p>
</div>

{{-- Rating Summary --}}
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Rating Rata-rata</p>
        <div class="mt-2 flex items-baseline gap-2">
            <span class="text-4xl font-bold text-charcoal">4.7</span>
            <span class="text-secondary">★★★★★</span>
        </div>
        <p class="mt-1 text-xs text-gray-400">dari 5.0 · 148 ulasan</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Ulasan Bulan Ini</p>
        <p class="mt-2 text-4xl font-bold text-charcoal">38</p>
        <p class="mt-1 text-xs font-semibold text-primary">↑ +14% dari bulan lalu</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Distribusi Bintang</p>
        @php
            $stars = [[5, 72], [4, 20], [3, 5], [2, 2], [1, 1]];
        @endphp
        @foreach ($stars as [$star, $pct])
            <div class="mb-1 flex items-center gap-2 text-xs">
                <span class="w-4 text-gray-500">{{ $star }}★</span>
                <div class="flex-1 h-1.5 rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-secondary" style="width: {{ $pct }}%"></div>
                </div>
                <span class="w-8 text-right text-gray-400">{{ $pct }}%</span>
            </div>
        @endforeach
    </div>
</div>

{{-- Review List --}}
<div class="space-y-4">
    @php
        $reviews = [
            ['name' => 'Sari Dewi',    'rating' => 5, 'date' => '21 Mei 2026', 'comment' => 'Desanya sangat bersih dan terawat! Pemandunya ramah sekali, sangat membantu kami memahami sejarah budaya Bali. Wajib dikunjungi!'],
            ['name' => 'Maria Tan',    'rating' => 4, 'date' => '20 Mei 2026', 'comment' => 'Pengalaman yang sangat berkesan. Loloh Cemcem-nya enak sekali. Hanya saja parkirannya agak jauh dari pintu masuk.'],
            ['name' => 'Budi Santoso', 'rating' => 5, 'date' => '19 Mei 2026', 'comment' => 'Kebun bambunya luar biasa! Suasananya tenang, cocok untuk melarikan diri dari kebisingan kota. Anak-anak saya sangat suka.'],
            ['name' => 'Reza Pratama', 'rating' => 3, 'date' => '18 Mei 2026', 'comment' => 'Desanya bagus tapi sedikit ramai saat weekend. Perlu lebih banyak penunjuk arah di dalam desa.'],
        ];
    @endphp
    @foreach ($reviews as $r)
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary">
                        {{ strtoupper(substr($r['name'], 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-charcoal">{{ $r['name'] }}</p>
                        <p class="text-xs text-gray-400">{{ $r['date'] }}</p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-0.5 text-secondary">
                    @for ($i = 0; $i < 5; $i++)
                        <span class="text-sm {{ $i < $r['rating'] ? '' : 'opacity-20' }}">★</span>
                    @endfor
                </div>
            </div>
            <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ $r['comment'] }}</p>
            <div class="mt-3 flex gap-2">
                <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-500 hover:bg-gray-50">Tandai Dibaca</button>
                <button class="rounded-lg border border-warning/20 px-3 py-1.5 text-xs font-semibold text-warning hover:bg-warning/5">Hapus</button>
            </div>
        </div>
    @endforeach
</div>

@endsection
